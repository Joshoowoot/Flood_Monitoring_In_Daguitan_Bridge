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
		$query_role = $this->input->get('role');
		if ($query_role === 'admin' || $role === 'admin')
		{
			$role = 'admin';
		}
		else
		{
			$role = 'user';
		}

		if ($this->session->userdata('auth_user'))
		{
			return $this->redirect_for_role($this->session->userdata('auth_role'));
		}

		$error = '';
		if ($this->input->method(TRUE) === 'POST')
		{
			$posted_role = $this->input->post('role') === 'admin' ? 'admin' : 'user';
			$role = $posted_role;
			$username = trim((string) $this->input->post('username'));
			$password = (string) $this->input->post('password');
			$user = $this->Auth_model->verify($username, $password);

			if ( ! $user)
			{
				$error = 'Incorrect username or password.';
			}
			elseif ($user['role'] !== $posted_role)
			{
				$error = $posted_role === 'admin'
					? 'This account is not an administrator. Use Resident Login instead.'
					: 'This account is an administrator. Use Admin Login instead.';
			}
			else
			{
				$this->session->set_userdata(array(
					'auth_user' => $user['username'],
					'auth_name' => $user['name'],
					'auth_role' => $user['role'],
				));
				return $this->redirect_for_role($user['role']);
			}
		}

		$data = $this->shell($role === 'admin' ? 'Administrator Login' : 'Resident Login');
		$data['role'] = $role;
		$data['error'] = $error;
		$data['username'] = isset($username) ? $username : '';
		$this->load->view('auth/login', $data);
	}

	public function logout()
	{
		$this->session->unset_userdata(array('auth_user', 'auth_name', 'auth_role'));
		$this->session->sess_destroy();
		redirect('/');
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
