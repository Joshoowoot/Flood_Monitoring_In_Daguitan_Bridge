<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Auth extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Auth_model');
		$this->load->helper(array('url', 'form'));
	}

	public function login($role = 'user')
	{
		if ($role === 'admin' || $this->input->get('role') === 'admin')
		{
			redirect('admin');
			return;
		}

		if ($this->session->userdata('auth_user'))
		{
			return $this->redirect_for_role($this->session->userdata('auth_role'));
		}

		$error = '';
		$username = '';
		if ($this->input->method(TRUE) === 'POST')
		{
			$username = trim((string) $this->input->post('username'));
			$password = (string) $this->input->post('password');
			$user = $this->Auth_model->verify($username, $password);

			if ( ! $user)
			{
				$error = 'Incorrect username or password.';
			}
			elseif ($user['role'] !== 'user')
			{
				$error = 'This sign-in page is for residents only.';
			}
			else
			{
				$this->Auth_model->record_login($user);
				$this->establish_session($user);
				return $this->redirect_for_role($user['role']);
			}
		}

		$data = $this->shell('Sign in');
		$data['error'] = $error;
		$data['username'] = $username;
		$data['name'] = '';
		$data['phone'] = '';
		$data['auth_mode'] = 'signin';
		$this->load->view('auth/login', $data);
	}

	public function signup()
	{
		if ($this->session->userdata('auth_user'))
		{
			return $this->redirect_for_role($this->session->userdata('auth_role'));
		}

		$error = '';
		$username = '';
		$name = '';
		$phone = '';
		if ($this->input->method(TRUE) === 'POST')
		{
			$name = trim((string) $this->input->post('name'));
			$username = strtolower(trim((string) $this->input->post('username')));
			$phone = trim((string) $this->input->post('phone'));
			$password = (string) $this->input->post('password');
			$confirm = (string) $this->input->post('password_confirm');

			if (strlen($name) < 2)
			{
				$error = 'Please enter your full name.';
			}
			elseif ( ! preg_match('/^[a-z0-9_]{3,32}$/', $username))
			{
				$error = 'Username must be 3–32 characters using letters, numbers, or underscore.';
			}
			elseif (strlen($password) < 8)
			{
				$error = 'Password must be at least 8 characters.';
			}
			elseif ($password !== $confirm)
			{
				$error = 'Passwords do not match.';
			}
			else
			{
				$result = $this->Auth_model->register_resident($username, $name, $phone, $password);
				if (empty($result['ok']))
				{
					$error = isset($result['error']) ? $result['error'] : 'Could not create your account.';
				}
				else
				{
					$this->load->model('Notification_model');
					$this->Notification_model->on_resident_signup($result['user']);
					$this->Auth_model->record_login($result['user']);
					$this->establish_session($result['user']);
					redirect('portal');
					return;
				}
			}
		}

		$data = $this->shell('Sign up');
		$data['error'] = $error;
		$data['username'] = $username;
		$data['name'] = $name;
		$data['phone'] = $phone;
		$data['auth_mode'] = 'signup';
		$this->load->view('auth/login', $data);
	}

	public function logout()
	{
		$this->session->unset_userdata(array('auth_user', 'auth_name', 'auth_role', 'auth_phone', 'auth_uid', 'auth_id'));
		$this->session->sess_destroy();
		redirect('/');
	}

	protected function establish_session($user)
	{
		$this->session->set_userdata(array(
			'auth_user'  => $user['username'],
			'auth_name'  => $user['name'],
			'auth_role'  => $user['role'],
			'auth_phone' => isset($user['phone']) ? $user['phone'] : '',
			'auth_uid'   => isset($user['record_uid']) ? $user['record_uid'] : '',
			'auth_id'    => isset($user['id']) ? (int) $user['id'] : 0,
		));
	}

	protected function redirect_for_role($role)
	{
		if ($role === 'admin')
		{
			redirect('admin');
			return;
		}
		redirect('portal');
	}

	protected function shell($title)
	{
		$base = rtrim(base_url(), '/');
		return array(
			'base_url'   => $base . '/',
			'asset_url'  => $base . '/assets/',
			'page_title' => $title,
		);
	}
}
