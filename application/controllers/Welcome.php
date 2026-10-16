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

	public function announcement_image($name = '')
	{
		if ( ! is_string($name) || ! preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/', $name))
		{
			show_404();
			return;
		}

		$path = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'announcement_uploads' . DIRECTORY_SEPARATOR . $name;
		if ( ! is_file($path) || ! is_readable($path))
		{
			show_404();
			return;
		}

		$image = @getimagesize($path);
		$allowed_mime_types = array('image/jpeg', 'image/png', 'image/webp');
		if ( ! is_array($image) || empty($image['mime']) || ! in_array($image['mime'], $allowed_mime_types, TRUE))
		{
			log_message('error', 'Refused to serve invalid announcement image ' . $name . '.');
			show_404();
			return;
		}

		$content = file_get_contents($path);
		if ($content === FALSE)
		{
			log_message('error', 'Could not read announcement image ' . $name . '.');
			show_404();
			return;
		}
		$this->output
			->set_status_header(200)
			->set_content_type($image['mime'])
			->set_header('X-Content-Type-Options: nosniff')
			->set_header('Cache-Control: public, max-age=86400, immutable')
			->set_output($content);
	}

	public function hazard_image($name = '')
	{
		if ( ! is_string($name) || ! preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/', $name))
		{
			show_404();
			return;
		}

		$path = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'hazard_uploads' . DIRECTORY_SEPARATOR . $name;
		if ( ! is_file($path) || ! is_readable($path))
		{
			show_404();
			return;
		}

		$image = @getimagesize($path);
		$allowed_mime_types = array('image/jpeg', 'image/png', 'image/webp');
		if ( ! is_array($image) || empty($image['mime']) || ! in_array($image['mime'], $allowed_mime_types, TRUE))
		{
			log_message('error', 'Refused to serve invalid hazard image ' . $name . '.');
			show_404();
			return;
		}

		$content = file_get_contents($path);
		if ($content === FALSE)
		{
			log_message('error', 'Could not read hazard image ' . $name . '.');
			show_404();
			return;
		}
		$this->output
			->set_status_header(200)
			->set_content_type($image['mime'])
			->set_header('X-Content-Type-Options: nosniff')
			->set_header('Cache-Control: public, max-age=86400, immutable')
			->set_output($content);
	}

	public function about()
	{
		$data = $this->public_data();
		$data['page_title'] = 'About the System';
		$data['nav_page'] = 'about';
		$this->load->view('about', $data);
	}

	protected function public_data()
	{
		$this->load->helper('url');
		$this->load->helper('auth_session');
		maintain_admin_session($this);
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
			'base_url'          => $base . '/',
			'asset_url'         => $base . '/assets/',
			'status_url'        => $base . '/index.php/api/status',
			'ingest_url'        => $base . '/index.php/api/ingest',
			'home_url'          => site_url(),
			'announcements_url' => site_url('announcements'),
			'about_url'         => site_url('about'),
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

		$role = $this->session->userdata('auth_role');
		if ($role === 'user' || $role === 'admin')
		{
			$this->load->model('Notification_model');
			$user_id = $this->Notification_model->resolve_user_id_from_session();
			$data['notify_audience'] = $role;
			$data['notify_unread'] = ($user_id > 0)
				? $this->Notification_model->count_unread($user_id, $role)
				: 0;
			$data['notify_config'] = array(
				'listUrl' => $base . '/index.php/api/notifications',
				'readUrl' => $base . '/index.php/api/notifications/read',
				'readAllUrl' => $base . '/index.php/api/notifications/read_all',
				'pushStatusUrl' => $base . '/index.php/api/notifications/push_status',
				'pushSubscriptionUrl' => $base . '/index.php/api/notifications/push_subscription',
				'serviceWorkerUrl' => $base . '/sw.js',
				'pollMs' => 5000,
			);
		}

		return $data;
	}
}
