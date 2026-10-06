<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Auth_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('auth', TRUE);
		$this->load->model('Sync_model');
		$this->ensure_seed();
	}

	public function normalize_phone($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);
		if (strlen($digits) >= 12 && strpos($digits, '63') === 0)
		{
			$digits = '0' . substr($digits, 2);
		}

		return $digits;
	}

	public function is_valid_phone($phone)
	{
		$digits = $this->normalize_phone($phone);
		if ($digits === '')
		{
			return FALSE;
		}

		return (bool) preg_match('/^09\d{9}$/', $digits);
	}

	public function find_by_username($username)
	{
		$username = strtolower(trim((string) $username));
		if ($username === '')
		{
			return NULL;
		}

		$row = $this->db->query(
			'SELECT * FROM `users` WHERE LOWER(`username`) = ? LIMIT 1',
			array($username)
		)->row_array();

		return $row ? $row : NULL;
	}

	public function find_by_phone($phone)
	{
		$digits = $this->normalize_phone($phone);
		if ($digits === '' || ! $this->db->field_exists('phone', 'users'))
		{
			return NULL;
		}

		$row = $this->db->query(
			'SELECT * FROM `users` WHERE `phone` = ? LIMIT 1',
			array($digits)
		)->row_array();

		return $row ? $row : NULL;
	}

	public function find_by_login($login)
	{
		$login = trim((string) $login);
		if ($login === '')
		{
			return NULL;
		}

		$digits = $this->normalize_phone($login);
		if ($digits !== '' && strlen($digits) >= 10)
		{
			$user = $this->find_by_phone($digits);
			if ($user)
			{
				return $user;
			}
		}

		return $this->find_by_username($login);
	}

	public function verify($login, $password)
	{
		$user = $this->find_by_login($login);
		if ( ! $user || empty($user['password_hash']))
		{
			return NULL;
		}
		if ( ! password_verify($password, $user['password_hash']))
		{
			return NULL;
		}
		unset($user['password_hash']);
		return $user;
	}

	public function record_login($user)
	{
		if ( ! is_array($user) || empty($user['id']))
		{
			return;
		}

		$now = date('Y-m-d H:i:s');
		$update = array('last_login_at' => $now);
		if ($this->db->field_exists('last_login_at', 'users'))
		{
			$this->db->where('id', (int) $user['id'])->update('users', $update);
		}

		if ( ! $this->db->table_exists('user_logins'))
		{
			return;
		}

		$ci = get_instance();
		$ip = $ci->input->ip_address();
		$agent = substr((string) $ci->input->user_agent(), 0, 255);

		$this->db->insert('user_logins', array(
			'user_id'      => (int) $user['id'],
			'record_uid'   => isset($user['record_uid']) ? $user['record_uid'] : NULL,
			'username'     => $user['username'],
			'name'         => $user['name'],
			'phone'        => isset($user['phone']) ? $user['phone'] : NULL,
			'role'         => $user['role'],
			'ip_address'   => $ip !== FALSE ? $ip : NULL,
			'user_agent'   => $agent !== '' ? $agent : NULL,
			'logged_in_at' => $now,
		));

		if ($user['role'] === 'user')
		{
			$this->load->model('Notification_model');
			$this->Notification_model->on_resident_login($user);
		}
	}

	public function register_resident($username, $name, $phone, $barangay, $password)
	{
		$username = strtolower(trim((string) $username));
		$name = trim((string) $name);
		$phone = $this->normalize_phone($phone);
		$barangay = trim((string) $barangay);
		$this->load->model('Hazard_model');
		$barangays = $this->Hazard_model->barangays();

		if ( ! isset($barangays[$barangay]))
		{
			return array('ok' => FALSE, 'error' => 'Select your barangay from the list.');
		}
		if ($this->find_by_username($username))
		{
			return array('ok' => FALSE, 'error' => 'That username is already taken.');
		}
		if ($phone !== '' && $this->find_by_phone($phone))
		{
			return array('ok' => FALSE, 'error' => 'That mobile number is already registered.');
		}
		if ( ! $this->is_valid_phone($phone))
		{
			return array('ok' => FALSE, 'error' => 'Enter a valid Philippine mobile number (09XXXXXXXXX).');
		}

		$ok = $this->db->insert('users', array(
			'record_uid'    => $this->Sync_model->new_uid(),
			'username'      => $username,
			'name'          => $name,
			'phone'         => $phone,
			'barangay'      => $barangay,
			'role'          => 'user',
			'password_hash' => password_hash($password, PASSWORD_DEFAULT),
			'sync_status'   => 'pending',
		));

		if ( ! $ok)
		{
			return array('ok' => FALSE, 'error' => 'Could not create your account. Please try again.');
		}

		$this->Sync_model->try_copy_now(FALSE);
		$user = $this->find_by_username($username);
		if ($user)
		{
			unset($user['password_hash']);
		}

		return array('ok' => TRUE, 'user' => $user);
	}

	public function public_user($user)
	{
		if ( ! is_array($user))
		{
			return NULL;
		}
		return array(
			'username' => $user['username'],
			'name'     => isset($user['name']) ? $user['name'] : $user['username'],
			'phone'    => isset($user['phone']) ? $user['phone'] : NULL,
			'barangay' => isset($user['barangay']) ? $user['barangay'] : NULL,
			'role'     => $user['role'],
		);
	}

	public function update_resident_profile($user_id, $name, $phone, $barangay)
	{
		$user_id = (int) $user_id;
		$name = trim((string) $name);
		$phone = $this->normalize_phone($phone);
		$barangay = trim((string) $barangay);

		if ($user_id < 1 || strlen($name) < 2 || strlen($name) > 120)
		{
			return array('ok' => FALSE, 'error' => 'Enter a name between 2 and 120 characters.');
		}
		if ( ! $this->is_valid_barangay($barangay))
		{
			return array('ok' => FALSE, 'error' => 'Select a valid barangay from the list.');
		}

		if ($phone !== '' && ! $this->is_valid_phone($phone))
		{
			return array('ok' => FALSE, 'error' => 'Enter a valid Philippine mobile number (09XXXXXXXXX), or leave it blank.');
		}
		if ($phone !== '')
		{
			$existing = $this->db
				->where('phone', $phone)
				->where('id !=', $user_id)
				->limit(1)
				->get('users')
				->row_array();
			if ($existing)
			{
				return array('ok' => FALSE, 'error' => 'That mobile number is already registered to another account.');
			}
		}

		$update = array('name' => $name, 'phone' => $phone !== '' ? $phone : NULL, 'barangay' => $barangay);
		$this->mark_user_pending($update);
		$ok = $this->db->where('id', $user_id)->where('role', 'user')->update('users', $update);
		if ( ! $ok)
		{
			return array('ok' => FALSE, 'error' => 'Could not save your profile. Please try again.');
		}

		return array('ok' => TRUE);
	}

	public function is_valid_barangay($barangay)
	{
		$this->load->model('Hazard_model');
		$barangays = $this->Hazard_model->barangays();
		return isset($barangays[trim((string) $barangay)]);
	}

	public function change_resident_password($user_id, $current_password, $new_password)
	{
		$user_id = (int) $user_id;
		$user = $this->db
			->select('id, password_hash')
			->where('id', $user_id)
			->where('role', 'user')
			->limit(1)
			->get('users')
			->row_array();

		if ( ! $user || empty($user['password_hash']) || ! password_verify($current_password, $user['password_hash']))
		{
			return array('ok' => FALSE, 'error' => 'Your current password is incorrect.');
		}
		if (strlen($new_password) < 8 || strlen($new_password) > 4096)
		{
			return array('ok' => FALSE, 'error' => 'Your new password must be at least 8 characters.');
		}

		$update = array('password_hash' => password_hash($new_password, PASSWORD_DEFAULT));
		$this->mark_user_pending($update);
		$ok = $this->db->where('id', $user_id)->where('role', 'user')->update('users', $update);
		if ( ! $ok)
		{
			return array('ok' => FALSE, 'error' => 'Could not update your password. Please try again.');
		}

		return array('ok' => TRUE);
	}

	protected function mark_user_pending(&$update)
	{
		if ($this->db->field_exists('sync_status', 'users'))
		{
			$update['sync_status'] = 'pending';
		}
		if ($this->db->field_exists('sync_error', 'users'))
		{
			$update['sync_error'] = NULL;
		}
	}

	protected function ensure_seed()
	{
		if ( ! $this->db->table_exists('users'))
		{
			return;
		}
		if ((int) $this->db->count_all('users') > 0)
		{
			return;
		}

		$seed = $this->config->item('auth_accounts', 'auth');
		if ( ! is_array($seed))
		{
			return;
		}

		foreach ($seed as $row)
		{
			if ($this->find_by_username($row['username']))
			{
				continue;
			}

			$phone = isset($row['phone']) ? $this->normalize_phone($row['phone']) : NULL;
			if ($phone === '')
			{
				$phone = NULL;
			}

			$this->db->insert('users', array(
				'record_uid'    => $this->Sync_model->new_uid(),
				'username'      => $row['username'],
				'name'          => $row['name'],
				'phone'         => $phone,
				'role'          => $row['role'],
				'password_hash' => password_hash($row['password'], PASSWORD_DEFAULT),
				'sync_status'   => 'pending',
			));
		}
	}
}
