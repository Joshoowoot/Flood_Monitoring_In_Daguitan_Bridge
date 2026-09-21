<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Admin extends CI_Controller {

	protected $nav = array(
		'dashboard'     => array('label' => 'Dashboard', 'href' => 'admin'),
		'live'          => array('label' => 'Live Monitoring', 'href' => 'admin/live'),
		'history'       => array('label' => 'Monitoring History', 'href' => 'admin/history'),
		'alerts'        => array('label' => 'Flood Alerts', 'href' => 'admin/alerts'),
		'analytics'     => array('label' => 'Analytics', 'href' => 'admin/analytics'),
		'sensors'       => array('label' => 'Sensor Status', 'href' => 'admin/sensors'),
		'announcements' => array('label' => 'Announcements', 'href' => 'admin/announcements'),
		'residents'     => array('label' => 'Residents', 'href' => 'admin/residents'),
		'reports'       => array('label' => 'Reports', 'href' => 'admin/reports'),
		'settings'      => array('label' => 'Settings', 'href' => 'admin/settings'),
	);

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
		return $this->login_form();
	}

	protected function login_form()
	{
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
				$this->Auth_model->record_login($user);
				$this->session->set_userdata(array(
					'auth_user'  => $user['username'],
					'auth_name'  => $user['name'],
					'auth_role'  => $user['role'],
					'auth_phone' => isset($user['phone']) ? $user['phone'] : '',
					'auth_uid'   => isset($user['record_uid']) ? $user['record_uid'] : '',
					'auth_id'    => isset($user['id']) ? (int) $user['id'] : 0,
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

	public function dashboard()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();
		$this->Admin_portal_model->sync_alerts_from_monitor($ctx['monitor'], $this->session->userdata('auth_user'));

		$this->render('dashboard', array(
			'page_title'   => 'Admin Dashboard',
			'page_heading' => 'Operations overview',
			'page_lede'    => 'Real-time Daguitan Bridge telemetry, threshold-based warnings, and station health for MDRRMO Dulag.',
			'chart'        => $this->Admin_portal_model->chart_series(80),
			'history'      => $this->Admin_portal_model->history_table(12),
			'snapshot'     => $this->Admin_portal_model->dashboard_snapshot(),
			'recent_logins'=> $this->Admin_portal_model->list_recent_logins(8),
			'sync_notice'  => $this->session->flashdata('sync_notice'),
		) + $ctx);
	}

	public function live()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();
		$this->render('live', array(
			'page_title'   => 'Live Monitoring',
			'page_heading' => 'Live monitoring',
			'page_lede'    => 'Current river level, trend, and field hardware status at Daguitan Bridge.',
		) + $ctx);
	}

	public function history()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();
		$filters = array(
			'warning'   => trim((string) $this->input->get('warning')),
			'date_from' => $this->input->get('date_from') ? strtotime($this->input->get('date_from')) : NULL,
			'date_to'   => $this->input->get('date_to') ? strtotime($this->input->get('date_to') . ' 23:59:59') : NULL,
		);
		$history_rows = $this->Admin_portal_model->history_table(500, $filters);
		$this->render('history', array(
			'page_title'   => 'Monitoring History',
			'page_heading' => 'Monitoring history',
			'page_lede'    => 'Stored ultrasonic readings with trend, rate of rise, and threshold classification.',
			'history_rows' => $history_rows,
			'history_count'=> count($history_rows),
			'filters'      => $filters,
			'filter_date_from' => $this->input->get('date_from'),
			'filter_date_to'   => $this->input->get('date_to'),
		) + $ctx);
	}

	public function alerts()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();
		$this->Admin_portal_model->sync_alerts_from_monitor($ctx['monitor'], $this->session->userdata('auth_user'));
		$this->render('alerts', array(
			'page_title'   => 'Flood Alerts',
			'page_heading' => 'Flood alerts',
			'page_lede'    => 'Threshold-based warnings (not AI predictions). Acknowledge alerts after MDRRMO review.',
			'alerts'       => $this->Admin_portal_model->list_alerts(),
			'sync_notice'  => $this->session->flashdata('sync_notice'),
		) + $ctx);
	}

	public function alert_acknowledge($id = 0)
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$this->Admin_portal_model->acknowledge_alert((int) $id, (string) $this->session->userdata('auth_user'));
		$this->session->set_flashdata('sync_notice', 'Alert acknowledged.');
		redirect('admin/alerts');
	}

	public function alert_acknowledge_all()
	{
		$this->require_admin();
		if ($this->input->method(TRUE) !== 'POST')
		{
			redirect('admin/alerts');
			return;
		}
		$this->load->model('Admin_portal_model');
		$n = $this->Admin_portal_model->acknowledge_all_alerts((string) $this->session->userdata('auth_user'));
		$this->session->set_flashdata('sync_notice', $n > 0 ? $n . ' alert(s) acknowledged.' : 'No active alerts to acknowledge.');
		redirect('admin/alerts');
	}

	public function analytics()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();
		$days = (int) $this->input->get('days');
		if ( ! in_array($days, array(0, 7, 30, 90), TRUE))
		{
			$days = 30;
		}
		$filters = array();
		if ($days > 0)
		{
			$filters['since_ts'] = $this->Admin_portal_model->history_since($days);
		}
		$since = ($days > 0) ? $filters['since_ts'] : NULL;
		$this->render('analytics', array(
			'page_title'   => 'Analytics',
			'page_heading' => 'Analytics',
			'page_lede'    => 'Historical water levels and warning-band counts from stored sensor readings.',
			'summary'      => $this->Admin_portal_model->analytics_summary($since),
			'chart'        => $this->Admin_portal_model->chart_series(500, $since),
			'history_rows' => $this->Admin_portal_model->history_table(50, $filters),
			'analytics_days' => $days,
		) + $ctx);
	}

	public function sensors()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();
		$this->render('sensors', array(
			'page_title'   => 'Sensor Status',
			'page_heading' => 'Sensor & connectivity',
			'page_lede'    => 'ESP32, JSN-SR04T, network, and power indicators for the bridge station.',
		) + $ctx);
	}

	public function announcements()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();

		if ($this->input->method(TRUE) === 'POST')
		{
			$action = (string) $this->input->post('action');
			if ($action === 'delete')
			{
				$this->Admin_portal_model->delete_announcement((int) $this->input->post('id'));
				$this->session->set_flashdata('sync_notice', 'Announcement deleted.');
			}
			elseif ($action === 'publish' || $action === 'unpublish')
			{
				$send_push = ! empty($this->input->post('send_push'));
				$this->Admin_portal_model->toggle_announcement_publish(
					(int) $this->input->post('id'),
					$action === 'publish',
					$send_push
				);
				$msg = ($action === 'publish') ? 'Announcement published.' : 'Announcement unpublished.';
				if ($send_push)
				{
					$msg .= ' Push notification queued (requires configured web push).';
				}
				$this->session->set_flashdata('sync_notice', $msg);
			}
			else
			{
				$result = $this->Admin_portal_model->save_announcement((int) $this->input->post('id'), array(
					'title'         => $this->input->post('title'),
					'body'          => $this->input->post('body'),
					'level'         => $this->input->post('level'),
					'is_published'  => $this->input->post('is_published'),
				));
				$this->session->set_flashdata('sync_notice', empty($result['ok'])
					? (isset($result['error']) ? $result['error'] : 'Could not save.')
					: 'Announcement saved.');
			}
			redirect('admin/announcements');
			return;
		}

		$edit_id = (int) $this->input->get('edit');
		$this->render('announcements', array(
			'page_title'    => 'Announcements',
			'page_heading'  => 'Announcements',
			'page_lede'     => 'Create and publish MDRRMO advisories for the resident portal and public site.',
			'announcements' => $this->Admin_portal_model->list_announcements(),
			'edit_row'      => $this->Admin_portal_model->get_announcement($edit_id),
			'sync_notice'   => $this->session->flashdata('sync_notice'),
		) + $ctx);
	}

	public function residents()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();
		$search = trim((string) $this->input->get('q'));
		$status = trim((string) $this->input->get('status'));
		$residents = $this->Admin_portal_model->list_residents($search, $status);
		$this->render('residents', array(
			'page_title'   => 'Residents',
			'page_heading' => 'Resident accounts',
			'page_lede'    => 'Registered community members with contact details and account status.',
			'residents'    => $residents,
			'resident_count' => count($residents),
			'recent_logins'=> $this->Admin_portal_model->list_recent_logins(15),
			'search'       => $search,
			'status_filter'=> $status,
		) + $ctx);
	}

	public function reports()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();
		$since_30 = $this->Admin_portal_model->history_since(30);
		$this->render('reports', array(
			'page_title'   => 'Reports',
			'page_heading' => 'Reports',
			'page_lede'    => 'Summary snapshot for briefings and documentation.',
			'summary'      => $this->Admin_portal_model->analytics_summary($since_30),
			'summary_label'=> 'Last 30 days',
			'history_rows' => $this->Admin_portal_model->history_table(30, array('since_ts' => $since_30)),
			'alerts'       => $this->Admin_portal_model->list_alerts(20),
		) + $ctx);
	}

	public function settings()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();

		if ($this->input->method(TRUE) === 'POST')
		{
			$result = $this->Admin_portal_model->save_settings($this->input->post());
			$this->session->set_flashdata('sync_notice', empty($result['ok'])
				? (isset($result['error']) ? $result['error'] : 'Could not save settings.')
				: 'Settings saved.');
			redirect('admin/settings');
			return;
		}

		$this->render('settings', array(
			'page_title'   => 'Settings',
			'page_heading' => 'Settings',
			'page_lede'    => 'Flood thresholds, monitoring windows, and notification preferences.',
			'sync_notice'  => $this->session->flashdata('sync_notice'),
			'ingest_url'   => rtrim(base_url(), '/') . '/index.php/api/ingest',
		) + $ctx);
	}

	public function sync()
	{
		$this->require_admin();
		$this->load->model('Sync_model');
		$result = $this->Sync_model->flush();
		$this->session->set_flashdata('sync_notice', $result['message']);
		$return = trim((string) $this->input->post('return_to'));
		if ($return !== '' && $this->is_safe_admin_return($return))
		{
			redirect($return);
			return;
		}
		redirect('admin');
	}

	public function residents_export()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$search = trim((string) $this->input->get('q'));
		$status = trim((string) $this->input->get('status'));
		$rows = $this->Admin_portal_model->list_residents($search, $status);
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=daguitan-residents-' . date('Y-m-d') . '.csv');
		$out = fopen('php://output', 'w');
		fputcsv($out, array('name', 'phone', 'username', 'registered', 'last_sign_in', 'sign_in_count', 'notifications'));
		foreach ($rows as $row)
		{
			fputcsv($out, array(
				$row['name'],
				isset($row['phone']) ? $row['phone'] : '',
				$row['username'],
				isset($row['created_at']) ? $row['created_at'] : '',
				$row['last_login_at_display'],
				$row['login_count'],
				$row['notify_status'],
			));
		}
		fclose($out);
		exit;
	}

	protected function is_safe_admin_return($url)
	{
		if ($url === '')
		{
			return FALSE;
		}
		$base = rtrim(base_url(), '/');
		if (strpos($url, $base) === 0)
		{
			$path = substr($url, strlen($base));
		}
		else
		{
			$path = $url;
		}
		if ($path === '' || $path[0] !== '/')
		{
			return FALSE;
		}
		return (bool) preg_match('#/(admin|index\.php/admin)(/|$)#', $path);
	}

	public function history_export()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$filters = array(
			'warning'   => trim((string) $this->input->get('warning')),
			'date_from' => $this->input->get('date_from') ? strtotime($this->input->get('date_from')) : NULL,
			'date_to'   => $this->input->get('date_to') ? strtotime($this->input->get('date_to') . ' 23:59:59') : NULL,
		);
		$rows = $this->Admin_portal_model->history_table(2000, $filters);
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=daguitan-history-' . date('Y-m-d') . '.csv');
		$out = fopen('php://output', 'w');
		fputcsv($out, array('datetime', 'water_level_m', 'trend', 'rate_cm_min', 'ett', 'warning', 'sensor', 'sync'));
		foreach ($rows as $row)
		{
			fputcsv($out, array(
				date('Y-m-d H:i:s', (int) $row['ts']),
				$row['water_level_m'],
				$row['trend_label'],
				$row['rate_cm_min'],
				$row['ett_label'],
				$row['warning_label'],
				$row['sensor_label'],
				$row['sync_status'],
			));
		}
		fclose($out);
		exit;
	}

	public function reports_export()
	{
		$this->require_admin();
		$this->load->model('Admin_portal_model');
		$ctx = $this->Admin_portal_model->portal_context();
		$summary = $this->Admin_portal_model->analytics_summary();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=daguitan-report-' . date('Y-m-d') . '.csv');
		$out = fopen('php://output', 'w');
		fputcsv($out, array('Daguitan Bridge operations report', date('c')));
		fputcsv($out, array('current_water_level_m', $ctx['monitor']['water_level_m']));
		fputcsv($out, array('warning_level', $ctx['monitor']['warning_label']));
		fputcsv($out, array('rate_cm_min', $ctx['monitor']['rate_cm_min']));
		fputcsv($out, array('ett', $ctx['monitor']['ett_label']));
		fputcsv($out, array('readings_stored', $summary['readings']));
		fputcsv($out, array('max_level_m', $summary['max_level']));
		fputcsv($out, array('min_level_m', $summary['min_level']));
		fputcsv($out, array('avg_level_m', $summary['avg_level']));
		fclose($out);
		exit;
	}

	protected function require_admin()
	{
		if ($this->session->userdata('auth_role') !== 'admin')
		{
			redirect('admin');
			exit;
		}
	}

	protected function render($section, $data)
	{
		$base = rtrim(base_url(), '/');
		$this->load->model('Notification_model');
		$notify_uid = $this->Notification_model->resolve_user_id_from_session();
		$data['notify_audience'] = 'admin';
		$data['notify_unread'] = ($notify_uid > 0)
			? $this->Notification_model->count_unread($notify_uid, 'admin')
			: 0;
		$data['notify_config'] = array(
			'listUrl'    => $base . '/index.php/api/notifications',
			'readUrl'    => $base . '/index.php/api/notifications/read',
			'readAllUrl' => $base . '/index.php/api/notifications/read_all',
			'pollMs'     => 30000,
		);
		if ( ! isset($data['portal_stats']))
		{
			$this->load->model('Admin_portal_model');
			$this->load->model('Sync_model');
			$sync = $this->Sync_model->status();
			$data['portal_stats'] = array(
				'active_alerts' => $this->Admin_portal_model->count_active_alerts(),
				'residents'     => $this->Admin_portal_model->count_residents(),
				'pending_sync'  => isset($sync['pending_total']) ? (int) $sync['pending_total'] : 0,
				'sensor_online' => isset($data['monitor']['sensor_status']) && $data['monitor']['sensor_status'] === 'online',
			);
		}
		$payload = array_merge(array(
			'base_url'      => $base . '/',
			'asset_url'     => $base . '/assets/',
			'auth_name'     => $this->session->userdata('auth_name'),
			'auth_user'     => $this->session->userdata('auth_user'),
			'admin_section' => $section,
			'admin_nav'     => $this->nav,
			'status_url'    => $base . '/index.php/api/status',
			'logout_url'    => site_url('auth/logout'),
			'public_url'    => site_url('/'),
		), $data);

		$this->load->view('dash/admin/layout', $payload);
	}
}
