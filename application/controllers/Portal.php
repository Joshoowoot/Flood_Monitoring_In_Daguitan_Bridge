<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Portal extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Monitor_model');
		if ($this->session->userdata('auth_role') !== 'user')
		{
			redirect('login');
		}
	}

	public function index()
	{
		$live = $this->Monitor_model->get_status();
		$base = rtrim(base_url(), '/');
		$level = $live['monitor']['warning_level'];

		$actions = array(
			'green' => array(
				'Keep drainage around your home clear.',
				'Save this page or install the app for live updates.',
				'Know your nearest evacuation center.',
			),
			'yellow' => array(
				'Stay alert and avoid the riverbank and low-lying roads.',
				'Prepare a go-bag: IDs, flashlight, drinking water, medicines.',
				'Keep phones charged and listen for MDRRMO updates.',
			),
			'red' => array(
				'Move immediately to higher ground or your assigned evacuation center.',
				'Do not cross flowing water on foot or by vehicle.',
				'Follow barangay officials and MDRRMO instructions.',
			),
		);

		$this->load->view('dash/portal', array(
			'base_url'     => $base . '/',
			'asset_url'    => $base . '/assets/',
			'page_title'   => 'Resident Portal',
			'monitor'      => $live['monitor'],
			'announcement' => $live['announcement'],
			'weather'      => $live['weather'],
			'auth_name'    => $this->session->userdata('auth_name'),
			'status_url'   => $base . '/index.php/api/status',
			'actions'      => isset($actions[$level]) ? $actions[$level] : $actions['green'],
		));
	}
}
