<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Admin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url', 'form'));
		$this->load->model('Auth_model');
	}

	public function index()
	{
		if ($this->session->userdata('auth_role') === 'admin')
		{
			return $this->dashboard();
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
			elseif ($user['role'] !== 'admin')
			{
				$error = 'This page is for MDRRMO administrators only.';
			}
			else
			{
				$this->session->set_userdata(array(
					'auth_user' => $user['username'],
					'auth_name' => $user['name'],
					'auth_role' => $user['role'],
				));
				redirect('admin');
				return;
			}
		}

		$base = rtrim(base_url(), '/');
		$this->load->view('auth/admin_login', array(
			'base_url'   => $base . '/',
			'asset_url'  => $base . '/assets/',
			'page_title' => 'Administrator Sign in',
			'error'      => $error,
			'username'   => $username,
		));
	}

	protected function dashboard()
	{
		$this->load->model('Monitor_model');
		$this->config->load('monitor', TRUE);

		$live = $this->Monitor_model->get_status();
		$history = $this->Monitor_model->get_history(200);
		$this->load->model('Sync_model');
		$sync = $this->Sync_model->status();
		$base = rtrim(base_url(), '/');
		$notice = $this->session->flashdata('sync_notice');

		$this->load->view('dash/admin', array(
			'base_url'     => $base . '/',
			'asset_url'    => $base . '/assets/',
			'page_title'   => 'Operations Console',
			'monitor'      => $live['monitor'],
			'announcement' => $live['announcement'],
			'history'      => $history,
			'sync'         => $sync,
			'sync_notice'  => $notice,
			'auth_name'    => $this->session->userdata('auth_name'),
			'status_url'   => $base . '/index.php/api/status',
			'ingest_url'   => $base . '/index.php/api/ingest',
			'proxy_url'    => 'http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/daguitan/ingest.php',
			'thresholds'   => array(
				'yellow' => $this->config->item('monitor_threshold_yellow_m', 'monitor'),
				'red'    => $this->config->item('monitor_threshold_red_m', 'monitor'),
				'height' => $this->config->item('monitor_sensor_height_cm', 'monitor'),
			),
		));
	}

	public function sync()
	{
		if ($this->session->userdata('auth_role') !== 'admin')
		{
			redirect('admin');
			return;
		}

		$this->load->model('Sync_model');
		$result = $this->Sync_model->flush();
		$this->session->set_flashdata('sync_notice', $result['message']);
		redirect('admin');
	}
}
