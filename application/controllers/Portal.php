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
		$this->load->model('Evacuation_center_model');
		$evacuation_centers = array_values(array_filter($this->Evacuation_center_model->all(), function ($center) {
			return ! empty($center['active']);
		}));
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

		$this->load->model('Notification_model');
		$notify_uid = $this->Notification_model->resolve_user_id_from_session();
		$this->load->view('dash/portal', array(
			'base_url'     => $base . '/',
			'asset_url'    => $base . '/assets/',
			'page_title'   => 'Resident Portal',
			'monitor'      => $live['monitor'],
			'announcement' => $live['announcement'],
			'weather'      => $live['weather'],
			'auth_name'    => $this->session->userdata('auth_name'),
			'auth_phone'   => $this->session->userdata('auth_phone'),
			'logout_url'   => site_url('auth/logout'),
			'status_url'   => $base . '/index.php/api/status',
			'notify_audience' => 'user',
			'notify_unread'   => ($notify_uid > 0)
				? $this->Notification_model->count_unread($notify_uid, 'user')
				: 0,
			'notify_config' => array(
				'listUrl'    => $base . '/index.php/api/notifications',
				'readUrl'    => $base . '/index.php/api/notifications/read',
				'readAllUrl' => $base . '/index.php/api/notifications/read_all',
				'pollMs'     => 30000,
			),
			'actions_map'  => $actions,
			'actions'      => isset($actions[$level]) ? $actions[$level] : $actions['green'],
			'evacuation_centers' => $evacuation_centers,
		));
	}

	public function announcements()
	{
		$live = $this->Monitor_model->get_status();
		$base = rtrim(base_url(), '/');
		$this->load->view('announcements', array(
			'base_url'          => $base . '/',
			'asset_url'         => $base . '/assets/',
			'status_url'        => $base . '/index.php/api/status',
			'home_url'          => site_url('portal'),
			'announcements_url' => site_url('portal/announcements'),
			'login_user'        => site_url('login'),
			'signup_url'        => site_url('signup'),
			'logout_url'        => site_url('auth/logout'),
			'auth_role'         => 'user',
			'auth_name'         => $this->session->userdata('auth_name'),
			'page_title'        => 'Resident Announcements',
			'resident_portal'   => TRUE,
			'nav_page'          => 'announcements',
			'monitor'           => $live['monitor'],
			'weather'           => $live['weather'],
			'announcement'      => $live['announcement'],
			'announcements'     => $this->Monitor_model->list_published_announcements(),
		));
	}

	public function go_bag()
	{
		$base = rtrim(base_url(), '/');
		$this->load->view('dash/go_bag', array(
			'base_url'   => $base . '/',
			'asset_url'  => $base . '/assets/',
			'page_title' => 'Go Bag Checklist',
			'auth_name'  => $this->session->userdata('auth_name'),
			'logout_url' => site_url('auth/logout'),
			'portal_url' => site_url('portal'),
		));
	}

	public function profile()
	{
		$base = rtrim(base_url(), '/');
		$this->load->view('dash/profile', array(
			'asset_url'  => $base . '/assets/',
			'page_title' => 'My Profile',
			'auth_name'  => $this->session->userdata('auth_name'),
			'auth_user'  => $this->session->userdata('auth_user'),
			'auth_phone' => $this->session->userdata('auth_phone'),
			'logout_url' => site_url('auth/logout'),
			'portal_url' => site_url('portal'),
		));
	}

	public function help()
	{
		$base = rtrim(base_url(), '/');
		$this->load->view('dash/help', array(
			'asset_url'  => $base . '/assets/',
			'page_title' => 'Help and How to Use',
			'auth_name'  => $this->session->userdata('auth_name'),
			'logout_url' => site_url('auth/logout'),
			'portal_url' => site_url('portal'),
		));
	}

	public function evacuation_centers()
	{
		$base = rtrim(base_url(), '/');
		$this->load->model('Evacuation_center_model');
		$centers = array();
		foreach ($this->Evacuation_center_model->all() as $center)
		{
			if (empty($center['active'])) continue;
			$center['phone_link'] = 'tel:' . preg_replace('/[^0-9+]/', '', $center['phone']);
			$center['maps'] = 'https://www.google.com/maps/dir/?api=1&destination='
				. rawurlencode($center['latitude'] . ',' . $center['longitude']);
			$center['distance'] = 'Pinned by MDRRMO · Barangay ' . $center['barangay'];
			$centers[] = $center;
		}

		$this->load->view('dash/evacuation_centers', array(
			'base_url' => $base . '/',
			'asset_url' => $base . '/assets/',
			'page_title' => 'Evacuation Centers',
			'auth_name' => $this->session->userdata('auth_name'),
			'logout_url' => site_url('auth/logout'),
			'portal_url' => site_url('portal'),
			'centers' => $centers,
		));
	}
}
