<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Auth_model extends CI_Model {

	protected $users_file;

	public function __construct()
	{
		parent::__construct();
		$this->config->load('auth', TRUE);
		$dir = APPPATH . 'data';
		if ( ! is_dir($dir))
		{
			@mkdir($dir, 0755, TRUE);
		}
		$this->users_file = $dir . DIRECTORY_SEPARATOR . 'users.json';
		$this->ensure_seed();
	}

	public function find_by_username($username)
	{
		$username = strtolower(trim((string) $username));
		foreach ($this->all_users() as $user)
		{
			if (isset($user['username']) && strtolower($user['username']) === $username)
			{
				return $user;
			}
		}
		return NULL;
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

	protected function all_users()
	{
		if ( ! is_file($this->users_file))
		{
			return array();
		}
		$data = json_decode((string) @file_get_contents($this->users_file), TRUE);
		return (is_array($data) && isset($data['users']) && is_array($data['users'])) ? $data['users'] : array();
	}

	protected function ensure_seed()
	{
		if (is_file($this->users_file) && filesize($this->users_file) > 20)
		{
			return;
		}

		$seed = $this->config->item('auth_accounts', 'auth');
		if ( ! is_array($seed))
		{
			return;
		}

		$users = array();
		foreach ($seed as $row)
		{
			$users[] = array(
				'username'      => $row['username'],
				'name'          => $row['name'],
				'role'          => $row['role'],
				'password_hash' => password_hash($row['password'], PASSWORD_DEFAULT),
			);
		}

		@file_put_contents(
			$this->users_file,
			json_encode(array('users' => $users), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);
	}
}
