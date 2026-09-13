<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Admin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Monitor_model');
		$this->config->load('monitor', TRUE);
		$this->require_role('admin');
	}

	public function index()
	{
		$live = $this->Monitor_model->get_status();
		$store = $this->read_history();
		$base = rtrim(base_url(), '/');

		$this->load->view('dash/admin', array(
			'base_url'     => $base . '/',
			'asset_url'    => $base . '/assets/',
			'page_title'   => 'Operations Console',
			'monitor'      => $live['monitor'],
			'announcement' => $live['announcement'],
			'history'      => $store,
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

	protected function require_role($role)
	{
		if ($this->session->userdata('auth_role') !== $role)
		{
			redirect('auth/login?role=' . $role);
		}
	}

	protected function read_history()
	{
		$file = APPPATH . 'data/monitor.json';
		if ( ! is_file($file))
		{
			return array();
		}
		$data = json_decode((string) file_get_contents($file), TRUE);
		$history = (is_array($data) && isset($data['history'])) ? $data['history'] : array();
		return array_reverse($history);
	}
}
