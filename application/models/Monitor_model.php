<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Monitor_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Manila');
		$this->config->load('monitor', TRUE);
		$this->load->model('Sync_model');
		$this->migrate_json_if_needed();
	}

	public function get_status()
	{
		$current = $this->latest_reading();
		$history = $this->recent_history(40);

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
		$yellow = (float) $this->cfg('monitor_threshold_yellow_m', 1.5);
		$red = (float) $this->cfg('monitor_threshold_red_m', 2.5);
		$ett = $online
			? $this->estimate_time_to_threshold($level, $trend['rate_cm_min'], $warning['level'], $yellow, $red)
			: array('minutes' => NULL, 'label' => $has_reading ? 'Station offline' : 'Waiting for telemetry');

		$monitor = array(
			'station'          => $this->cfg('monitor_station', 'Daguitan Bridge Monitoring Station'),
			'location'         => $this->cfg('monitor_location', 'Daguitan Bridge, Dulag, Leyte'),
			'water_level_m'    => round($level, 2),
			'trend'            => $trend['trend'],
			'trend_label'      => $trend['trend_label'],
			'rate_cm_min'      => $trend['rate_cm_min'],
			'sensor_status'    => $sensor_status,
			'sensor_label'     => $sensor_label,
			'ett_minutes'      => $ett['minutes'],
			'ett_label'        => $ett['label'],
			'warning_level'    => $warning['level'],
			'warning_label'    => $warning['label'],
			'last_updated'     => $display_time,
			'last_updated_iso' => $iso,
			'gauge_pct'        => $gauge,
			'source'           => $has_reading ? 'arduino' : 'none',
			'age_seconds'      => $has_reading ? $age : null,
		);

		$announcement = $this->announcement_from($monitor, $online, $has_reading);
		$custom = $this->published_announcement();
		if ($custom)
		{
			$announcement = $custom;
		}

		return array(
			'monitor'      => $monitor,
			'weather'      => $this->get_weather(),
			'announcement' => $announcement,
		);
	}

	public function get_weather()
	{
		$cached = $this->read_weather_cache();
		$ttl = (int) $this->cfg('weather_cache_ttl', 1800);
		$cached_wx = ($cached !== NULL) ? $cached['weather'] : NULL;
		$fresh = $cached !== NULL && (time() - (int) $cached['fetched_at']) < $ttl;
		$has_forecast = is_array($cached_wx) && ! empty($cached_wx['forecast']);
		if ($fresh && $has_forecast)
		{
			return $cached_wx;
		}

		$live = $this->fetch_open_meteo_weather();
		if ($live !== NULL)
		{
			$this->write_weather_cache($live);
			return $live;
		}

		if ($cached_wx !== NULL)
		{
			return $cached_wx;
		}

		return $this->default_weather();
	}

	protected function read_weather_cache()
	{
		$file = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'weather_cache.json';
		if ( ! is_file($file))
		{
			return NULL;
		}
		$data = json_decode((string) @file_get_contents($file), TRUE);
		if ( ! is_array($data) || empty($data['weather']))
		{
			return NULL;
		}
		return array(
			'fetched_at' => isset($data['fetched_at']) ? (int) $data['fetched_at'] : 0,
			'weather'    => $this->normalize_weather_row($data['weather']),
		);
	}

	protected function write_weather_cache(array $weather)
	{
		$file = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'weather_cache.json';
		@file_put_contents($file, json_encode(array(
			'fetched_at' => time(),
			'weather'    => $weather,
		), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	}

	protected function fetch_open_meteo_weather()
	{
		$lat = (float) $this->cfg('weather_latitude', 10.9525);
		$lon = (float) $this->cfg('weather_longitude', 125.0322);
		$url = 'https://api.open-meteo.com/v1/forecast?latitude=' . rawurlencode((string) $lat)
			. '&longitude=' . rawurlencode((string) $lon)
			. '&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,cloud_cover,surface_pressure,wind_speed_10m,wind_direction_10m'
			. '&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max'
			. '&forecast_days=7'
			. '&timezone=Asia%2FManila';

		$ctx = stream_context_create(array(
			'http' => array(
				'timeout' => 6,
				'header'  => "User-Agent: DaguitanFloodMonitor/1.0\r\n",
			),
			'ssl' => array(
				'verify_peer'      => TRUE,
				'verify_peer_name' => TRUE,
			),
		));

		$raw = @file_get_contents($url, FALSE, $ctx);
		if ($raw === FALSE)
		{
			return NULL;
		}
		$json = json_decode($raw, TRUE);
		if ( ! is_array($json) || empty($json['current']))
		{
			return NULL;
		}
		$c = $json['current'];
		$code = isset($c['weather_code']) ? (int) $c['weather_code'] : 3;
		$theme = $this->weather_theme_from_code($code);
		$wind = isset($c['wind_speed_10m']) ? (float) $c['wind_speed_10m'] : 0.0;
		$dir = isset($c['wind_direction_10m']) ? (float) $c['wind_direction_10m'] : 0.0;
		$forecast = $this->forecast_from_open_meteo(isset($json['daily']) ? $json['daily'] : array());
		$rain_chance = 0;
		if ( ! empty($forecast[0]['rain_chance']))
		{
			$rain_chance = (int) $forecast[0]['rain_chance'];
		}

		return $this->normalize_weather_row(array(
			'temp_c'        => isset($c['temperature_2m']) ? round((float) $c['temperature_2m']) : 29,
			'feels_like_c'  => isset($c['apparent_temperature']) ? round((float) $c['apparent_temperature']) : (isset($c['temperature_2m']) ? round((float) $c['temperature_2m']) : 29),
			'condition'     => $this->weather_condition_from_code($code),
			'theme'         => $theme,
			'humidity'      => isset($c['relative_humidity_2m']) ? (int) $c['relative_humidity_2m'] : 0,
			'rainfall_mm'   => isset($c['precipitation']) ? round((float) $c['precipitation'], 1) : 0.0,
			'rain_chance'   => $rain_chance,
			'cloud_pct'     => isset($c['cloud_cover']) ? (int) $c['cloud_cover'] : 0,
			'pressure_hpa'  => isset($c['surface_pressure']) ? (int) round((float) $c['surface_pressure']) : 1013,
			'wind_kmh'      => (int) round($wind),
			'wind_dir'      => $this->wind_dir_from_deg($dir),
			'weather_code'  => $code,
			'source'        => 'Open-Meteo · Dulag area',
			'forecast'      => $forecast,
		));
	}

	protected function forecast_from_open_meteo($daily)
	{
		if ( ! is_array($daily) || empty($daily['time']) || ! is_array($daily['time']))
		{
			return array();
		}

		$out = array();
		$count = min(7, count($daily['time']));
		for ($i = 0; $i < $count; $i++)
		{
			$code = isset($daily['weather_code'][$i]) ? (int) $daily['weather_code'][$i] : 3;
			$date = (string) $daily['time'][$i];
			$stamp = strtotime($date . ' 12:00:00');
			$out[] = array(
				'date'        => $date,
				'label'       => ($i === 0) ? 'Today' : date('D', $stamp ? $stamp : time()),
				'temp_max'    => isset($daily['temperature_2m_max'][$i]) ? (int) round((float) $daily['temperature_2m_max'][$i]) : 0,
				'temp_min'    => isset($daily['temperature_2m_min'][$i]) ? (int) round((float) $daily['temperature_2m_min'][$i]) : 0,
				'rain_mm'     => isset($daily['precipitation_sum'][$i]) ? round((float) $daily['precipitation_sum'][$i], 1) : 0.0,
				'rain_chance' => isset($daily['precipitation_probability_max'][$i]) ? (int) $daily['precipitation_probability_max'][$i] : 0,
				'wind_kmh'    => isset($daily['wind_speed_10m_max'][$i]) ? (int) round((float) $daily['wind_speed_10m_max'][$i]) : 0,
				'condition'   => $this->weather_condition_from_code($code),
				'theme'       => $this->weather_theme_from_code($code),
				'weather_code'=> $code,
			);
		}

		return $out;
	}

	protected function wind_dir_from_deg($deg)
	{
		$dirs = array('N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW');
		$index = (int) round(fmod((float) $deg, 360.0) / 22.5) % 16;
		if ($index < 0)
		{
			$index += 16;
		}
		return $dirs[$index];
	}

	protected function normalize_forecast($rows)
	{
		if ( ! is_array($rows))
		{
			return array();
		}

		$out = array();
		foreach ($rows as $i => $day)
		{
			if ( ! is_array($day) || count($out) >= 7)
			{
				continue;
			}
			$code = isset($day['weather_code']) ? (int) $day['weather_code'] : 3;
			$out[] = array(
				'date'         => isset($day['date']) ? (string) $day['date'] : '',
				'label'        => isset($day['label']) ? (string) $day['label'] : (($i === 0) ? 'Today' : 'Day'),
				'temp_max'     => isset($day['temp_max']) ? (int) $day['temp_max'] : 0,
				'temp_min'     => isset($day['temp_min']) ? (int) $day['temp_min'] : 0,
				'rain_mm'      => isset($day['rain_mm']) ? (float) $day['rain_mm'] : 0.0,
				'rain_chance'  => isset($day['rain_chance']) ? (int) $day['rain_chance'] : 0,
				'wind_kmh'     => isset($day['wind_kmh']) ? (int) $day['wind_kmh'] : 0,
				'condition'    => isset($day['condition']) ? (string) $day['condition'] : $this->weather_condition_from_code($code),
				'theme'        => isset($day['theme']) ? (string) $day['theme'] : $this->weather_theme_from_code($code),
				'weather_code' => $code,
			);
		}

		return $out;
	}

	protected function normalize_weather_row($row)
	{
		if ( ! is_array($row))
		{
			return $this->default_weather();
		}
		$code = isset($row['weather_code']) ? (int) $row['weather_code'] : 3;
		$theme = isset($row['theme']) ? (string) $row['theme'] : $this->weather_theme_from_code($code);
		$temp = isset($row['temp_c']) ? (int) $row['temp_c'] : 29;
		return array(
			'temp_c'       => $temp,
			'feels_like_c' => isset($row['feels_like_c']) ? (int) $row['feels_like_c'] : $temp,
			'condition'    => isset($row['condition']) ? (string) $row['condition'] : $this->weather_condition_from_code($code),
			'theme'        => $theme,
			'humidity'     => isset($row['humidity']) ? (int) $row['humidity'] : 0,
			'rainfall_mm'  => isset($row['rainfall_mm']) ? (float) $row['rainfall_mm'] : 0.0,
			'rain_chance'  => isset($row['rain_chance']) ? (int) $row['rain_chance'] : 0,
			'cloud_pct'    => isset($row['cloud_pct']) ? (int) $row['cloud_pct'] : 0,
			'pressure_hpa' => isset($row['pressure_hpa']) ? (int) $row['pressure_hpa'] : 1013,
			'wind_kmh'     => isset($row['wind_kmh']) ? (int) $row['wind_kmh'] : 0,
			'wind_dir'     => isset($row['wind_dir']) ? (string) $row['wind_dir'] : 'N',
			'weather_code' => $code,
			'source'       => isset($row['source']) ? (string) $row['source'] : 'Weather data',
			'forecast'     => $this->normalize_forecast(isset($row['forecast']) ? $row['forecast'] : array()),
		);
	}

	protected function weather_theme_from_code($code)
	{
		$code = (int) $code;
		if ($code === 0)
		{
			return 'sunny';
		}
		if (in_array($code, array(1, 2), TRUE))
		{
			return 'partly-cloudy';
		}
		if ($code === 3)
		{
			return 'cloudy';
		}
		if (in_array($code, array(45, 48), TRUE))
		{
			return 'fog';
		}
		if ($code >= 51 && $code <= 57)
		{
			return 'drizzle';
		}
		if (($code >= 61 && $code <= 67) || ($code >= 80 && $code <= 82))
		{
			return 'rainy';
		}
		if ($code >= 95)
		{
			return 'storm';
		}
		return 'cloudy';
	}

	protected function weather_condition_from_code($code)
	{
		$labels = array(
			0  => 'Clear sky',
			1  => 'Mainly clear',
			2  => 'Partly cloudy',
			3  => 'Overcast',
			45 => 'Fog',
			48 => 'Depositing rime fog',
			51 => 'Light drizzle',
			53 => 'Drizzle',
			55 => 'Dense drizzle',
			56 => 'Freezing drizzle',
			57 => 'Freezing drizzle',
			61 => 'Slight rain',
			63 => 'Rain',
			65 => 'Heavy rain',
			66 => 'Freezing rain',
			67 => 'Freezing rain',
			71 => 'Snow fall',
			80 => 'Rain showers',
			81 => 'Rain showers',
			82 => 'Violent rain showers',
			95 => 'Thunderstorm',
			96 => 'Thunderstorm with hail',
			99 => 'Thunderstorm with hail',
		);
		return isset($labels[$code]) ? $labels[$code] : 'Cloudy';
	}

	protected function published_announcement()
	{
		if ( ! $this->db->table_exists('announcements'))
		{
			return NULL;
		}

		$row = $this->db
			->where('is_published', 1)
			->order_by('updated_at', 'DESC')
			->limit(1)
			->get('announcements')
			->row_array();

		if ( ! $row)
		{
			return NULL;
		}

		$level = ($row['level'] === 'info') ? NULL : $row['level'];

		return array(
			'active' => TRUE,
			'level'  => $level,
			'title'  => $row['title'],
			'body'   => $row['body'],
			'issuer' => 'MDRRMO Dulag',
		);
	}

	public function list_published_announcements()
	{
		if ( ! $this->db->table_exists('announcements'))
		{
			return array();
		}

		return $this->db
			->where('is_published', 1)
			->order_by('updated_at', 'DESC')
			->get('announcements')
			->result_array();
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
		$uid = isset($input['record_uid']) && preg_match('/^[a-zA-Z0-9._-]{8,64}$/', (string) $input['record_uid'])
			? (string) $input['record_uid']
			: $this->Sync_model->new_uid();

		$existing = $this->db->where('record_uid', $uid)->get('water_readings')->row_array();
		if ($existing)
		{
			$sync = $this->Sync_model->try_copy_now(FALSE);
			$status = $this->get_status();
			return array(
				'ok'       => TRUE,
				'code'     => 200,
				'duplicate'=> TRUE,
				'record_uid' => $uid,
				'monitor'  => $status['monitor'],
				'sync'     => array(
					'internet' => ! empty($sync['online']),
					'message'  => $sync['message'],
				),
			);
		}

		$ok = $this->db->insert('water_readings', array(
			'record_uid'       => $uid,
			'water_level_m'    => round($level, 3),
			'distance_cm'      => isset($input['distance_cm']) ? (float) $input['distance_cm'] : NULL,
			'sensor_height_cm' => $sensor_height_cm,
			'received_at'      => $now,
			'sync_status'      => 'pending',
		));

		if ( ! $ok)
		{
			return array('ok' => FALSE, 'error' => 'write_failed', 'code' => 500);
		}

		$sync = $this->Sync_model->try_copy_now(FALSE);
		$status = $this->get_status();
		return array(
			'ok'         => TRUE,
			'code'       => 200,
			'record_uid' => $uid,
			'stored'     => 'local',
			'monitor'    => $status['monitor'],
			'sync'       => array(
				'internet' => ! empty($sync['online']),
				'message'  => $sync['message'],
			),
		);
	}

	public function get_history($limit = 200)
	{
		if ( ! $this->db->table_exists('water_readings'))
		{
			return array();
		}

		$rows = $this->db
			->order_by('received_at', 'DESC')
			->order_by('id', 'DESC')
			->limit((int) $limit)
			->get('water_readings')
			->result_array();

		$history = array();
		foreach ($rows as $row)
		{
			$history[] = array(
				'ts'            => (int) $row['received_at'],
				'water_level_m' => (float) $row['water_level_m'],
				'sync_status'   => isset($row['sync_status']) ? $row['sync_status'] : 'pending',
			);
		}

		return $history;
	}

	protected function latest_reading()
	{
		if ( ! $this->db->table_exists('water_readings'))
		{
			return array();
		}

		$row = $this->db
			->order_by('received_at', 'DESC')
			->order_by('id', 'DESC')
			->limit(1)
			->get('water_readings')
			->row_array();

		return $row ? $row : array();
	}

	protected function recent_history($limit)
	{
		$newest_first = $this->get_history($limit);
		return array_reverse($newest_first);
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

	public function estimate_time_to_threshold($level, $rate_cm_min, $warning_level, $yellow, $red)
	{
		$rate_cm_min = (float) $rate_cm_min;
		if ( ! is_finite($rate_cm_min) || $rate_cm_min <= 0.01)
		{
			return array('minutes' => NULL, 'label' => 'No rising trend');
		}
		$target = ($warning_level === 'green') ? $yellow : (($warning_level === 'yellow') ? $red : NULL);
		if ($target === NULL)
		{
			return array('minutes' => NULL, 'label' => 'At or above critical band');
		}
		$gap_cm = ($target - $level) * 100;
		if ($gap_cm <= 0)
		{
			return array('minutes' => 0, 'label' => 'Threshold reached');
		}
		$minutes = $gap_cm / $rate_cm_min;
		if ($minutes > 24 * 60)
		{
			return array('minutes' => $minutes, 'label' => '> 24 hours');
		}
		if ($minutes >= 60)
		{
			return array('minutes' => $minutes, 'label' => round($minutes / 60, 1) . ' hr');
		}
		return array('minutes' => $minutes, 'label' => round($minutes) . ' min');
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
				if (($latest['ts'] - $row['ts']) >= 60) //Change to 60 seconds for trend calculation demo
				{
					$prev = $row;
					break;
				}
			}
			$minutes = max(0.2, ($latest['ts'] - $prev['ts']) / 60);
			$rate = (($latest['water_level_m'] - $prev['water_level_m']) * 100) / $minutes;
		}

		$rate = round($rate, 4);
		if ($rate > 0.01)
		{
			return array('trend' => 'rising', 'trend_label' => 'Rising', 'rate_cm_min' => $rate);
		}
		if ($rate < -0.01)
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
			'temp_c'       => 29,
			'feels_like_c' => 32,
			'condition'    => 'Cloudy',
			'theme'        => 'cloudy',
			'humidity'     => 82,
			'rainfall_mm'  => 0.0,
			'rain_chance'  => 20,
			'cloud_pct'    => 70,
			'pressure_hpa' => 1010,
			'wind_kmh'     => 12,
			'wind_dir'     => 'NE',
			'weather_code' => 3,
			'source'       => 'Supplementary weather information',
			'forecast'     => array(),
		);
	}

	protected function cfg($key, $default = NULL)
	{
		static $overrides = NULL;
		if ($overrides === NULL)
		{
			$file = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'admin_settings.json';
			$overrides = is_file($file)
				? json_decode((string) @file_get_contents($file), TRUE)
				: array();
			if ( ! is_array($overrides))
			{
				$overrides = array();
			}
		}
		if (array_key_exists($key, $overrides))
		{
			return $overrides[$key];
		}
		$val = $this->config->item($key, 'monitor');
		return ($val === NULL) ? $default : $val;
	}

	protected function migrate_json_if_needed()
	{
		if ( ! $this->db->table_exists('water_readings'))
		{
			return;
		}

		if ((int) $this->db->count_all('water_readings') > 0)
		{
			return;
		}

		$file = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'monitor.json';
		if ( ! is_file($file))
		{
			return;
		}

		$data = json_decode((string) @file_get_contents($file), TRUE);
		if ( ! is_array($data) || empty($data['history']) || ! is_array($data['history']))
		{
			return;
		}

		$current = isset($data['current']) && is_array($data['current']) ? $data['current'] : array();
		foreach ($data['history'] as $row)
		{
			if ( ! isset($row['ts'], $row['water_level_m']))
			{
				continue;
			}
			$this->db->insert('water_readings', array(
				'record_uid'       => $this->Sync_model->new_uid(),
				'water_level_m'    => round((float) $row['water_level_m'], 3),
				'distance_cm'      => isset($current['distance_cm']) ? $current['distance_cm'] : NULL,
				'sensor_height_cm' => isset($current['sensor_height_cm']) ? $current['sensor_height_cm'] : $this->cfg('monitor_sensor_height_cm', 400),
				'received_at'      => (int) $row['ts'],
				'sync_status'      => 'pending',
			));
		}
	}
}
