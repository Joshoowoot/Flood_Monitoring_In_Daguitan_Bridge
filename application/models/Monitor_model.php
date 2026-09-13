<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Monitor_model extends CI_Model {

	protected $data_file;

	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Manila');
		$this->config->load('monitor', TRUE);
		$dir = APPPATH . 'data';
		if ( ! is_dir($dir))
		{
			@mkdir($dir, 0755, TRUE);
		}
		$this->data_file = $dir . DIRECTORY_SEPARATOR . 'monitor.json';
	}

	public function get_status()
	{
		$store = $this->read_store();
		$current = isset($store['current']) ? $store['current'] : array();
		$history = isset($store['history']) ? $store['history'] : array();

		$level = isset($current['water_level_m']) ? (float) $current['water_level_m'] : 0.0;
		$received = isset($current['received_at']) ? (int) $current['received_at'] : 0;
		$offline_after = (int) $this->cfg('monitor_offline_after', 90);
		$has_reading = $received > 0;
		$age = $has_reading ? (time() - $received) : PHP_INT_MAX;
		$online = $has_reading && $age <= $offline_after;

		$trend = $this->compute_trend($history);
		$warning = $this->classify_warning($level);

		if ( ! $has_reading)
		{
			$sensor_status = 'waiting';
			$sensor_label = 'Waiting';
		}
		elseif ($online)
		{
			$sensor_status = 'online';
			$sensor_label = 'Online';
		}
		else
		{
			$sensor_status = 'offline';
			$sensor_label = 'Offline';
		}

		$iso = $has_reading
			? date('c', $received)
			: date('c');
		$display_time = $has_reading
			? date('g:i A', $received)
			: 'Waiting for Arduino';

		$max_level = max(3.0, (float) $this->cfg('monitor_threshold_red_m', 2.5) * 1.2);
		$gauge = (int) min(100, max(4, round(($level / $max_level) * 100)));

		$monitor = array(
			'station'          => $this->cfg('monitor_station', 'Daguitan Bridge Monitoring Station'),
			'location'         => $this->cfg('monitor_location', 'Daguitan Bridge, Dulag, Leyte'),
			'water_level_m'    => round($level, 2),
			'trend'            => $trend['trend'],
			'trend_label'      => $trend['trend_label'],
			'rate_cm_min'      => $trend['rate_cm_min'],
			'sensor_status'    => $sensor_status,
			'sensor_label'     => $sensor_label,
			'warning_level'    => $warning['level'],
			'warning_label'    => $warning['label'],
			'last_updated'     => $display_time,
			'last_updated_iso' => $iso,
			'gauge_pct'        => $gauge,
			'source'           => $has_reading ? 'arduino' : 'none',
			'age_seconds'      => $has_reading ? $age : null,
		);

		return array(
			'monitor'      => $monitor,
			'weather'      => $this->default_weather(),
			'announcement' => $this->announcement_from($monitor, $online, $has_reading),
		);
	}

	public function ingest($input)
	{
		$key_ok = $this->cfg('monitor_api_key', '');
		$provided = '';
		if (isset($input['api_key']))
		{
			$provided = (string) $input['api_key'];
		}
		elseif (isset($_SERVER['HTTP_X_API_KEY']))
		{
			$provided = (string) $_SERVER['HTTP_X_API_KEY'];
		}

		if ($key_ok === '' || ! hash_equals($key_ok, $provided))
		{
			return array('ok' => FALSE, 'error' => 'unauthorized', 'code' => 401);
		}

		$sensor_height_cm = isset($input['sensor_height_cm'])
			? (float) $input['sensor_height_cm']
			: (float) $this->cfg('monitor_sensor_height_cm', 400);

		$level = NULL;
		if (isset($input['water_level_m']) && $input['water_level_m'] !== '')
		{
			$level = (float) $input['water_level_m'];
		}
		elseif (isset($input['distance_cm']) && $input['distance_cm'] !== '')
		{
			$distance = (float) $input['distance_cm'];
			$level = ($sensor_height_cm - $distance) / 100.0;
		}

		if ($level === NULL)
		{
			return array('ok' => FALSE, 'error' => 'missing_reading', 'code' => 400);
		}

		$level = max(0, min(20, $level));
		$now = time();

		$store = $this->read_store();
		$history = isset($store['history']) ? $store['history'] : array();
		$history[] = array(
			'ts'            => $now,
			'water_level_m' => round($level, 3),
		);
		if (count($history) > 40)
		{
			$history = array_slice($history, -40);
		}

		$store['current'] = array(
			'water_level_m'     => round($level, 3),
			'distance_cm'       => isset($input['distance_cm']) ? (float) $input['distance_cm'] : NULL,
			'sensor_height_cm'  => $sensor_height_cm,
			'received_at'       => $now,
		);
		$store['history'] = $history;

		if ( ! $this->write_store($store))
		{
			return array('ok' => FALSE, 'error' => 'write_failed', 'code' => 500);
		}

		$status = $this->get_status();
		return array(
			'ok'      => TRUE,
			'code'    => 200,
			'monitor' => $status['monitor'],
		);
	}

	protected function classify_warning($level)
	{
		$red = (float) $this->cfg('monitor_threshold_red_m', 2.5);
		$yellow = (float) $this->cfg('monitor_threshold_yellow_m', 1.5);

		if ($level >= $red)
		{
			return array('level' => 'red', 'label' => 'Critical');
		}
		if ($level >= $yellow)
		{
			return array('level' => 'yellow', 'label' => 'Monitor');
		}
		return array('level' => 'green', 'label' => 'Safe');
	}

	protected function compute_trend($history)
	{
		$rate = 0.0;
		if (count($history) >= 2)
		{
			$latest = $history[count($history) - 1];
			$prev = $history[0];
			foreach (array_reverse($history) as $row)
			{
				if (($latest['ts'] - $row['ts']) >= 60)
				{
					$prev = $row;
					break;
				}
			}
			$minutes = max(0.2, ($latest['ts'] - $prev['ts']) / 60);
			$rate = (($latest['water_level_m'] - $prev['water_level_m']) * 100) / $minutes;
		}

		$rate = round($rate, 2);
		if ($rate > 0.03)
		{
			return array('trend' => 'rising', 'trend_label' => 'Rising', 'rate_cm_min' => $rate);
		}
		if ($rate < -0.03)
		{
			return array('trend' => 'falling', 'trend_label' => 'Falling', 'rate_cm_min' => $rate);
		}
		return array('trend' => 'steady', 'trend_label' => 'Steady', 'rate_cm_min' => $rate);
	}

	protected function announcement_from($monitor, $online, $has_reading)
	{
		if ( ! $has_reading)
		{
			return array(
				'active' => FALSE,
				'level'  => NULL,
				'title'  => 'Waiting for Arduino',
				'body'   => 'The monitoring station has not sent a reading yet. Power the ESP32 and confirm Wi-Fi and the ingest URL.',
				'issuer' => 'MDRRMO Dulag',
			);
		}

		if ( ! $online)
		{
			return array(
				'active' => TRUE,
				'level'  => 'yellow',
				'title'  => 'Sensor Offline',
				'body'   => 'No recent reading from the Daguitan Bridge station. Last known water level is shown until the Arduino reconnects.',
				'issuer' => 'MDRRMO Dulag',
			);
		}

		if ($monitor['warning_level'] === 'red')
		{
			return array(
				'active' => TRUE,
				'level'  => 'red',
				'title'  => 'Critical Flood Warning',
				'body'   => 'Water at Daguitan Bridge has reached the critical threshold. Follow MDRRMO instructions and stay away from the river.',
				'issuer' => 'MDRRMO Dulag',
			);
		}

		if ($monitor['warning_level'] === 'yellow')
		{
			return array(
				'active' => TRUE,
				'level'  => 'yellow',
				'title'  => 'Flood Advisory',
				'body'   => 'Water is approaching caution levels at Daguitan Bridge. Stay alert and monitor updates.',
				'issuer' => 'MDRRMO Dulag',
			);
		}

		return array(
			'active' => FALSE,
			'level'  => NULL,
			'title'  => 'No Active Emergency Announcement',
			'body'   => 'There is currently no emergency announcement for residents near Daguitan Bridge. Continue to monitor water levels during heavy rainfall.',
			'issuer' => 'MDRRMO Dulag',
		);
	}

	protected function default_weather()
	{
		return array(
			'temp_c'      => 29,
			'condition'   => 'Cloudy',
			'humidity'    => 82,
			'rainfall_mm' => 2.4,
			'wind_kmh'    => 12,
			'source'      => 'Supplementary weather information',
		);
	}

	protected function cfg($key, $default = NULL)
	{
		$val = $this->config->item($key, 'monitor');
		return ($val === NULL) ? $default : $val;
	}

	protected function read_store()
	{
		if ( ! is_file($this->data_file))
		{
			return array('current' => array(), 'history' => array());
		}
		$raw = @file_get_contents($this->data_file);
		$data = json_decode($raw, TRUE);
		if ( ! is_array($data))
		{
			return array('current' => array(), 'history' => array());
		}
		return $data;
	}

	protected function write_store($store)
	{
		$json = json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		$fp = @fopen($this->data_file, 'c+');
		if ( ! $fp)
		{
			return FALSE;
		}
		if ( ! flock($fp, LOCK_EX))
		{
			fclose($fp);
			return FALSE;
		}
		ftruncate($fp, 0);
		rewind($fp);
		$ok = fwrite($fp, $json) !== FALSE;
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);
		return $ok;
	}
}
