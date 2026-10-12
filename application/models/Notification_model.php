<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Notification_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->ensure_schema();
	}

	public function ensure_schema()
	{
		if ( ! $this->db->table_exists('notifications'))
		{
			$this->db->query("CREATE TABLE IF NOT EXISTS `notifications` (
				`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				`audience` ENUM('admin','user') NOT NULL,
				`type` VARCHAR(32) NOT NULL,
				`title` VARCHAR(160) NOT NULL,
				`body` TEXT NOT NULL,
				`link` VARCHAR(255) NULL,
				`ref_id` BIGINT UNSIGNED NULL,
				`recipient_user_id` INT UNSIGNED NULL,
				`created_at` DATETIME NOT NULL,
				PRIMARY KEY (`id`),
				KEY `idx_notifications_audience_created` (`audience`, `created_at`),
				KEY `idx_notifications_recipient` (`recipient_user_id`),
				KEY `idx_notifications_type_ref` (`type`, `ref_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}
		if ( ! $this->db->table_exists('notification_reads'))
		{
			$this->db->query("CREATE TABLE IF NOT EXISTS `notification_reads` (
				`notification_id` BIGINT UNSIGNED NOT NULL,
				`user_id` INT UNSIGNED NOT NULL,
				`read_at` DATETIME NOT NULL,
				PRIMARY KEY (`notification_id`, `user_id`),
				KEY `idx_notification_reads_user` (`user_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}
		if ( ! $this->db->table_exists('push_subscriptions'))
		{
			$this->db->query("CREATE TABLE IF NOT EXISTS `push_subscriptions` (
				`endpoint_hash` CHAR(64) NOT NULL,
				`user_id` INT UNSIGNED NOT NULL,
				`audience` ENUM('admin','user') NOT NULL,
				`endpoint` TEXT NOT NULL,
				`public_key` VARCHAR(255) NOT NULL,
				`auth_token` VARCHAR(255) NOT NULL,
				`content_encoding` VARCHAR(32) NOT NULL DEFAULT 'aes128gcm',
				`created_at` DATETIME NOT NULL,
				`updated_at` DATETIME NOT NULL,
				PRIMARY KEY (`endpoint_hash`),
				KEY `idx_push_subscriptions_user` (`user_id`, `audience`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}
	}

	public function create($audience, $type, $title, $body, $link = NULL, $ref_id = NULL, $recipient_user_id = NULL, $send_push = TRUE)
	{
		if ( ! $this->db->table_exists('notifications'))
		{
			return FALSE;
		}
		$audience = ($audience === 'admin') ? 'admin' : 'user';
		$title = trim((string) $title);
		$body = trim((string) $body);
		if ($title === '' || $body === '')
		{
			return FALSE;
		}
		$created = $this->db->insert('notifications', array(
			'audience'          => $audience,
			'type'              => substr((string) $type, 0, 32),
			'title'             => substr($title, 0, 160),
			'body'              => $body,
			'link'              => $link ? substr((string) $link, 0, 255) : NULL,
			'ref_id'            => $ref_id !== NULL ? (int) $ref_id : NULL,
			'recipient_user_id' => $recipient_user_id !== NULL ? (int) $recipient_user_id : NULL,
			'created_at'        => date('Y-m-d H:i:s'),
		));
		if ($created && $send_push)
		{
			try
			{
				$this->load->library('web_push_service');
				$this->web_push_service->send_notification(
					(int) $this->db->insert_id(),
					$audience,
					$title,
					$body,
					$link,
					$type,
					$recipient_user_id
				);
			}
			catch (Throwable $error)
			{
				log_message('error', 'Push delivery failed for notification ' . (int) $this->db->insert_id() . ': ' . $error->getMessage());
			}
		}
		return $created;
	}

	public function save_push_subscription($user_id, $audience, $subscription)
	{
		if ( ! $this->db->table_exists('push_subscriptions') || (int) $user_id <= 0 || ! is_array($subscription))
		{
			return FALSE;
		}

		$endpoint = isset($subscription['endpoint']) ? trim((string) $subscription['endpoint']) : '';
		$keys = isset($subscription['keys']) && is_array($subscription['keys']) ? $subscription['keys'] : array();
		$public_key = isset($keys['p256dh']) ? (string) $keys['p256dh'] : '';
		$auth_token = isset($keys['auth']) ? (string) $keys['auth'] : '';
		$encoding = isset($subscription['contentEncoding']) ? (string) $subscription['contentEncoding'] : 'aes128gcm';
		$endpoint_parts = parse_url($endpoint);
		if (strlen($endpoint) > 2048 || ! is_array($endpoint_parts)
			|| ! isset($endpoint_parts['scheme'], $endpoint_parts['host'])
			|| strtolower($endpoint_parts['scheme']) !== 'https'
			|| (isset($endpoint_parts['port']) && (int) $endpoint_parts['port'] !== 443)
			|| isset($endpoint_parts['user'])
			|| isset($endpoint_parts['pass'])
			|| ! $this->is_supported_push_host(strtolower($endpoint_parts['host']))
			|| ! $this->valid_push_key($public_key, 65)
			|| ! $this->valid_push_key($auth_token, 16)
			|| ! in_array($encoding, array('aes128gcm', 'aesgcm'), TRUE))
		{
			return FALSE;
		}

		$now = date('Y-m-d H:i:s');
		$hash = hash('sha256', $endpoint);
		$sql = "INSERT INTO `push_subscriptions`
			(`endpoint_hash`, `user_id`, `audience`, `endpoint`, `public_key`, `auth_token`, `content_encoding`, `created_at`, `updated_at`)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`), `audience` = VALUES(`audience`),
			`endpoint` = VALUES(`endpoint`), `public_key` = VALUES(`public_key`),
			`auth_token` = VALUES(`auth_token`), `content_encoding` = VALUES(`content_encoding`),
			`updated_at` = VALUES(`updated_at`)";
		return $this->db->query($sql, array(
			$hash, (int) $user_id, $audience === 'admin' ? 'admin' : 'user',
			$endpoint, $public_key, $auth_token, $encoding, $now, $now,
		));
	}

	public function delete_push_subscription($user_id, $endpoint)
	{
		if ( ! $this->db->table_exists('push_subscriptions') || (int) $user_id <= 0 || ! is_string($endpoint) || $endpoint === '')
		{
			return FALSE;
		}
		return $this->db
			->where('endpoint_hash', hash('sha256', $endpoint))
			->where('user_id', (int) $user_id)
			->delete('push_subscriptions');
	}

	public function push_subscriptions_for($audience, $recipient_user_id = NULL)
	{
		if ( ! $this->db->table_exists('push_subscriptions') || ! $this->db->table_exists('users'))
		{
			return array();
		}
		$audience = $audience === 'admin' ? 'admin' : 'user';
		$sql = 'SELECT ps.* FROM `push_subscriptions` ps
			INNER JOIN `users` u ON u.id = ps.user_id
			WHERE ps.audience = ? AND u.role = ?';
		$params = array($audience, $audience);
		if ($audience === 'user' && $recipient_user_id !== NULL)
		{
			$sql .= ' AND ps.user_id = ?';
			$params[] = (int) $recipient_user_id;
		}
		return $this->db->query($sql, $params)->result_array();
	}

	public function delete_push_subscription_by_endpoint($endpoint)
	{
		if ( ! $this->db->table_exists('push_subscriptions'))
		{
			return FALSE;
		}
		return $this->db->where('endpoint_hash', hash('sha256', (string) $endpoint))->delete('push_subscriptions');
	}

	protected function valid_push_key($key, $expected_length)
	{
		if ( ! is_string($key) || $key === '')
		{
			return FALSE;
		}
		$decoded = base64_decode(strtr($key, '-_', '+/') . str_repeat('=', (4 - strlen($key) % 4) % 4), TRUE);
		return is_string($decoded) && strlen($decoded) === $expected_length;
	}

	protected function is_supported_push_host($host)
	{
		foreach (array('fcm.googleapis.com', 'push.services.mozilla.com', 'notify.windows.com', 'push.apple.com') as $domain)
		{
			if ($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain)
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	public function notify_admins($type, $title, $body, $link = NULL, $ref_id = NULL)
	{
		if ($ref_id !== NULL && $this->recent_exists('admin', $type, $ref_id, 3600))
		{
			return FALSE;
		}
		return $this->create('admin', $type, $title, $body, $link, $ref_id);
	}

	public function notify_all_users($type, $title, $body, $link = NULL, $ref_id = NULL, $dedupe_seconds = 86400, $send_push = TRUE)
	{
		if ($ref_id !== NULL && $this->recent_exists('user', $type, $ref_id, $dedupe_seconds))
		{
			return FALSE;
		}
		$created = $this->create('user', $type, $title, $body, $link, $ref_id, NULL, FALSE);
		if ($created && $send_push)
		{
			$this->push_existing_notification('user', $type, $ref_id, NULL);
		}
		return $created;
	}

	protected function recent_exists($audience, $type, $ref_id, $window_seconds)
	{
		$since = date('Y-m-d H:i:s', time() - max(60, (int) $window_seconds));
		return (int) $this->db
			->where('audience', $audience)
			->where('type', (string) $type)
			->where('ref_id', (int) $ref_id)
			->where('created_at >=', $since)
			->count_all_results('notifications') > 0;
	}

	public function on_resident_login($user)
	{
		if ( ! is_array($user) || empty($user['id']) || $user['role'] !== 'user')
		{
			return;
		}
		$name = isset($user['name']) ? $user['name'] : $user['username'];
		$phone = ! empty($user['phone']) ? $user['phone'] : 'no mobile on file';
		$barangay = ! empty($user['barangay']) ? $user['barangay'] : 'barangay not specified';
		$this->notify_admins(
			'user_login',
			'Resident signed in',
			$name . ' (' . $user['username'] . ') · ' . $barangay . ' · ' . $phone,
			site_url('admin/residents'),
			(int) $user['id']
		);
	}

	public function on_resident_signup($user)
	{
		if ( ! is_array($user) || empty($user['id']))
		{
			return;
		}
		$name = isset($user['name']) ? $user['name'] : $user['username'];
		$phone = ! empty($user['phone']) ? $user['phone'] : '—';
		$barangay = ! empty($user['barangay']) ? $user['barangay'] : 'barangay not specified';
		$this->notify_admins(
			'user_signup',
			'New resident registered',
			$name . ' · ' . $barangay . ' · ' . $phone . ' · @' . $user['username'],
			site_url('admin/residents'),
			(int) $user['id']
		);
	}

	public function on_announcement_published($row, $send_push = TRUE)
	{
		if ( ! is_array($row) || empty($row['id']))
		{
			return;
		}
		$title = isset($row['title']) ? $row['title'] : 'MDRRMO advisory';
		$body = isset($row['body']) ? $row['body'] : '';
		if (strlen($body) > 180)
		{
			$body = substr($body, 0, 177) . '…';
		}
		$barangay = isset($row['barangay']) ? trim((string) $row['barangay']) : '';
		if ($barangay !== '')
		{
			$this->notify_barangay_users(
				$barangay,
				'announcement',
				'New advisory for your barangay: ' . $title,
				$body !== '' ? $body : 'MDRRMO Dulag posted a new announcement.',
				site_url('portal/announcements'),
				(int) $row['id'],
				FALSE
			);
			if ( ! $send_push)
			{
				return FALSE;
			}
			$user_ids = $this->db
				->select('recipient_user_id')
				->where('audience', 'user')
				->where('type', 'announcement')
				->where('ref_id', (int) $row['id'])
				->where('recipient_user_id IS NOT NULL', NULL, FALSE)
				->get('notifications')
				->result_array();
			$delivered = FALSE;
			foreach ($user_ids as $recipient)
			{
				$delivered = $this->push_existing_notification('user', 'announcement', (int) $row['id'], (int) $recipient['recipient_user_id']) || $delivered;
			}
			return $delivered;
		}

		$this->notify_all_users(
			'announcement',
			'New advisory: ' . $title,
			$body !== '' ? $body : 'MDRRMO Dulag posted a new announcement.',
			site_url('portal#alerts'),
			(int) $row['id'],
			86400,
			FALSE
		);
		return $send_push
			? $this->push_existing_notification('user', 'announcement', (int) $row['id'], NULL)
			: FALSE;
	}

	protected function notify_barangay_users($barangay, $type, $title, $body, $link, $ref_id, $send_push = TRUE)
	{
		if ( ! $this->db->table_exists('users') || ! $this->db->field_exists('barangay', 'users'))
		{
			return FALSE;
		}

		$users = $this->db
			->select('id')
			->where('role', 'user')
			->where('barangay', $barangay)
			->get('users')
			->result_array();
		$created = FALSE;
		foreach ($users as $user)
		{
			$user_id = (int) $user['id'];
			$exists = $this->db
				->where('audience', 'user')
				->where('type', $type)
				->where('ref_id', (int) $ref_id)
				->where('recipient_user_id', $user_id)
				->count_all_results('notifications');
			if ($exists === 0 && $this->create('user', $type, $title, $body, $link, $ref_id, $user_id, FALSE))
			{
				$created = TRUE;
			}
			if ($send_push)
			{
				$this->push_existing_notification('user', $type, $ref_id, $user_id);
			}
		}
		return $created;
	}

	protected function push_existing_notification($audience, $type, $ref_id, $recipient_user_id)
	{
		$this->db->where('audience', $audience)
			->where('type', $type)
			->where('ref_id', (int) $ref_id);
		if ($recipient_user_id === NULL)
		{
			$this->db->where('recipient_user_id', NULL);
		}
		else
		{
			$this->db->where('recipient_user_id', (int) $recipient_user_id);
		}
		$row = $this->db->order_by('id', 'DESC')->limit(1)->get('notifications')->row_array();
		if ( ! $row)
		{
			return FALSE;
		}
		try
		{
			$this->load->library('web_push_service');
			$result = $this->web_push_service->send_notification(
				(int) $row['id'],
				$audience,
				$row['title'],
				$row['body'],
				$row['link'],
				$type,
				$recipient_user_id
			);
			return ! empty($result['sent']);
		}
		catch (Throwable $error)
		{
			log_message('error', 'Push delivery failed for notification ' . (int) $row['id'] . ': ' . $error->getMessage());
			return FALSE;
		}
	}

	public function on_flood_alert($level, $title, $alert_id)
	{
		if ( ! in_array($level, array('yellow', 'red'), TRUE))
		{
			return;
		}
		$label = ($level === 'red') ? 'Critical flood alert' : 'Flood advisory';
		$this->notify_all_users(
			'flood_alert',
			$label,
			$title,
			site_url('portal#alerts'),
			(int) $alert_id,
			7200
		);
	}

	public function list_for_user($user_id, $role, $limit = 25)
	{
		if ( ! $this->db->table_exists('notifications') || (int) $user_id <= 0)
		{
			return array();
		}
		$user_id = (int) $user_id;
		$limit = max(1, min(50, (int) $limit));
		$audience = ($role === 'admin') ? 'admin' : 'user';

		$sql = "SELECT n.*,
			CASE WHEN nr.notification_id IS NOT NULL THEN 1 ELSE 0 END AS is_read
			FROM `notifications` n
			LEFT JOIN `notification_reads` nr
				ON nr.notification_id = n.id AND nr.user_id = ?
			WHERE n.audience = ?";
		$params = array($user_id, $audience);

		if ($audience === 'user')
		{
			$sql .= ' AND (n.recipient_user_id IS NULL OR n.recipient_user_id = ?)';
			$params[] = $user_id;
		}

		$sql .= ' ORDER BY n.created_at DESC LIMIT ' . $limit;
		$rows = $this->db->query($sql, $params)->result_array();
		$out = array();
		foreach ($rows as $row)
		{
			$out[] = $this->format_row($row);
		}
		return $out;
	}

	public function count_unread($user_id, $role)
	{
		if ( ! $this->db->table_exists('notifications') || (int) $user_id <= 0)
		{
			return 0;
		}
		$user_id = (int) $user_id;
		$audience = ($role === 'admin') ? 'admin' : 'user';

		$sql = "SELECT COUNT(*) AS c FROM `notifications` n
			LEFT JOIN `notification_reads` nr
				ON nr.notification_id = n.id AND nr.user_id = ?
			WHERE n.audience = ? AND nr.notification_id IS NULL";
		$params = array($user_id, $audience);

		if ($audience === 'user')
		{
			$sql .= ' AND (n.recipient_user_id IS NULL OR n.recipient_user_id = ?)';
			$params[] = $user_id;
		}

		$row = $this->db->query($sql, $params)->row_array();
		return $row ? (int) $row['c'] : 0;
	}

	public function mark_read($notification_id, $user_id)
	{
		if ( ! $this->db->table_exists('notification_reads') || (int) $notification_id <= 0 || (int) $user_id <= 0)
		{
			return FALSE;
		}
		if ( ! $this->user_can_access((int) $notification_id, (int) $user_id))
		{
			return FALSE;
		}
		$exists = $this->db
			->where('notification_id', (int) $notification_id)
			->where('user_id', (int) $user_id)
			->count_all_results('notification_reads');
		if ($exists > 0)
		{
			return TRUE;
		}
		return $this->db->insert('notification_reads', array(
			'notification_id' => (int) $notification_id,
			'user_id'         => (int) $user_id,
			'read_at'         => date('Y-m-d H:i:s'),
		));
	}

	public function mark_all_read($user_id, $role)
	{
		if ( ! $this->db->table_exists('notifications') || ! $this->db->table_exists('notification_reads') || (int) $user_id <= 0)
		{
			return FALSE;
		}
		$audience = ($role === 'admin') ? 'admin' : 'user';
		$recipient_clause = ($audience === 'user') ? ' AND (n.recipient_user_id IS NULL OR n.recipient_user_id = ?)' : '';
		$params = array((int) $user_id, (int) $user_id, $audience);
		if ($audience === 'user')
		{
			$params[] = (int) $user_id;
		}
		return $this->db->query(
			"INSERT IGNORE INTO `notification_reads` (`notification_id`, `user_id`, `read_at`)
			SELECT n.id, ?, NOW() FROM `notifications` n
			LEFT JOIN `notification_reads` nr ON nr.notification_id = n.id AND nr.user_id = ?
			WHERE n.audience = ?" . $recipient_clause . " AND nr.notification_id IS NULL",
			$params
		);
	}

	protected function user_can_access($notification_id, $user_id)
	{
		$row = $this->db->where('id', (int) $notification_id)->get('notifications')->row_array();
		if ( ! $row)
		{
			return FALSE;
		}
		$user = $this->db->where('id', (int) $user_id)->get('users')->row_array();
		if ( ! $user)
		{
			return FALSE;
		}
		if ($row['audience'] === 'admin')
		{
			return $user['role'] === 'admin';
		}
		if ($user['role'] !== 'user')
		{
			return FALSE;
		}
		if ($row['recipient_user_id'] === NULL || (int) $row['recipient_user_id'] === (int) $user_id)
		{
			return TRUE;
		}
		return FALSE;
	}

	protected function format_row($row)
	{
		$created = isset($row['created_at']) ? strtotime($row['created_at']) : time();
		return array(
			'id'        => (int) $row['id'],
			'type'      => $row['type'],
			'title'     => $row['title'],
			'body'      => $row['body'],
			'link'      => $row['link'],
			'is_read'   => ! empty($row['is_read']),
			'time_ago'  => $this->time_ago($created),
			'created_at'=> $row['created_at'],
		);
	}

	protected function time_ago($ts)
	{
		$diff = time() - (int) $ts;
		if ($diff < 60)
		{
			return 'Just now';
		}
		if ($diff < 3600)
		{
			return floor($diff / 60) . ' min ago';
		}
		if ($diff < 86400)
		{
			return floor($diff / 3600) . ' hr ago';
		}
		return date('M j, g:i A', (int) $ts);
	}

	public function resolve_user_id_from_session()
	{
		$ci = get_instance();
		$id = (int) $ci->session->userdata('auth_id');
		if ($id > 0)
		{
			return $id;
		}
		$username = (string) $ci->session->userdata('auth_user');
		if ($username === '')
		{
			return 0;
		}
		$row = $this->db->select('id')->where('username', $username)->get('users')->row_array();
		return $row ? (int) $row['id'] : 0;
	}
}
