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

	public function register_resident($username, $name, $phone, $password)
	{
		$username = strtolower(trim((string) $username));
		$name = trim((string) $name);
		$phone = $this->normalize_phone($phone);

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
			'role'     => $user['role'],
		);
	}

	protected function ensure_seed()
	{
		if ( ! $this->db->table_exists('users'))
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
