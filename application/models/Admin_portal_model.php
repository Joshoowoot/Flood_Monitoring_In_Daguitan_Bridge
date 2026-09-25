<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Admin_portal_model extends CI_Model {

	protected $settings_file;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Monitor_model');
		$this->load->model('Sync_model');
		$this->settings_file = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'admin_settings.json';
		$this->ensure_schema();
	}

	public function settings()
	{
		$defaults = array(
			'monitor_threshold_yellow_m' => (float) $this->Monitor_model_cfg('monitor_threshold_yellow_m', 1.5),
			'monitor_threshold_red_m'    => (float) $this->Monitor_model_cfg('monitor_threshold_red_m', 2.5),
			'monitor_sensor_height_cm'   => (int) $this->Monitor_model_cfg('monitor_sensor_height_cm', 400),
			'monitor_offline_after'      => (int) $this->Monitor_model_cfg('monitor_offline_after', 90),
			'notify_residents_email'     => FALSE,
			'notify_residents_push'      => TRUE,
			'notify_admin_on_red'        => TRUE,
			'notify_admin_on_offline'    => TRUE,
		);
		$file = $this->read_settings_file();
		return array_merge($defaults, is_array($file) ? $file : array());
	}

	public function save_settings($input)
	{
		$current = $this->settings();
		$next = array(
			'monitor_threshold_yellow_m' => isset($input['monitor_threshold_yellow_m']) ? (float) $input['monitor_threshold_yellow_m'] : $current['monitor_threshold_yellow_m'],
			'monitor_threshold_red_m'    => isset($input['monitor_threshold_red_m']) ? (float) $input['monitor_threshold_red_m'] : $current['monitor_threshold_red_m'],
			'monitor_sensor_height_cm'   => isset($input['monitor_sensor_height_cm']) ? (int) $input['monitor_sensor_height_cm'] : $current['monitor_sensor_height_cm'],
			'monitor_offline_after'      => isset($input['monitor_offline_after']) ? (int) $input['monitor_offline_after'] : $current['monitor_offline_after'],
			'notify_residents_email'     => ! empty($input['notify_residents_email']),
			'notify_residents_push'      => ! empty($input['notify_residents_push']),
			'notify_admin_on_red'        => ! empty($input['notify_admin_on_red']),
			'notify_admin_on_offline'    => ! empty($input['notify_admin_on_offline']),
		);
		if ($next['monitor_threshold_red_m'] <= $next['monitor_threshold_yellow_m'])
		{
			return array('ok' => FALSE, 'error' => 'Critical threshold must be higher than the advisory threshold.');
		}
		@file_put_contents($this->settings_file, json_encode($next, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		return array('ok' => TRUE);
	}

	public function portal_context()
	{
		$live = $this->Monitor_model->get_status();
		$sync = $this->Sync_model->status();
		$settings = $this->settings();
		$monitor = $this->enrich_monitor($live['monitor'], $settings);
		$infra = $this->infrastructure($monitor, $sync);

		return array(
			'monitor'      => $monitor,
			'announcement' => $live['announcement'],
			'weather'      => $live['weather'],
			'sync'         => $sync,
			'settings'     => $settings,
			'infra'        => $infra,
			'thresholds'   => array(
				'yellow' => (float) $settings['monitor_threshold_yellow_m'],
				'red'    => (float) $settings['monitor_threshold_red_m'],
				'height' => (int) $settings['monitor_sensor_height_cm'],
			),
		);
	}

	public function enrich_monitor($monitor, $settings = NULL)
	{
		if ($settings === NULL)
		{
			$settings = $this->settings();
		}
		$level = isset($monitor['water_level_m']) ? (float) $monitor['water_level_m'] : 0.0;
		$rate = isset($monitor['rate_cm_min']) ? (float) $monitor['rate_cm_min'] : 0.0;
		$yellow = (float) $settings['monitor_threshold_yellow_m'];
		$red = (float) $settings['monitor_threshold_red_m'];

		$ett = $this->estimate_time_to_threshold($level, $rate, $monitor['warning_level'], $yellow, $red);
		$monitor['ett_minutes'] = $ett['minutes'];
		$monitor['ett_label'] = $ett['label'];
		$monitor['threshold_yellow_m'] = $yellow;
		$monitor['threshold_red_m'] = $red;

		return $monitor;
	}

	public function estimate_time_to_threshold($level, $rate_cm_min, $warning_level, $yellow, $red)
	{
		if ($rate_cm_min <= 0.03)
		{
			return array('minutes' => NULL, 'label' => 'Not rising');
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

	public function infrastructure($monitor, $sync)
	{
		$online = isset($monitor['sensor_status']) && $monitor['sensor_status'] === 'online';
		$internet = ! empty($sync['online']);

		return array(
			'esp32' => array(
				'label'  => 'ESP32 controller',
				'status' => $online ? 'online' : (($monitor['sensor_status'] === 'waiting') ? 'warning' : 'offline'),
				'detail' => $online ? 'Receiving packets' : 'No recent telemetry',
			),
			'ultrasonic' => array(
				'label'  => 'JSN-SR04T ultrasonic',
				'status' => $online ? 'online' : 'offline',
				'detail' => $online ? 'Distance readings OK' : 'Check sensor wiring & face',
			),
			'internet' => array(
				'label'  => 'Internet / cloud sync',
				'status' => $internet ? 'online' : 'offline',
				'detail' => isset($sync['internet_label']) ? $sync['internet_label'] : ($internet ? 'Online' : 'Offline'),
			),
			'power' => array(
				'label'  => 'Power supply',
				'status' => $online ? 'online' : 'offline',
				'detail' => $online ? 'Telemetry connected; source not reported' : 'No telemetry available',
			),
			'solar' => array(
				'label'  => 'Solar panel',
				'status' => 'warning',
				'detail' => 'Telemetry unavailable',
			),
			'battery' => array(
				'label'  => 'Battery',
				'status' => 'warning',
				'detail' => 'Telemetry unavailable',
			),
			'last_reading' => isset($monitor['last_updated']) ? $monitor['last_updated'] : '—',
			'last_error'   => ($monitor['sensor_status'] === 'offline') ? 'No packet within offline window' : NULL,
		);
	}

	public function chart_series($limit = 80, $since_ts = NULL)
	{
		$rows = array_reverse($this->Monitor_model->get_history($limit));
		$settings = $this->settings();
		$out = array();
		foreach ($rows as $row)
		{
			if ($since_ts !== NULL && (int) $row['ts'] < (int) $since_ts)
			{
				continue;
			}
			$out[] = array(
				'ts'            => (int) $row['ts'],
				'water_level_m' => (float) $row['water_level_m'],
			);
		}
		return array(
			'points' => $out,
			'yellow' => (float) $settings['monitor_threshold_yellow_m'],
			'red'    => (float) $settings['monitor_threshold_red_m'],
		);
	}

	public function history_table($limit = 200, $filters = array())
	{
		if ( ! empty($filters['since_ts']))
		{
			$limit = max($limit, 2000);
		}

		$rows = $this->Monitor_model->get_history($limit);
		$settings = $this->settings();
		$offline_after = (int) $settings['monitor_offline_after'];
		$table = array();

		foreach ($rows as $idx => $row)
		{
			$level = (float) $row['water_level_m'];
			$warning = $this->classify($level, $settings);
			$prev = isset($rows[$idx + 1]) ? $rows[$idx + 1] : NULL;
			$trend = $this->row_trend($row, $prev);
			$ett = $this->estimate_time_to_threshold($level, $trend['rate_cm_min'], $warning['level'], $settings['monitor_threshold_yellow_m'], $settings['monitor_threshold_red_m']);
			$ts = (int) $row['ts'];
			$age = time() - $ts;
			$sensor_status = ($idx === 0 && $age <= $offline_after) ? 'online' : (($age <= $offline_after) ? 'online' : 'offline');

			if ( ! empty($filters['warning']) && $filters['warning'] !== $warning['level'])
			{
				continue;
			}
			if ( ! empty($filters['since_ts']) && $ts < (int) $filters['since_ts'])
			{
				continue;
			}
			if ( ! empty($filters['date_from']) && $ts < (int) $filters['date_from'])
			{
				continue;
			}
			if ( ! empty($filters['date_to']) && $ts > (int) $filters['date_to'])
			{
				continue;
			}

			$table[] = array(
				'ts'              => $ts,
				'water_level_m'   => $level,
				'trend_label'     => $trend['trend_label'],
				'rate_cm_min'     => $trend['rate_cm_min'],
				'ett_label'       => $ett['label'],
				'warning_level'   => $warning['level'],
				'warning_label'   => $warning['label'],
				'sensor_status'   => $sensor_status,
				'sensor_label'    => $sensor_status === 'online' ? 'Online' : 'Offline',
				'sync_status'     => isset($row['sync_status']) ? $row['sync_status'] : 'pending',
			);
		}

		return $table;
	}

	public function history_since($days)
	{
		if ($days <= 0)
		{
			return NULL;
		}
		return time() - ((int) $days * 86400);
	}

	public function count_active_alerts()
	{
		if ( ! $this->db->table_exists('flood_alerts'))
		{
			return 0;
		}
		return (int) $this->db->where('status', 'active')->count_all_results('flood_alerts');
	}

	public function count_residents()
	{
		if ( ! $this->db->table_exists('users'))
		{
			return 0;
		}
		return (int) $this->db->where('role', 'user')->count_all_results('users');
	}

	public function get_announcement($id)
	{
		if ( ! $this->db->table_exists('announcements') || (int) $id <= 0)
		{
			return NULL;
		}
		$row = $this->db->where('id', (int) $id)->get('announcements')->row_array();
		return $row ? $row : NULL;
	}

	public function acknowledge_all_alerts($admin_username)
	{
		if ( ! $this->db->table_exists('flood_alerts'))
		{
			return 0;
		}
		$this->db->where('status', 'active')->update('flood_alerts', array(
			'status'          => 'acknowledged',
			'acknowledged_at' => date('Y-m-d H:i:s'),
			'acknowledged_by' => $admin_username,
		));
		return (int) $this->db->affected_rows();
	}

	public function sync_alerts_from_monitor($monitor, $admin_user = NULL)
	{
		if ( ! $this->db->table_exists('flood_alerts'))
		{
			return;
		}

		if ($monitor['sensor_status'] === 'offline')
		{
			$this->insert_alert_if_new('yellow', $monitor['water_level_m'], 'Sensor offline', 'No recent packet from Daguitan Bridge station.', 7200);
		}

		if ($monitor['warning_level'] === 'green')
		{
			return;
		}

		$title = $monitor['warning_level'] === 'red' ? 'Critical flood threshold' : 'Flood advisory threshold';
		$body = 'Automated alert from threshold classification at Daguitan Bridge.';
		$this->insert_alert_if_new($monitor['warning_level'], $monitor['water_level_m'], $title, $body, 3600);
	}

	protected function insert_alert_if_new($level, $water_level_m, $title, $body, $window_seconds)
	{
		$since = time() - (int) $window_seconds;
		$exists = $this->db
			->where('title', $title)
			->where('status', 'active')
			->where('triggered_at >=', date('Y-m-d H:i:s', $since))
			->count_all_results('flood_alerts');
		if ($exists > 0)
		{
			return;
		}
		$this->db->insert('flood_alerts', array(
			'water_level_m' => $water_level_m,
			'warning_level' => in_array($level, array('green', 'yellow', 'red'), TRUE) ? $level : 'yellow',
			'title'         => $title,
			'body'          => $body,
			'triggered_at'  => date('Y-m-d H:i:s'),
			'status'        => 'active',
		));
		$alert_id = (int) $this->db->insert_id();
		if ($alert_id > 0)
		{
			$this->load->model('Notification_model');
			if (in_array($level, array('yellow', 'red'), TRUE))
			{
				$this->Notification_model->on_flood_alert($level, $title, $alert_id);
			}
			$this->Notification_model->notify_admins(
				'admin_flood_alert',
				$title,
				$body,
				site_url('admin/alerts'),
				$alert_id
			);
		}
	}

	public function list_alerts($limit = 100)
	{
		if ( ! $this->db->table_exists('flood_alerts'))
		{
			return array('active' => array(), 'history' => array(), 'all' => array());
		}
		$rows = $this->db->order_by('triggered_at', 'DESC')->limit($limit)->get('flood_alerts')->result_array();
		$active = array();
		$history = array();
		foreach ($rows as $row)
		{
			if ($row['status'] === 'active')
			{
				$active[] = $row;
			}
			else
			{
				$history[] = $row;
			}
		}
		return array('active' => $active, 'history' => $history, 'all' => $rows);
	}

	public function acknowledge_alert($id, $admin_username)
	{
		if ( ! $this->db->table_exists('flood_alerts'))
		{
			return FALSE;
		}
		return $this->db->where('id', (int) $id)->update('flood_alerts', array(
			'status'          => 'acknowledged',
			'acknowledged_at' => date('Y-m-d H:i:s'),
			'acknowledged_by' => $admin_username,
		));
	}

	public function list_announcements()
	{
		if ( ! $this->db->table_exists('announcements'))
		{
			return array();
		}
		return $this->db->order_by('updated_at', 'DESC')->get('announcements')->result_array();
	}

	public function save_announcement($id, $data)
	{
		$row = array(
			'title'        => trim((string) $data['title']),
			'body'         => trim((string) $data['body']),
			'level'        => in_array($data['level'], array('info', 'yellow', 'red'), TRUE) ? $data['level'] : 'info',
			'is_published' => ! empty($data['is_published']) ? 1 : 0,
		);
		if ($row['title'] === '' || $row['body'] === '')
		{
			return array('ok' => FALSE, 'error' => 'Title and message are required.');
		}
		if ((int) $id > 0)
		{
			$this->db->where('id', (int) $id)->update('announcements', $row);
			if ( ! empty($row['is_published']))
			{
				$saved = $this->get_announcement((int) $id);
				if ($saved)
				{
					$this->load->model('Notification_model');
					$this->Notification_model->on_announcement_published($saved);
				}
			}
			return array('ok' => TRUE, 'id' => (int) $id);
		}
		$this->db->insert('announcements', $row);
		$new_id = (int) $this->db->insert_id();
		if ($new_id > 0 && ! empty($row['is_published']))
		{
			$saved = $this->get_announcement($new_id);
			if ($saved)
			{
				$this->load->model('Notification_model');
				$this->Notification_model->on_announcement_published($saved);
			}
		}
		return array('ok' => TRUE, 'id' => $new_id);
	}

	public function delete_announcement($id)
	{
		if ( ! $this->db->table_exists('announcements'))
		{
			return FALSE;
		}
		return $this->db->where('id', (int) $id)->delete('announcements');
	}

	public function toggle_announcement_publish($id, $publish, $send_push = FALSE)
	{
		if ( ! $this->db->table_exists('announcements'))
		{
			return FALSE;
		}
		$update = array('is_published' => $publish ? 1 : 0);
		if ($publish && $send_push)
		{
			$update['push_sent'] = 1;
		}
		$ok = $this->db->where('id', (int) $id)->update('announcements', $update);
		if ($ok && $publish)
		{
			$row = $this->get_announcement((int) $id);
			if ($row)
			{
				$this->load->model('Notification_model');
				$this->Notification_model->on_announcement_published($row);
			}
		}
		return $ok;
	}

	public function list_residents($search = '', $status = '')
	{
		if ( ! $this->db->table_exists('users'))
		{
			return array();
		}
		$this->db->where('role', 'user');
		if ($search !== '')
		{
			$q = $this->db->escape_like_str($search);
			$this->db->group_start()
				->like('name', $q)
				->or_like('username', $q)
				->or_like('phone', $q)
				->group_end();
		}
		$rows = $this->db->order_by('created_at', 'DESC')->get('users')->result_array();
		$login_stats = array();
		if ($this->db->table_exists('user_logins'))
		{
			$stats_rows = $this->db
				->select('user_id, COUNT(*) AS login_count, MAX(logged_in_at) AS last_login_at')
				->group_by('user_id')
				->get('user_logins')
				->result_array();
			foreach ($stats_rows as $stat)
			{
				$login_stats[(int) $stat['user_id']] = $stat;
			}
		}
		$out = array();
		foreach ($rows as $row)
		{
			$row['login_count'] = 0;
			$row['last_login_at_display'] = '—';
			$uid = (int) $row['id'];
			if (isset($login_stats[$uid]))
			{
				$row['login_count'] = (int) $login_stats[$uid]['login_count'];
				if ( ! empty($login_stats[$uid]['last_login_at']))
				{
					$row['last_login_at_display'] = $login_stats[$uid]['last_login_at'];
				}
			}
			elseif ( ! empty($row['last_login_at']))
			{
				$row['last_login_at_display'] = $row['last_login_at'];
			}
			$row['notify_status'] = ! empty($row['phone']) ? 'Ready' : 'No phone';
			if ($status === 'active' && $row['login_count'] < 1)
			{
				continue;
			}
			if ($status === 'no_phone' && ! empty($row['phone']))
			{
				continue;
			}
			$out[] = $row;
		}
		return $out;
	}

	public function list_recent_logins($limit = 12)
	{
		if ( ! $this->db->table_exists('user_logins'))
		{
			return array();
		}
		return $this->db
			->order_by('logged_in_at', 'DESC')
			->limit(max(1, (int) $limit))
			->get('user_logins')
			->result_array();
	}

	public function dashboard_snapshot()
	{
		$ctx = $this->portal_context();
		return array(
			'monitor'       => $ctx['monitor'],
			'active_alerts' => $this->count_active_alerts(),
			'residents'     => $this->count_residents(),
			'pending_sync'  => isset($ctx['sync']['pending_total']) ? (int) $ctx['sync']['pending_total'] : 0,
			'published_announcements' => $this->count_published_announcements(),
		);
	}

	public function count_published_announcements()
	{
		if ( ! $this->db->table_exists('announcements'))
		{
			return 0;
		}
		return (int) $this->db->where('is_published', 1)->count_all_results('announcements');
	}

	public function analytics_summary($since_ts = NULL)
	{
		$limit = ($since_ts !== NULL) ? 2000 : 500;
		$history = $this->Monitor_model->get_history($limit);
		$settings = $this->settings();
		if (empty($history))
		{
			return array(
				'max_level' => 0,
				'min_level' => 0,
				'avg_level' => 0,
				'readings'  => 0,
				'warning_counts' => array('green' => 0, 'yellow' => 0, 'red' => 0),
			);
		}
		$levels = array();
		$counts = array('green' => 0, 'yellow' => 0, 'red' => 0);
		foreach ($history as $row)
		{
			if ($since_ts !== NULL && (int) $row['ts'] < (int) $since_ts)
			{
				continue;
			}
			$l = (float) $row['water_level_m'];
			$levels[] = $l;
			$w = $this->classify($l, $settings);
			$counts[$w['level']]++;
		}
		if (empty($levels))
		{
			return array(
				'max_level' => 0,
				'min_level' => 0,
				'avg_level' => 0,
				'readings'  => 0,
				'warning_counts' => array('green' => 0, 'yellow' => 0, 'red' => 0),
			);
		}
		return array(
			'max_level' => max($levels),
			'min_level' => min($levels),
			'avg_level' => array_sum($levels) / count($levels),
			'readings'  => count($levels),
			'warning_counts' => $counts,
		);
	}

	protected function row_trend($row, $prev)
	{
		if ( ! $prev)
		{
			return array('trend_label' => '—', 'rate_cm_min' => 0.0);
		}
		$minutes = max(0.2, ($row['ts'] - $prev['ts']) / 60);
		$rate = (($row['water_level_m'] - $prev['water_level_m']) * 100) / $minutes;
		$rate = round($rate, 2);
		if ($rate > 0.03)
		{
			return array('trend_label' => 'Rising', 'rate_cm_min' => $rate);
		}
		if ($rate < -0.03)
		{
			return array('trend_label' => 'Falling', 'rate_cm_min' => $rate);
		}
		return array('trend_label' => 'Stable', 'rate_cm_min' => $rate);
	}

	protected function classify($level, $settings)
	{
		$red = (float) $settings['monitor_threshold_red_m'];
		$yellow = (float) $settings['monitor_threshold_yellow_m'];
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

	protected function Monitor_model_cfg($key, $default)
	{
		$this->config->load('monitor', TRUE);
		$val = $this->config->item($key, 'monitor');
		return ($val === NULL) ? $default : $val;
	}

	protected function read_settings_file()
	{
		if ( ! is_file($this->settings_file))
		{
			return array();
		}
		$data = json_decode((string) @file_get_contents($this->settings_file), TRUE);
		return is_array($data) ? $data : array();
	}

	protected function ensure_schema()
	{
		if ( ! $this->db->table_exists('flood_alerts'))
		{
			$this->db->query("CREATE TABLE IF NOT EXISTS `flood_alerts` (
				`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				`water_level_m` DECIMAL(6,3) NOT NULL,
				`warning_level` ENUM('green','yellow','red') NOT NULL,
				`title` VARCHAR(120) NOT NULL,
				`body` TEXT NULL,
				`triggered_at` DATETIME NOT NULL,
				`acknowledged_at` DATETIME NULL,
				`acknowledged_by` VARCHAR(64) NULL,
				`status` ENUM('active','acknowledged') NOT NULL DEFAULT 'active',
				PRIMARY KEY (`id`),
				KEY `idx_flood_alerts_triggered` (`triggered_at`),
				KEY `idx_flood_alerts_status` (`status`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}
		if ( ! $this->db->table_exists('announcements'))
		{
			$this->db->query("CREATE TABLE IF NOT EXISTS `announcements` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`title` VARCHAR(160) NOT NULL,
				`body` TEXT NOT NULL,
				`level` ENUM('info','yellow','red') NOT NULL DEFAULT 'info',
				`is_published` TINYINT(1) NOT NULL DEFAULT 0,
				`push_sent` TINYINT(1) NOT NULL DEFAULT 0,
				`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}
	}
}
