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
		$barangay = (string) $this->session->userdata('auth_barangay');
		$live = $this->Monitor_model->get_status($barangay);
		$this->load->model('Evacuation_center_model');
		$evacuation_centers = array_values(array_filter($this->Evacuation_center_model->all(), function ($center) {
			return ! empty($center['active']);
		}));
		$this->load->model('Hazard_model');
		$hazards = array_values(array_filter($this->Hazard_model->all(), function ($hazard) {
			return ! empty($hazard['active']);
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
			'resident_barangay' => $barangay,
			'announcements' => $this->Monitor_model->list_published_announcements($barangay),
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
			'hazards' => $hazards,
			'hazard_types' => $this->Hazard_model->types(),
		));
	}

	public function announcements()
	{
		$barangay = (string) $this->session->userdata('auth_barangay');
		$live = $this->Monitor_model->get_status($barangay);
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
			'resident_barangay' => $barangay,
			'page_title'        => 'Resident Announcements',
			'resident_portal'   => TRUE,
			'nav_page'          => 'announcements',
			'monitor'           => $live['monitor'],
			'weather'           => $live['weather'],
			'announcement'      => $live['announcement'],
			'announcements'     => $this->Monitor_model->list_published_announcements($barangay),
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

	public function reports()
	{
		$this->load->model('Community_report_model');
		$this->load->model('Hazard_model');
		$user_id = (int) $this->session->userdata('auth_id');
		$error = '';
		$form_values = array(
			'report_type' => '',
			'barangay' => (string) $this->session->userdata('auth_barangay'),
			'landmark' => '',
			'description' => '',
		);

		if ( ! $this->session->userdata('community_report_token'))
		{
			$this->session->set_userdata('community_report_token', bin2hex(random_bytes(32)));
		}

		if ($this->input->method(TRUE) === 'POST')
		{
			$posted_token = (string) $this->input->post('report_token');
			$session_token = (string) $this->session->userdata('community_report_token');
			if ($session_token === '' || ! hash_equals($session_token, $posted_token))
			{
				$error = 'This form has expired. Refresh the page and try again.';
			}
			else
			{
				foreach (array_keys($form_values) as $field)
				{
					$form_values[$field] = trim((string) $this->input->post($field, TRUE));
				}
				$photo = NULL;
				if (isset($_FILES['report_photo']) && (int) $_FILES['report_photo']['error'] !== UPLOAD_ERR_NO_FILE)
				{
					if ( ! is_dir(APPPATH . 'data' . DIRECTORY_SEPARATOR . 'report_uploads')
						&& ! @mkdir(APPPATH . 'data' . DIRECTORY_SEPARATOR . 'report_uploads', 0750, TRUE))
					{
						$error = 'Photo storage is unavailable. Remove the photo or try again later.';
					}
					else
					{
						$config = array(
							'upload_path' => APPPATH . 'data' . DIRECTORY_SEPARATOR . 'report_uploads' . DIRECTORY_SEPARATOR,
							'allowed_types' => 'jpg|jpeg|png|webp',
							'max_size' => 3072,
							'max_width' => 6000,
							'max_height' => 6000,
							'encrypt_name' => TRUE,
							'detect_mime' => TRUE,
							'remove_spaces' => TRUE,
							'file_ext_tolower' => TRUE,
						);
						$this->load->library('upload', $config);
						if ( ! $this->upload->do_upload('report_photo'))
						{
							$error = trim(strip_tags($this->upload->display_errors('', ' ')));
						}
						else
						{
							$photo = $this->upload->data();
							$mime = $this->Community_report_model->photo_mime($photo['full_path']);
							if ($mime === NULL)
							{
								@unlink($photo['full_path']);
								$photo = NULL;
								$error = 'The uploaded file is not a supported image. Use a JPEG, PNG, or WebP photo.';
							}
						}
					}
				}

				if ($error === '')
				{
					$result = $this->Community_report_model->create($user_id, $this->input->post(NULL, TRUE), $photo);
					if (empty($result['ok']))
					{
						if (is_array($photo) && ! empty($photo['full_path']) && is_file($photo['full_path']))
						{
							unlink($photo['full_path']);
						}
						$error = isset($result['error']) ? $result['error'] : 'Could not submit the report.';
					}
					else
					{
						$this->session->set_flashdata('report_notice', 'Your report was submitted. Its status is now Received.');
						redirect('portal/reports');
						return;
					}
				}
			}
		}

		$base = rtrim(base_url(), '/');
		$this->load->view('dash/community_reports', array(
			'base_url' => $base . '/',
			'asset_url' => $base . '/assets/',
			'page_title' => 'Flood and Hazard Reports',
			'auth_name' => $this->session->userdata('auth_name'),
			'resident_barangay' => (string) $this->session->userdata('auth_barangay'),
			'logout_url' => site_url('auth/logout'),
			'portal_url' => site_url('portal'),
			'barangays' => $this->Hazard_model->barangays(),
			'reports' => $this->Community_report_model->list_for_user($user_id),
			'report_statuses' => $this->Community_report_model->statuses(),
			'report_token' => $this->session->userdata('community_report_token'),
			'report_error' => $error,
			'report_notice' => $this->session->flashdata('report_notice'),
			'report_form_values' => $form_values,
			'report_photo_base' => site_url('portal/report-photo'),
		));
	}

	public function report_photo($id = 0)
	{
		if ($this->session->userdata('auth_role') !== 'user')
		{
			show_404();
			return;
		}
		$this->load->model('Community_report_model');
		$path = $this->Community_report_model->photo_path((int) $id, (int) $this->session->userdata('auth_id'));
		$mime = $path !== NULL ? $this->Community_report_model->photo_mime($path) : NULL;
		if ($path === NULL || $mime === NULL)
		{
			show_404();
			return;
		}
		$this->output
			->set_header('X-Content-Type-Options: nosniff')
			->set_header('Cache-Control: private, no-store')
			->set_content_type($mime)
			->set_output(file_get_contents($path));
	}

	public function profile()
	{
		$this->load->model('Auth_model');
		$this->load->model('Sync_model');
		$this->load->model('Hazard_model');
		$base = rtrim(base_url(), '/');
		$user_id = (int) $this->session->userdata('auth_id');
		if ($user_id < 1)
		{
			redirect('login');
			return;
		}

		$notice = $this->session->flashdata('profile_notice');
		$error = '';
		$form_values = array(
			'name' => (string) $this->session->userdata('auth_name'),
			'phone' => (string) $this->session->userdata('auth_phone'),
			'barangay' => (string) $this->session->userdata('auth_barangay'),
		);

		if ( ! $this->session->userdata('profile_form_token'))
		{
			$this->session->set_userdata('profile_form_token', bin2hex(random_bytes(32)));
		}

		if ($this->input->method(TRUE) === 'POST')
		{
			$posted_token = (string) $this->input->post('profile_form_token');
			$session_token = (string) $this->session->userdata('profile_form_token');
			if ($session_token === '' || ! hash_equals($session_token, $posted_token))
			{
				$error = 'This form has expired. Please refresh the page and try again.';
			}
			else
			{
				$action = (string) $this->input->post('action', TRUE);
				if ($action === 'profile')
				{
					$form_values['name'] = trim((string) $this->input->post('name', TRUE));
					$form_values['phone'] = trim((string) $this->input->post('phone', TRUE));
					$form_values['barangay'] = trim((string) $this->input->post('barangay', TRUE));
					$result = $this->Auth_model->update_resident_profile(
						$user_id,
						$form_values['name'],
						$form_values['phone'],
						$form_values['barangay']
					);
					if (empty($result['ok']))
					{
						$error = isset($result['error']) ? $result['error'] : 'Could not save your profile.';
					}
					else
					{
						$this->session->set_userdata(array(
							'auth_name' => $form_values['name'],
							'auth_phone' => $this->Auth_model->normalize_phone($form_values['phone']),
							'auth_barangay' => $form_values['barangay'],
						));
						$sync = $this->Sync_model->try_copy_now(FALSE);
						$this->session->set_flashdata('profile_notice', $this->profile_save_notice('Profile details updated.', $sync));
						$this->session->unset_userdata('profile_form_token');
						redirect('portal/profile');
						return;
					}
				}
				elseif ($action === 'password')
				{
					$current_password = (string) $this->input->post('current_password', FALSE);
					$new_password = (string) $this->input->post('new_password', FALSE);
					$confirm_password = (string) $this->input->post('confirm_password', FALSE);
					if ($new_password !== $confirm_password)
					{
						$error = 'The new password and confirmation do not match.';
					}
					else
					{
						$result = $this->Auth_model->change_resident_password($user_id, $current_password, $new_password);
						if (empty($result['ok']))
						{
							$error = isset($result['error']) ? $result['error'] : 'Could not update your password.';
						}
						else
						{
							$sync = $this->Sync_model->try_copy_now(FALSE);
							$this->session->set_flashdata('profile_notice', $this->profile_save_notice('Password updated.', $sync));
							$this->session->unset_userdata('profile_form_token');
							redirect('portal/profile');
							return;
						}
					}
				}
				else
				{
					$error = 'Choose a valid profile action and try again.';
				}
			}
		}

		$form_token = (string) $this->session->userdata('profile_form_token');
		$this->load->view('dash/profile', array(
			'asset_url'  => $base . '/assets/',
			'page_title' => 'My Profile',
			'auth_name'  => $this->session->userdata('auth_name'),
			'auth_user'  => $this->session->userdata('auth_user'),
			'auth_phone' => $this->session->userdata('auth_phone'),
			'auth_barangay' => $this->session->userdata('auth_barangay'),
			'barangays' => $this->Hazard_model->barangays(),
			'profile_notice' => $notice,
			'profile_error' => $error,
			'profile_form_token' => $form_token,
			'profile_form_values' => $form_values,
			'logout_url' => site_url('auth/logout'),
			'portal_url' => site_url('portal'),
		));
	}

	protected function profile_save_notice($success, $sync)
	{
		if ( ! empty($sync['online']) && ! empty($sync['ok']) && empty($sync['failed']))
		{
			return $success . ' Cloud copy synchronized.';
		}
		if (empty($sync['online']))
		{
			return $success . ' Saved locally; cloud sync will resume when a connection is available.';
		}
		return $success . ' Saved locally, but cloud sync needs attention: ' . $sync['message'];
	}

	public function help()
	{
		$base = rtrim(base_url(), '/');
		$live = $this->Monitor_model->get_status();
		$this->load->view('dash/help', array(
			'asset_url'  => $base . '/assets/',
			'page_title' => 'Help and How to Use',
			'auth_name'  => $this->session->userdata('auth_name'),
			'monitor'    => $live['monitor'],
			'status_url' => $base . '/index.php/api/status',
			'logout_url' => site_url('auth/logout'),
			'portal_url' => site_url('portal'),
		));
	}

	public function evacuation_centers()
	{
		$base = rtrim(base_url(), '/');
		$this->load->model('Evacuation_center_model');
		$this->load->model('Hazard_model');
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
			'hazards' => array_values(array_filter($this->Hazard_model->all(), function ($hazard) {
				return ! empty($hazard['active']);
			})),
			'hazard_types' => $this->Hazard_model->types(),
		));
	}
}
