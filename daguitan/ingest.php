<?php
/**
 * Space-free proxy so the ESP32 can POST without encoding the project folder name.
 * Arduino URL: http://PC_LAN_IP/ONE%20DULAG_MDRRMO/daguitan/ingest.php
 * Also copy this file to C:\xampp\htdocs\daguitan\ingest.php for:
 *   http://PC_LAN_IP/daguitan/ingest.php
 */
$dest = 'http://127.0.0.1/ONE%20DULAG_MDRRMO/index.php/api/ingest';
$body = file_get_contents('php://input');
if ($body === FALSE || $body === '')
{
	$body = http_build_query($_POST);
}

$headers = array('Content-Type: application/x-www-form-urlencoded');
foreach (array('HTTP_X_API_KEY', 'REDIRECT_HTTP_X_API_KEY') as $header)
{
	if ( ! empty($_SERVER[$header]))
	{
		$headers[] = 'X-Api-Key: ' . $_SERVER[$header];
		break;
	}
}

$ch = curl_init($dest);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
curl_setopt($ch, CURLOPT_TIMEOUT, 12);
$response = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code < 100)
{
	$code = 502;
	$response = json_encode(array('ok' => FALSE, 'error' => 'proxy_unreachable'));
}

http_response_code($code);
header('Content-Type: application/json; charset=utf-8');
echo $response;
