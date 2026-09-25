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

	public function evacuation_centers()
	{
		$base = rtrim(base_url(), '/');
		$centers = array(
			array(
				'name' => 'Dulag Municipal Evacuation Center',
				'address' => 'Municipal Complex, Dulag, Leyte',
				'distance' => 'Approx. 2.4 km from Daguitan Bridge',
				'capacity' => 'Confirm current capacity with MDRRMO',
				'phone' => '053 325 0000',
				'phone_link' => 'tel:0533250000',
				'maps' => 'https://www.google.com/maps/search/?api=1&query=Dulag+Municipal+Evacuation+Center+Leyte',
			),
			array(
				'name' => 'Dulag Central School Evacuation Site',
				'address' => 'Dulag Central School, Dulag, Leyte',
				'distance' => 'Approx. 3.1 km from Daguitan Bridge',
				'capacity' => 'Confirm current capacity with MDRRMO',
				'phone' => '053 325 0000',
				'phone_link' => 'tel:0533250000',
				'maps' => 'https://www.google.com/maps/search/?api=1&query=Dulag+Central+School+Leyte',
			),
		);

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
