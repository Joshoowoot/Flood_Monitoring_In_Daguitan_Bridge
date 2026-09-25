<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Welcome extends CI_Controller {

	public function index()
	{
		$data = $this->public_data();
		$data['page_title'] = 'Daguitan Flood Monitor';
		$data['nav_page'] = 'home';
		$this->load->view('landing', $data);
	}

	public function announcements()
	{
		if ($this->session->userdata('auth_role') === 'user')
		{
			redirect('portal/announcements');
		}

		$data = $this->public_data();
		$data['page_title'] = 'Announcements';
		$data['nav_page'] = 'announcements';
		$data['announcements'] = $this->Monitor_model->list_published_announcements();
		$this->load->view('announcements', $data);
	}

	protected function public_data()
	{
		$this->load->helper('url');
		$this->load->model('Monitor_model');

		$base = rtrim(base_url(), '/');
		if ($base === '')
		{
			$scheme = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
			$dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
			$base = $scheme . '://' . $host . rtrim($dir, '/');
		}

		$live = $this->Monitor_model->get_status();
		$this->config->load('monitor', TRUE);

		return array(
			'base_url'          => $base . '/',
			'asset_url'         => $base . '/assets/',
			'status_url'        => $base . '/index.php/api/status',
			'ingest_url'        => $base . '/index.php/api/ingest',
			'home_url'          => site_url(),
			'announcements_url' => site_url('announcements'),
			'login_user'        => site_url('login'),
			'signup_url'        => site_url('signup'),
			'logout_url'        => site_url('logout'),
			'auth_role'         => $this->session->userdata('auth_role'),
			'auth_name'         => $this->session->userdata('auth_name'),
			'monitor'           => $live['monitor'],
			'weather'           => $live['weather'],
			'announcement'      => $live['announcement'],
			'yellow_m'          => (float) $this->config->item('monitor_threshold_yellow_m', 'monitor'),
			'red_m'             => (float) $this->config->item('monitor_threshold_red_m', 'monitor'),
			'sensor_cm'         => (int) $this->config->item('monitor_sensor_height_cm', 'monitor'),
		);
	}
}
