<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Family_evacuation_plan_model extends CI_Model {

	protected $support_options = array(
		'Young children or infants',
		'Older adults',
		'Mobility or accessibility support',
		'Medication or health supplies',
		'Pets or service animals',
	);

	public function __construct()
	{
		parent::__construct();
		$this->ensure_schema();
	}

	public function shared_status($user_id)
	{
		$row = $this->db
			->select('shared_at, updated_at')
			->where('user_id', (int) $user_id)
			->get('family_evacuation_plans')
			->row_array();

		if ($this->db->error()['code'] !== 0)
		{
			log_message('error', 'Could not read family evacuation plan sharing status.');
			return array('ok' => FALSE);
		}

		return array('ok' => TRUE, 'shared' => ! empty($row), 'shared_at' => $row ? $row['shared_at'] : NULL);
	}

	public function share($user_id, $input)
	{
		$user_id = (int) $user_id;
		if ($user_id <= 0)
		{
			return array('ok' => FALSE, 'error' => 'Please sign in again before sharing your plan.');
		}
		$plan = array();
		foreach (array('meetingPoint' => 120, 'evacuationPlace' => 120, 'contactName' => 80, 'contactPhone' => 32) as $key => $max_length)
		{
			$value = trim((string) (isset($input[$key]) ? $input[$key] : ''));
			if (strlen($value) > $max_length)
			{
				return array('ok' => FALSE, 'error' => 'One or more plan fields exceed the allowed length. Please shorten them and try again.');
			}
			$plan[$key] = $value;
		}
		if ($plan['evacuationPlace'] === '')
		{
			return array('ok' => FALSE, 'error' => 'Add the evacuation destination assigned by your barangay before sharing your plan.');
		}

		$support_needs = isset($input['supportNeeds']) && is_array($input['supportNeeds']) ? $input['supportNeeds'] : array();
		$support_needs = array_values(array_unique(array_map('strval', $support_needs)));
		if (count(array_diff($support_needs, $this->support_options)) > 0)
		{
			return array('ok' => FALSE, 'error' => 'The support needs contain an unrecognized option. Refresh and try again.');
		}
		$support_json = json_encode($support_needs);
		if ($support_json === FALSE)
		{
			return array('ok' => FALSE, 'error' => 'The support needs could not be saved. Please review the form and try again.');
		}
		$reviewed_at = isset($input['lastReviewedAt']) ? trim((string) $input['lastReviewedAt']) : '';
		$reviewed_timestamp = $reviewed_at === '' ? FALSE : strtotime($reviewed_at);
		if ($reviewed_at !== '' && $reviewed_timestamp === FALSE)
		{
			return array('ok' => FALSE, 'error' => 'The plan review date is invalid. Save your plan again and retry.');
		}

		$existing = $this->db->select('user_id')->where('user_id', $user_id)->get('family_evacuation_plans')->row_array();
		$exists_error = $this->db->error();
		if ((int) $exists_error['code'] !== 0)
		{
			log_message('error', 'Could not check for an existing family evacuation plan.');
			return array('ok' => FALSE, 'error' => 'The plan could not be shared right now. Please try again.');
		}

		$now = date('Y-m-d H:i:s');
		$data = array(
			'meeting_point' => $plan['meetingPoint'],
			'evacuation_place' => $plan['evacuationPlace'],
			'contact_name' => $plan['contactName'],
			'contact_phone' => $plan['contactPhone'],
			'support_needs' => $support_json,
			'last_reviewed_at' => $reviewed_timestamp === FALSE ? NULL : date('Y-m-d H:i:s', $reviewed_timestamp),
			'consented_at' => $now,
			'shared_at' => $now,
			'updated_at' => $now,
		);

		if ($existing)
		{
			$this->db->where('user_id', $user_id)->update('family_evacuation_plans', $data);
		}
		else
		{
			$data['user_id'] = $user_id;
			$this->db->insert('family_evacuation_plans', $data);
		}

		if ((int) $this->db->error()['code'] !== 0)
		{
			log_message('error', 'Could not save voluntarily shared family evacuation plan for resident ID ' . $user_id . '.');
			return array('ok' => FALSE, 'error' => 'The plan could not be shared right now. Please try again.');
		}
		return array('ok' => TRUE, 'shared_at' => $now);
	}

	public function revoke($user_id)
	{
		$this->db->where('user_id', (int) $user_id)->delete('family_evacuation_plans');
		if ((int) $this->db->error()['code'] !== 0)
		{
			log_message('error', 'Could not revoke shared family evacuation plan for resident ID ' . (int) $user_id . '.');
			return FALSE;
		}
		return TRUE;
	}

	public function all_shared($search = '')
	{
		$this->db
			->select('p.meeting_point, p.evacuation_place, p.contact_name, p.contact_phone, p.support_needs, p.last_reviewed_at, p.consented_at, p.shared_at, p.updated_at, u.name, u.barangay')
			->from('family_evacuation_plans p')
			->join('users u', 'u.id = p.user_id', 'inner')
			->where('u.role', 'user')
			->order_by('p.updated_at', 'DESC');
		$search = trim((string) $search);
		if ($search !== '')
		{
			$this->db->group_start()
				->like('u.name', $search)
				->or_like('u.barangay', $search)
				->or_like('p.evacuation_place', $search)
				->group_end();
		}
		$rows = $this->db->limit(500)->get()->result_array();
		if ((int) $this->db->error()['code'] !== 0)
		{
			log_message('error', 'Could not list voluntarily shared family evacuation plans.');
			return FALSE;
		}
		foreach ($rows as &$row)
		{
			$decoded = json_decode((string) $row['support_needs'], TRUE);
			$row['support_needs'] = is_array($decoded) ? $decoded : array();
		}
		unset($row);
		return $rows;
	}

	protected function ensure_schema()
	{
		$sql = 'CREATE TABLE IF NOT EXISTS `family_evacuation_plans` (
			`user_id` INT NOT NULL,
			`meeting_point` VARCHAR(120) NOT NULL DEFAULT \'\',
			`evacuation_place` VARCHAR(120) NOT NULL,
			`contact_name` VARCHAR(80) NOT NULL DEFAULT \'\',
			`contact_phone` VARCHAR(32) NOT NULL DEFAULT \'\',
			`support_needs` TEXT NOT NULL,
			`last_reviewed_at` DATETIME NULL,
			`consented_at` DATETIME NOT NULL,
			`shared_at` DATETIME NOT NULL,
			`updated_at` DATETIME NOT NULL,
			PRIMARY KEY (`user_id`),
			KEY `idx_family_plan_updated` (`updated_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
		if ( ! $this->db->query($sql))
		{
			log_message('error', 'Could not create family evacuation plan sharing storage.');
		}
	}
}
