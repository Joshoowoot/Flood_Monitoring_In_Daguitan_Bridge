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

		$barangay = ($this->session->userdata('auth_role') === 'user')
			? (string) $this->session->userdata('auth_barangay')
			: NULL;
		$payload = $this->Monitor_model->get_status($barangay);
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

		if ( ! $this->Notification_model->mark_read($id, $user_id))
		{
			return $this->json(array('ok' => FALSE, 'error' => 'notification_not_accessible'), 404);
		}
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

		if ( ! $this->Notification_model->mark_all_read($user_id, $role))
		{
			return $this->json(array('ok' => FALSE, 'error' => 'notifications_not_updated'), 500);
		}
		return $this->json(array(
			'ok'     => TRUE,
			'unread' => 0,
		));
	}

	public function notifications_push_status()
	{
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

		$this->load->library('web_push_service');
		return $this->json(array(
			'ok' => TRUE,
			'push' => $this->web_push_service->client_config(),
		));
	}

	public function notifications_push_subscription()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->json(array('ok' => FALSE, 'error' => 'method_not_allowed'), 405);
		}
		if ( ! $this->is_same_origin_request())
		{
			return $this->json(array('ok' => FALSE, 'error' => 'invalid_origin'), 403);
		}

		$this->load->model('Notification_model');
		$user_id = $this->Notification_model->resolve_user_id_from_session();
		$role = (string) $this->session->userdata('auth_role');
		if ($user_id <= 0 || ! in_array($role, array('admin', 'user'), TRUE))
		{
			return $this->json(array('ok' => FALSE, 'error' => 'unauthorized'), 401);
		}

		$request = json_decode($this->input->raw_input_stream, TRUE);
		if ( ! is_array($request) || ! isset($request['action']))
		{
			return $this->json(array('ok' => FALSE, 'error' => 'invalid_request'), 400);
		}

		if ($request['action'] === 'subscribe')
		{
			$this->load->library('web_push_service');
			$push = $this->web_push_service->client_config();
			if (empty($push['configured']))
			{
				return $this->json(array('ok' => FALSE, 'error' => 'push_not_configured'), 503);
			}
			if (empty($request['subscription']) || ! is_array($request['subscription']))
			{
				return $this->json(array('ok' => FALSE, 'error' => 'invalid_subscription'), 400);
			}
			$saved = $this->Notification_model->save_push_subscription($user_id, $role, $request['subscription']);
			if ( ! $saved)
			{
				log_message('error', 'Could not save web push subscription for user ' . $user_id . '.');
				return $this->json(array('ok' => FALSE, 'error' => 'subscription_not_saved'), 400);
			}
			return $this->json(array('ok' => TRUE));
		}

		if ($request['action'] === 'unsubscribe')
		{
			$endpoint = isset($request['endpoint']) ? (string) $request['endpoint'] : '';
			if ($endpoint === '' || strlen($endpoint) > 2048)
			{
				return $this->json(array('ok' => FALSE, 'error' => 'invalid_endpoint'), 400);
			}
			if ( ! $this->Notification_model->delete_push_subscription($user_id, $endpoint))
			{
				log_message('error', 'Could not remove web push subscription for user ' . $user_id . '.');
				return $this->json(array('ok' => FALSE, 'error' => 'subscription_not_removed'), 500);
			}
			return $this->json(array('ok' => TRUE));
		}

		return $this->json(array('ok' => FALSE, 'error' => 'invalid_action'), 400);
	}

	protected function is_same_origin_request()
	{
		$origin = $this->input->server('HTTP_ORIGIN');
		$base = parse_url(base_url());
		$source = is_string($origin) ? parse_url($origin) : FALSE;
		if ( ! is_array($base) || ! is_array($source) || empty($base['scheme']) || empty($base['host']) || empty($source['scheme']) || empty($source['host']))
		{
			return FALSE;
		}

		$base_port = isset($base['port']) ? (int) $base['port'] : (strtolower($base['scheme']) === 'https' ? 443 : 80);
		$source_port = isset($source['port']) ? (int) $source['port'] : (strtolower($source['scheme']) === 'https' ? 443 : 80);
		return strtolower($base['scheme']) === strtolower($source['scheme'])
			&& strtolower($base['host']) === strtolower($source['host'])
			&& $base_port === $source_port;
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
