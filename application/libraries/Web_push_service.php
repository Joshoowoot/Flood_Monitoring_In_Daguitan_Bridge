<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class Web_push_service
{
	protected $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	public function client_config()
	{
		$keys = $this->keys();
		$autoload = FCPATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
		$configured = is_file($autoload)
			&& strlen($this->decode_base64url($keys['public_key'])) === 65
			&& strlen($this->decode_base64url($keys['private_key'])) === 32
			&& preg_match('/^(https:\/\/|mailto:)/i', $keys['subject']) === 1;

		return array(
			'configured' => $configured,
			'publicKey' => $configured ? $keys['public_key'] : '',
		);
	}

	public function send_notification($notification_id, $audience, $title, $body, $link, $type, $recipient_user_id = NULL)
	{
		$this->ci->load->model('Notification_model');
		$subscriptions = $this->ci->Notification_model->push_subscriptions_for($audience, $recipient_user_id);
		if (empty($subscriptions))
		{
			return array('sent' => 0, 'failed' => 0);
		}

		$keys = $this->keys();
		$autoload = FCPATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
		if ( ! is_file($autoload) || $keys['public_key'] === '' || $keys['private_key'] === '' || $keys['subject'] === '')
		{
			log_message('error', 'Web push delivery is not configured; notification ' . (int) $notification_id . ' remains available in the in-app feed.');
			return array('sent' => 0, 'failed' => count($subscriptions));
		}

		require_once $autoload;
		$web_push = new WebPush(array(
			'VAPID' => array(
				'subject' => $keys['subject'],
				'publicKey' => $keys['public_key'],
				'privateKey' => $keys['private_key'],
			),
		), array('TTL' => 86400, 'urgency' => in_array($type, array('flood_alert', 'admin_flood_alert'), TRUE) ? 'high' : 'normal'));
		$web_push->setReuseVAPIDHeaders(TRUE);

		$payload_body = mb_substr((string) $body, 0, 180, 'UTF-8');
		$payload = json_encode(array(
			'notificationId' => (int) $notification_id,
			'title' => mb_substr((string) $title, 0, 120, 'UTF-8'),
			'body' => $payload_body,
			'url' => $this->same_origin_url($link),
			'tag' => 'daguitan-' . (int) $notification_id,
		), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if ( ! is_string($payload))
		{
			log_message('error', 'Web push payload encoding failed for notification ' . (int) $notification_id . '.');
			return array('sent' => 0, 'failed' => count($subscriptions));
		}

		foreach ($subscriptions as $row)
		{
			$web_push->queueNotification(Subscription::create(array(
				'endpoint' => $row['endpoint'],
				'publicKey' => $row['public_key'],
				'authToken' => $row['auth_token'],
				'contentEncoding' => $row['content_encoding'],
			)), $payload);
		}

		$sent = 0;
		$failed = 0;
		foreach ($web_push->flush() as $report)
		{
			if ($report->isSuccess())
			{
				$sent++;
				continue;
			}

			$failed++;
			if ($report->isSubscriptionExpired())
			{
				$this->ci->Notification_model->delete_push_subscription_by_endpoint($report->getEndpoint());
			}
			log_message('error', 'Web push delivery failed for notification ' . (int) $notification_id . ': ' . $report->getReason());
		}

		return array('sent' => $sent, 'failed' => $failed);
	}

	protected function keys()
	{
		$local_file = APPPATH . 'config' . DIRECTORY_SEPARATOR . 'web_push_keys.php';
		$local = is_file($local_file) ? require $local_file : array();
		$local = is_array($local) ? $local : array();

		return array(
			'public_key' => trim((string) getenv('DAGUITAN_VAPID_PUBLIC_KEY')) ?: (isset($local['public_key']) ? trim((string) $local['public_key']) : ''),
			'private_key' => trim((string) getenv('DAGUITAN_VAPID_PRIVATE_KEY')) ?: (isset($local['private_key']) ? trim((string) $local['private_key']) : ''),
			'subject' => trim((string) getenv('DAGUITAN_VAPID_SUBJECT')) ?: (isset($local['subject']) ? trim((string) $local['subject']) : ''),
		);
	}

	protected function decode_base64url($value)
	{
		if ( ! is_string($value) || $value === '')
		{
			return '';
		}
		$decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), TRUE);
		return is_string($decoded) ? $decoded : '';
	}

	protected function same_origin_url($link)
	{
		$base = base_url();
		$base_parts = parse_url($base);
		$link_parts = parse_url((string) $link);
		$base_port = is_array($base_parts) && isset($base_parts['port'])
			? (int) $base_parts['port']
			: (is_array($base_parts) && isset($base_parts['scheme']) && strtolower($base_parts['scheme']) === 'https' ? 443 : 80);
		$link_port = is_array($link_parts) && isset($link_parts['port'])
			? (int) $link_parts['port']
			: (is_array($link_parts) && isset($link_parts['scheme']) && strtolower($link_parts['scheme']) === 'https' ? 443 : 80);
		if ( ! is_array($base_parts) || ! isset($base_parts['scheme'], $base_parts['host'])
			|| ! is_array($link_parts)
			|| (isset($link_parts['host']) && strtolower($link_parts['host']) !== strtolower($base_parts['host']))
			|| (isset($link_parts['scheme']) && strtolower($link_parts['scheme']) !== strtolower($base_parts['scheme']))
			|| (isset($link_parts['host']) && $link_port !== $base_port))
		{
			return $base;
		}

		return isset($link_parts['scheme']) ? (string) $link : site_url(ltrim((string) $link, '/'));
	}
}
