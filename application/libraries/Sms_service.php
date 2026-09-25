<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_service {
	protected $api_url = 'https://api.semaphore.co/api/v4/messages';

	public function send_broadcast($recipients, $message)
	{
		$api_key = trim((string) getenv('SEMAPHORE_API_KEY'));
		$sender = trim((string) getenv('SEMAPHORE_SENDER_NAME'));
		if ($api_key === '')
		{
			return array('ok' => FALSE, 'sent' => 0, 'error' => 'SMS is not configured. Set SEMAPHORE_API_KEY in the server environment first.');
		}
		if ( ! function_exists('curl_init'))
		{
			return array('ok' => FALSE, 'sent' => 0, 'error' => 'SMS cannot be sent because the PHP cURL extension is not enabled.');
		}
		$sent = 0;
		foreach ($recipients as $recipient)
		{
			$payload = array(
				'apikey' => $api_key,
				'number' => $recipient['phone'],
				'message' => $message,
			);
			if ($sender !== '')
			{
				$payload['sendername'] = $sender;
			}
			$ch = curl_init($this->api_url);
			curl_setopt_array($ch, array(
				CURLOPT_POST => TRUE,
				CURLOPT_POSTFIELDS => http_build_query($payload),
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_TIMEOUT => 15,
			));
			$response = curl_exec($ch);
			$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if ($response !== FALSE && $http_code >= 200 && $http_code < 300)
			{
				$sent++;
			}
		}
		return array('ok' => $sent === count($recipients), 'sent' => $sent, 'error' => $sent . ' of ' . count($recipients) . ' SMS message(s) were sent.');
	}
}