<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_service {
	protected $api_url = 'https://sms.iprogtech.com/api/v1/sms_messages';

	public function send_broadcast($recipients, $message)
	{
		$api_token = trim((string) getenv('IPROG_API_TOKEN'));
		if ($api_token === '')
		{
			$api_token = trim((string) getenv('IPROG_SMS_API_TOKEN'));
		}
		if ($api_token === '' && function_exists('get_instance'))
		{
			$CI =& get_instance();
			$CI->config->load('sms_credentials', TRUE);
			$api_token = trim((string) $CI->config->item('api_token', 'sms_credentials'));
		}
		if ($api_token === '')
		{
			return array(
				'ok' => FALSE,
				'sent' => 0,
				'error' => 'SMS cannot be sent because the iProg API token is not configured on the server.',
			);
		}
		if ( ! function_exists('curl_init'))
		{
			return array('ok' => FALSE, 'sent' => 0, 'error' => 'SMS cannot be sent because the PHP cURL extension is not enabled.');
		}

		$total = count($recipients);
		$sent = 0;
		$results = array();
		foreach ($recipients as $recipient)
		{
			$phone = $this->normalize_phone($recipient['phone'] ?? '');
			if ($phone === '')
			{
				$results[] = array(
					'name' => isset($recipient['name']) ? (string) $recipient['name'] : '',
					'phone' => '',
					'ok' => FALSE,
					'message_id' => '',
					'error' => 'Missing phone number.',
				);
				continue;
			}

			$payload = array(
				'api_token' => $api_token,
				'phone_number' => $phone,
				'message' => $message,
				'sms_provider' => 2,
			);

			$ch = curl_init($this->api_url);
			curl_setopt_array($ch, array(
				CURLOPT_POST => TRUE,
				CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_HTTPHEADER => array(
					'Accept: application/json',
					'Content-Type: application/json; charset=UTF-8',
				),
			));
			$response = curl_exec($ch);
			$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_error = curl_error($ch);
			curl_close($ch);

			$decoded = $response !== FALSE ? json_decode($response, TRUE) : NULL;
			$api_status = is_array($decoded) && isset($decoded['status']) ? $decoded['status'] : NULL;
			$api_ok = $response !== FALSE
				&& $http_code >= 200
				&& $http_code < 300
				&& ((string) $api_status === '200' || strtolower((string) $api_status) === 'success');
			$result_error = '';
			if ($response === FALSE)
			{
				$result_error = 'Could not connect to the iProg SMS gateway' . ($curl_error !== '' ? ': ' . $curl_error : '.');
			}
			elseif ($http_code < 200 || $http_code >= 300)
			{
				$result_error = 'iProg returned HTTP ' . $http_code . '.';
			}
			elseif ( ! $api_ok)
			{
				$provider_message = is_array($decoded) && isset($decoded['message']) && is_scalar($decoded['message'])
					? trim((string) $decoded['message'])
					: '';
				$result_error = $provider_message !== ''
					? $provider_message
					: 'iProg did not confirm that the message was queued. Check the token, account credits, and recipient number.';
			}

			$results[] = array(
				'name' => isset($recipient['name']) ? (string) $recipient['name'] : '',
				'phone' => $phone,
				'ok' => $api_ok,
				'message_id' => $api_ok && is_array($decoded) && isset($decoded['message_id'])
					? (string) $decoded['message_id']
					: '',
				'error' => $result_error,
			);
			if ($api_ok)
			{
				$sent++;
			}
		}

		$failed = $total - $sent;
		if ($total === 0)
		{
			return array(
				'ok' => FALSE,
				'sent' => $sent,
				'total' => $total,
				'results' => $results,
				'error' => 'No recipients were provided.',
			);
		}

		return array(
			'ok' => $sent === $total && $total > 0,
			'sent' => $sent,
			'total' => $total,
			'results' => $results,
			'error' => $failed > 0 ? $sent . ' of ' . $total . ' messages queued by iProg; ' . $failed . ' failed.' : '',
		);
	}

	protected function normalize_phone($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);
		if ($digits === '')
		{
			return '';
		}
		if (strncmp($digits, '63', 2) === 0)
		{
			return $digits;
		}
		if (strncmp($digits, '0', 1) === 0)
		{
			return '63' . substr($digits, 1);
		}
		return '63' . $digits;
	}
}