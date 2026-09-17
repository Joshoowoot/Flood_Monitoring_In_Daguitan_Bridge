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

	public function find_by_username($username)
	{
		$username = strtolower(trim((string) $username));
		$row = $this->db
			->where('username', $username)
			->get('users')
			->row_array();

		return $row ? $row : NULL;
	}

	public function verify($username, $password)
	{
		$user = $this->find_by_username($username);
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

	public function register_resident($username, $name, $password)
	{
		$username = strtolower(trim((string) $username));
		$name = trim((string) $name);

		if ($this->find_by_username($username))
		{
			return array('ok' => FALSE, 'error' => 'That username is already taken.');
		}

		$ok = $this->db->insert('users', array(
			'record_uid'    => $this->Sync_model->new_uid(),
			'username'      => $username,
			'name'          => $name,
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
			'role'     => $user['role'],
		);
	}

	protected function ensure_seed()
	{
		if ( ! $this->db->table_exists('users'))
		{
			return;
		}

		$count = (int) $this->db->count_all('users');
		if ($count > 0)
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
			$this->db->insert('users', array(
				'record_uid'    => $this->Sync_model->new_uid(),
				'username'      => $row['username'],
				'name'          => $row['name'],
				'role'          => $row['role'],
				'password_hash' => password_hash($row['password'], PASSWORD_DEFAULT),
				'sync_status'   => 'pending',
			));
		}
	}
}
