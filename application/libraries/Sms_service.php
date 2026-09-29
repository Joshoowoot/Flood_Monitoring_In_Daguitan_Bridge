<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_service {
	protected $api_url = 'https://www.iprogsms.com/api/v1/sms_messages';

	public function send_broadcast($recipients, $message)
	{
		$api_token = trim((string) getenv('IPROG_API_TOKEN'));
		if ($api_token === '')
		{
			$api_token = trim((string) getenv('IPROG_SMS_API_TOKEN'));
		}
		if ($api_token === '')
		{
			$api_token = 'a56ded39176f943077111894237b5e3578c557f8';
		}
		if ( ! function_exists('curl_init'))
		{
			return array('ok' => FALSE, 'sent' => 0, 'error' => 'SMS cannot be sent because the PHP cURL extension is not enabled.');
		}

		$total = count($recipients);
		$sent = 0;
		$failures = array();
		$gateway_error = '';
		foreach ($recipients as $recipient)
		{
			$phone = $this->normalize_phone($recipient['phone'] ?? '');
			if ($phone === '')
			{
				$failures[] = 'Missing phone number';
				continue;
			}

			$payload = array(
				'api_token' => $api_token,
				'phone_number' => $phone,
				'message' => $message,
			);

			$ch = curl_init($this->api_url);
			curl_setopt_array($ch, array(
				CURLOPT_POST => TRUE,
				CURLOPT_POSTFIELDS => http_build_query($payload),
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_HTTPHEADER => array(
					'Accept: application/json',
					'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
				),
			));
			$response = curl_exec($ch);
			$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_error = curl_error($ch);
			curl_close($ch);

			$decoded = json_decode($response, TRUE);
			$api_ok = $response !== FALSE && $http_code >= 200 && $http_code < 300;
			if (is_array($decoded) && isset($decoded['status']))
			{
				$api_ok = $api_ok && ((int) $decoded['status'] === 200 || (string) $decoded['status'] === '200');
			}
			if ($api_ok)
			{
				$sent++;
			}
			else
			{
				$failures[] = $phone;
				if ($gateway_error === '')
				{
					if ($response === FALSE)
					{
						$gateway_error = 'Could not connect to the SMS gateway' . ($curl_error !== '' ? ': ' . $curl_error : '.') ;
					}
					elseif ($http_code < 200 || $http_code >= 300)
					{
						$gateway_error = 'The SMS gateway returned HTTP ' . $http_code . '. Check the API token and provider account.';
					}
					else
					{
						$gateway_error = 'The SMS gateway rejected the request. Check the API token, account balance, and recipient numbers.';
					}
				}
			}
		}

		if ($sent === 0 && $total > 0)
		{
			return array(
				'ok' => FALSE,
				'sent' => 0,
				'error' => $gateway_error !== '' ? $gateway_error : 'The SMS gateway rejected the request. Check the API token and phone numbers, then try again.',
			);
		}

		return array(
			'ok' => $sent === $total,
			'sent' => $sent,
			'error' => $sent . ' of ' . $total . ' SMS message(s) were accepted by the gateway.' . ($sent < $total ? ' Some recipients were rejected.' : ''),
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