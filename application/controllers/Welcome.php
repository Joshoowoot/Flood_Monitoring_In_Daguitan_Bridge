<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Welcome extends CI_Controller {

	public function index()
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

		$data = array(
			'base_url'     => $base . '/',
			'asset_url'    => $base . '/assets/',
			'status_url'   => $base . '/index.php/api/status',
			'ingest_url'   => $base . '/index.php/api/ingest',
			'login_user'   => $base . '/index.php/login/resident',
			'login_admin'  => $base . '/index.php/login/admin',
			'logout_url'   => $base . '/index.php/auth/logout',
			'auth_role'    => $this->session->userdata('auth_role'),
			'auth_name'    => $this->session->userdata('auth_name'),
			'page_title'   => 'Daguitan Flood Monitor',
			'monitor'      => $live['monitor'],
			'weather'      => $live['weather'],
			'announcement' => $live['announcement'],
			'yellow_m'     => (float) $this->config->item('monitor_threshold_yellow_m', 'monitor'),
			'red_m'        => (float) $this->config->item('monitor_threshold_red_m', 'monitor'),
			'sensor_cm'    => (int) $this->config->item('monitor_sensor_height_cm', 'monitor'),
		);

		$this->load->view('landing', $data);
	}
}
