<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Api extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Monitor_model');
		$this->output->set_header('Access-Control-Allow-Origin: *');
		$this->output->set_header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');
		$this->output->set_header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
	}

	public function status()
	{
		if ($this->input->method(TRUE) === 'OPTIONS')
		{
			return $this->json(array('ok' => TRUE), 204);
		}

		$payload = $this->Monitor_model->get_status();
		$this->load->model('Admin_portal_model');
		$payload['monitor'] = $this->Admin_portal_model->enrich_monitor($payload['monitor']);
		$this->load->model('Sync_model');
		$payload['sync'] = $this->Sync_model->status();
		return $this->json(array('ok' => TRUE) + $payload);
	}

	public function notifications()
	{
		if ($this->input->method(TRUE) === 'OPTIONS')
		{
			return $this->json(array('ok' => TRUE), 204);
		}
		if ($this->input->method(TRUE) !== 'GET')
		{
			return $this->json(array('ok' => FALSE, 'error' => 'method_not_allowed'), 405);
		}

		$this->load->model('Notification_model');
		$user_id = $this->Notification_model->resolve_user_id_from_session();
		$role = (string) $this->session->userdata('auth_role');
		if ($user_id <= 0 || ! in_array($role, array('admin', 'user'), TRUE))
		{
			return $this->json(array('ok' => FALSE, 'error' => 'unauthorized'), 401);
		}

		return $this->json(array(
			'ok'     => TRUE,
			'unread' => $this->Notification_model->count_unread($user_id, $role),
			'items'  => $this->Notification_model->list_for_user($user_id, $role, 25),
		));
	}

	public function notifications_read()
	{
		if ($this->input->method(TRUE) === 'OPTIONS')
		{
			return $this->json(array('ok' => TRUE), 204);
		}
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->json(array('ok' => FALSE, 'error' => 'method_not_allowed'), 405);
		}

		$this->load->model('Notification_model');
		$user_id = $this->Notification_model->resolve_user_id_from_session();
		$role = (string) $this->session->userdata('auth_role');
		if ($user_id <= 0 || ! in_array($role, array('admin', 'user'), TRUE))
		{
			return $this->json(array('ok' => FALSE, 'error' => 'unauthorized'), 401);
		}

		$id = (int) $this->input->post('id');
		if ($id <= 0)
		{
			return $this->json(array('ok' => FALSE, 'error' => 'invalid_id'), 400);
		}

		$this->Notification_model->mark_read($id, $user_id);
		return $this->json(array(
			'ok'     => TRUE,
			'unread' => $this->Notification_model->count_unread($user_id, $role),
		));
	}

	public function notifications_read_all()
	{
		if ($this->input->method(TRUE) === 'OPTIONS')
		{
			return $this->json(array('ok' => TRUE), 204);
		}
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->json(array('ok' => FALSE, 'error' => 'method_not_allowed'), 405);
		}

		$this->load->model('Notification_model');
		$user_id = $this->Notification_model->resolve_user_id_from_session();
		$role = (string) $this->session->userdata('auth_role');
		if ($user_id <= 0 || ! in_array($role, array('admin', 'user'), TRUE))
		{
			return $this->json(array('ok' => FALSE, 'error' => 'unauthorized'), 401);
		}

		$this->Notification_model->mark_all_read($user_id, $role);
		return $this->json(array(
			'ok'     => TRUE,
			'unread' => 0,
		));
	}

	public function sync()
	{
		if ($this->input->method(TRUE) === 'OPTIONS')
		{
			return $this->json(array('ok' => TRUE), 204);
		}

		$this->load->model('Sync_model');
		$flush = $this->Sync_model->flush();
		$status = $this->Sync_model->status();
		return $this->json(array('ok' => TRUE, 'status' => $status, 'flush' => $flush));
	}

	public function ingest()
	{
		if ($this->input->method(TRUE) === 'OPTIONS')
		{
			return $this->json(array('ok' => TRUE), 204);
		}

		if ( ! in_array($this->input->method(TRUE), array('POST', 'PUT'), TRUE))
		{
			return $this->json(array('ok' => FALSE, 'error' => 'method_not_allowed'), 405);
		}

		$input = $this->collect_input();
		$result = $this->Monitor_model->ingest($input);
		$code = isset($result['code']) ? (int) $result['code'] : 200;
		unset($result['code']);
		return $this->json($result, $code);
	}

	protected function collect_input()
	{
		$input = array();
		$raw = $this->input->raw_input_stream;
		if (is_string($raw) && $raw !== '')
		{
			$json = json_decode($raw, TRUE);
			if (is_array($json))
			{
				$input = $json;
			}
			else
			{
				parse_str($raw, $parsed);
				if (is_array($parsed))
				{
					$input = $parsed;
				}
			}
		}

		foreach (array('api_key', 'water_level_m', 'distance_cm', 'sensor_height_cm', 'record_uid') as $key)
		{
			$posted = $this->input->post($key);
			if ($posted !== NULL && $posted !== FALSE && $posted !== '')
			{
				$input[$key] = $posted;
			}
			$get = $this->input->get($key);
			if (( ! isset($input[$key]) || $input[$key] === '') && $get !== NULL && $get !== FALSE && $get !== '')
			{
				$input[$key] = $get;
			}
		}

		// ESP32 / Apache header variants
		if (empty($input['api_key']))
		{
			foreach (array('HTTP_X_API_KEY', 'REDIRECT_HTTP_X_API_KEY') as $header)
			{
				if ( ! empty($_SERVER[$header]))
				{
					$input['api_key'] = $_SERVER[$header];
					break;
				}
			}
		}

		return $input;
	}

	protected function json($data, $status = 200)
	{
		return $this->output
			->set_status_header($status)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}
}
