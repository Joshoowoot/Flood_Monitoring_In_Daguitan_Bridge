<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Community_report_model extends CI_Model {

	protected $upload_dir;
	protected $statuses = array(
		'received' => 'Received',
		'reviewing' => 'Under review',
		'action_taken' => 'Action taken',
		'resolved' => 'Resolved',
	);

	public function __construct()
	{
		parent::__construct();
		$this->upload_dir = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'report_uploads' . DIRECTORY_SEPARATOR;
		$this->ensure_schema();
	}

	public function statuses()
	{
		return $this->statuses;
	}

	public function create($user_id, $input, $photo_file = NULL)
	{
		$this->load->model('Hazard_model');
		$barangays = $this->Hazard_model->barangays();
		$barangay = trim((string) (isset($input['barangay']) ? $input['barangay'] : ''));
		$types = array(
			'flooding' => 'Flooding',
			'blocked_drainage' => 'Blocked drainage',
			'rising_water' => 'Rising water',
			'other_hazard' => 'Other hazard',
		);
		$type = trim((string) (isset($input['report_type']) ? $input['report_type'] : ''));
		$landmark = trim((string) (isset($input['landmark']) ? $input['landmark'] : ''));
		$description = trim((string) (isset($input['description']) ? $input['description'] : ''));

		if ((int) $user_id < 1)
		{
			return array('ok' => FALSE, 'error' => 'Please sign in again before submitting a report.');
		}
		if ( ! isset($barangays[$barangay]))
		{
			return array('ok' => FALSE, 'error' => 'Choose the barangay where the condition was observed.');
		}
		if ( ! isset($types[$type]))
		{
			return array('ok' => FALSE, 'error' => 'Choose a valid report type.');
		}
		if ($landmark === '' || strlen($landmark) > 160)
		{
			return array('ok' => FALSE, 'error' => 'Enter a nearby street, landmark, or location (up to 160 characters).');
		}
		if ($description === '' || strlen($description) > 2000)
		{
			return array('ok' => FALSE, 'error' => 'Describe what you observed in 1 to 2,000 characters.');
		}

		$photo_path = NULL;
		if (is_array($photo_file) && ! empty($photo_file['full_path']) && is_file($photo_file['full_path']))
		{
			$photo_path = $photo_file['full_path'];
		}

		$ok = $this->db->insert('community_reports', array(
			'user_id' => (int) $user_id,
			'barangay' => $barangay,
			'report_type' => $type,
			'landmark' => $landmark,
			'description' => $description,
			'photo_name' => $photo_path !== NULL ? basename($photo_path) : NULL,
			'status' => 'received',
			'admin_note' => NULL,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));

		if ( ! $ok)
		{
			if ($photo_path !== NULL && is_file($photo_path))
			{
				unlink($photo_path);
			}
			return array('ok' => FALSE, 'error' => 'The report could not be saved. Please try again.');
		}

		return array('ok' => TRUE, 'id' => (int) $this->db->insert_id());
	}

	public function list_for_user($user_id)
	{
		if ((int) $user_id < 1)
		{
			return array();
		}
		$rows = $this->db
			->where('user_id', (int) $user_id)
			->order_by('created_at', 'DESC')
			->limit(100)
			->get('community_reports')
			->result_array();
		return $this->format_rows($rows);
	}

	public function list_all($filters = array())
	{
		if (isset($filters['status']) && isset($this->statuses[$filters['status']]))
		{
			$this->db->where('status', $filters['status']);
		}
		if (isset($filters['barangay']) && $filters['barangay'] !== '')
		{
			$this->db->where('barangay', $filters['barangay']);
		}
		$rows = $this->db
			->select('community_reports.*, users.name AS reporter_name, users.username AS reporter_username')
			->join('users', 'users.id = community_reports.user_id', 'left')
			->order_by("FIELD(community_reports.status, 'received', 'reviewing', 'action_taken', 'resolved')", '', FALSE)
			->order_by('community_reports.created_at', 'DESC')
			->limit(300)
			->get('community_reports')
			->result_array();
		return $this->format_rows($rows);
	}

	public function find($id)
	{
		if ((int) $id < 1)
		{
			return NULL;
		}
		$row = $this->db->where('id', (int) $id)->get('community_reports')->row_array();
		if ( ! $row)
		{
			return NULL;
		}
		$rows = $this->format_rows(array($row));
		return $rows[0];
	}

	public function update_review($id, $status, $admin_note, $admin_name)
	{
		$admin_note = trim((string) $admin_note);
		if ((int) $id < 1 || ! isset($this->statuses[$status]))
		{
			return array('ok' => FALSE, 'error' => 'Choose a valid report status.');
		}
		if (strlen($admin_note) > 1000)
		{
			return array('ok' => FALSE, 'error' => 'The follow-up note must be 1,000 characters or fewer.');
		}
		if ( ! $this->find($id))
		{
			return array('ok' => FALSE, 'error' => 'This report no longer exists.');
		}

		$updated = $this->db->where('id', (int) $id)->update('community_reports', array(
			'status' => $status,
			'admin_note' => $admin_note !== '' ? $admin_note : NULL,
			'reviewed_by' => substr(trim((string) $admin_name), 0, 80),
			'updated_at' => date('Y-m-d H:i:s'),
		));
		return $updated
			? array('ok' => TRUE)
			: array('ok' => FALSE, 'error' => 'The report update could not be saved.');
	}

	public function photo_path($id, $user_id = NULL, $admin = FALSE)
	{
		$row = $this->db->where('id', (int) $id)->get('community_reports')->row_array();
		if ( ! $row || empty($row['photo_name']))
		{
			return NULL;
		}
		if ( ! $admin && (int) $row['user_id'] !== (int) $user_id)
		{
			return NULL;
		}
		$name = basename($row['photo_name']);
		if ($name !== $row['photo_name'] || ! preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/', $name))
		{
			return NULL;
		}
		$path = $this->upload_dir . $name;
		return is_file($path) ? $path : NULL;
	}

	public function photo_mime($path)
	{
		$info = @getimagesize($path);
		if ( ! is_array($info) || empty($info['mime']))
		{
			return NULL;
		}
		$allowed = array('image/jpeg', 'image/png', 'image/webp');
		return in_array($info['mime'], $allowed, TRUE) ? $info['mime'] : NULL;
	}

	protected function format_rows($rows)
	{
		$type_labels = array(
			'flooding' => 'Flooding',
			'blocked_drainage' => 'Blocked drainage',
			'rising_water' => 'Rising water',
			'other_hazard' => 'Other hazard',
		);
		foreach ($rows as &$row)
		{
			$row['status_label'] = isset($this->statuses[$row['status']]) ? $this->statuses[$row['status']] : 'Received';
			$row['type_label'] = isset($type_labels[$row['report_type']]) ? $type_labels[$row['report_type']] : 'Other hazard';
			$row['has_photo'] = ! empty($row['photo_name']);
		}
		unset($row);
		return $rows;
	}

	protected function ensure_schema()
	{
		if ( ! $this->db->table_exists('community_reports'))
		{
			$this->db->query("CREATE TABLE IF NOT EXISTS `community_reports` (
				`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				`user_id` INT UNSIGNED NOT NULL,
				`barangay` VARCHAR(80) NOT NULL,
				`report_type` ENUM('flooding','blocked_drainage','rising_water','other_hazard') NOT NULL,
				`landmark` VARCHAR(160) NOT NULL,
				`description` TEXT NOT NULL,
				`photo_name` VARCHAR(64) NULL,
				`status` ENUM('received','reviewing','action_taken','resolved') NOT NULL DEFAULT 'received',
				`admin_note` TEXT NULL,
				`reviewed_by` VARCHAR(80) NULL,
				`created_at` DATETIME NOT NULL,
				`updated_at` DATETIME NOT NULL,
				PRIMARY KEY (`id`),
				KEY `idx_community_reports_user_created` (`user_id`, `created_at`),
				KEY `idx_community_reports_status_created` (`status`, `created_at`),
				KEY `idx_community_reports_barangay_created` (`barangay`, `created_at`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}
	}
}
