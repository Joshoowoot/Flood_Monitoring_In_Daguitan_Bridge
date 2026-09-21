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
	}

	public function create($audience, $type, $title, $body, $link = NULL, $ref_id = NULL, $recipient_user_id = NULL)
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
		return $this->db->insert('notifications', array(
			'audience'          => $audience,
			'type'              => substr((string) $type, 0, 32),
			'title'             => substr($title, 0, 160),
			'body'              => $body,
			'link'              => $link ? substr((string) $link, 0, 255) : NULL,
			'ref_id'            => $ref_id !== NULL ? (int) $ref_id : NULL,
			'recipient_user_id' => $recipient_user_id !== NULL ? (int) $recipient_user_id : NULL,
			'created_at'        => date('Y-m-d H:i:s'),
		));
	}

	public function notify_admins($type, $title, $body, $link = NULL, $ref_id = NULL)
	{
		if ($ref_id !== NULL && $this->recent_exists('admin', $type, $ref_id, 3600))
		{
			return FALSE;
		}
		return $this->create('admin', $type, $title, $body, $link, $ref_id);
	}

	public function notify_all_users($type, $title, $body, $link = NULL, $ref_id = NULL, $dedupe_seconds = 86400)
	{
		if ($ref_id !== NULL && $this->recent_exists('user', $type, $ref_id, $dedupe_seconds))
		{
			return FALSE;
		}
		return $this->create('user', $type, $title, $body, $link, $ref_id);
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
		$this->notify_admins(
			'user_login',
			'Resident signed in',
			$name . ' (' . $user['username'] . ') · ' . $phone,
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
		$this->notify_admins(
			'user_signup',
			'New resident registered',
			$name . ' · ' . $phone . ' · @' . $user['username'],
			site_url('admin/residents'),
			(int) $user['id']
		);
	}

	public function on_announcement_published($row)
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
		$this->notify_all_users(
			'announcement',
			'New advisory: ' . $title,
			$body !== '' ? $body : 'MDRRMO Dulag posted a new announcement.',
			site_url('portal#alerts'),
			(int) $row['id']
		);
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
		$items = $this->list_for_user($user_id, $role, 50);
		foreach ($items as $item)
		{
			if (empty($item['is_read']))
			{
				$this->mark_read((int) $item['id'], (int) $user_id);
			}
		}
		return TRUE;
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
