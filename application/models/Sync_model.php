<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Sync_model extends CI_Model {

	protected $cloud = NULL;
	protected $probe_file;

	public function __construct()
	{
		parent::__construct();
		$this->config->load('sync', TRUE);
		$this->probe_file = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'sync_probe.json';
		$this->ensure_local_schema();
	}

	public function new_uid()
	{
		$data = random_bytes(16);
		$data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
		$data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
		$hex = bin2hex($data);
		return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
			. '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
	}

	public function is_online($force = FALSE)
	{
		if ( ! $this->cfg('sync_enabled', TRUE) || $this->cfg('sync_force_offline', FALSE))
		{
			return FALSE;
		}

		$ttl = (int) $this->cfg('sync_probe_ttl', 20);
		if ( ! $force && is_file($this->probe_file))
		{
			$cached = json_decode((string) @file_get_contents($this->probe_file), TRUE);
			if (is_array($cached) && isset($cached['at'], $cached['online']) && (time() - (int) $cached['at']) < $ttl)
			{
				return (bool) $cached['online'];
			}
		}

		$host = (string) $this->cfg('sync_probe_host', '8.8.8.8');
		$port = (int) $this->cfg('sync_probe_port', 53);
		$timeout = (float) $this->cfg('sync_probe_timeout', 2);
		$errno = 0;
		$errstr = '';
		$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
		$online = is_resource($fp);
		if ($online)
		{
			fclose($fp);
		}

		@file_put_contents($this->probe_file, json_encode(array(
			'at'     => time(),
			'online' => $online,
		)));

		return $online;
	}

	public function status()
	{
		$online = $this->is_online();
		$pending_readings = 0;
		$pending_users = 0;
		$synced_readings = 0;

		if ($this->db->table_exists('water_readings') && $this->has_column('water_readings', 'sync_status'))
		{
			$pending_readings = (int) $this->db->where_in('sync_status', array('pending', 'failed'))->count_all_results('water_readings');
			$synced_readings = (int) $this->db->where('sync_status', 'synced')->count_all_results('water_readings');
		}
		if ($this->db->table_exists('users') && $this->has_column('users', 'sync_status'))
		{
			$pending_users = (int) $this->db->where_in('sync_status', array('pending', 'failed'))->count_all_results('users');
		}

		$last = NULL;
		if ($this->db->table_exists('water_readings') && $this->has_column('water_readings', 'synced_at'))
		{
			$row = $this->db->select_max('synced_at')->get('water_readings')->row_array();
			$last = (isset($row['synced_at']) && $row['synced_at']) ? $row['synced_at'] : NULL;
		}

		return array(
			'enabled'          => (bool) $this->cfg('sync_enabled', TRUE),
			'internet'         => $online,
			'internet_label'   => $online ? 'Online' : 'Offline',
			'cloud_database'   => 'MDRRMO_DULAG_CLOUD',
			'pending_readings' => $pending_readings,
			'pending_users'    => $pending_users,
			'synced_readings'  => $synced_readings,
			'pending_total'    => $pending_readings + $pending_users,
			'last_synced_at'   => $last,
		);
	}

	public function try_copy_now($force_probe = FALSE, $limit = NULL)
	{
		if ($limit === NULL)
		{
			$limit = (int) $this->cfg('sync_batch_size', 40);
		}

		$result = array(
			'ok'       => TRUE,
			'online'   => FALSE,
			'pushed'   => 0,
			'failed'   => 0,
			'skipped'  => 0,
			'message'  => '',
		);

		try
		{
			if ( ! $this->is_online($force_probe))
			{
				$result['message'] = 'No internet. Data stays in local SQL until the connection returns.';
				return $result;
			}

			$result['online'] = TRUE;
			$cloud = $this->cloud_db();
			if ( ! $cloud)
			{
				$result['ok'] = FALSE;
				$result['message'] = 'Internet is up, but the cloud database could not be reached.';
				return $result;
			}
		}
		catch (Throwable $e)
		{
			$result['ok'] = FALSE;
			$result['message'] = 'Local SQL is OK. Cloud copy could not start.';
			return $result;
		}

		try
		{
			foreach ($this->pending_rows('users', $limit) as $user)
			{
				if ($this->push_user($cloud, $user))
				{
					$result['pushed']++;
				}
				else
				{
					$result['failed']++;
				}
			}

			foreach ($this->pending_rows('water_readings', $limit) as $reading)
			{
				if ($this->push_reading($cloud, $reading))
				{
					$result['pushed']++;
				}
				else
				{
					$result['failed']++;
				}
			}
		}
		catch (Throwable $e)
		{
			$result['ok'] = FALSE;
			$result['message'] = 'Local SQL is OK. Cloud copy stopped after a connection error.';
			return $result;
		}

		if ($result['pushed'] === 0 && $result['failed'] === 0)
		{
			$result['message'] = 'Internet is up. Everything already on the cloud copy is in sync.';
		}
		elseif ($result['failed'] > 0)
		{
			$result['ok'] = FALSE;
			$result['message'] = 'Copied ' . $result['pushed'] . ' row(s); ' . $result['failed'] . ' failed.';
		}
		else
		{
			$result['message'] = 'Copied ' . $result['pushed'] . ' pending row(s) to the cloud database.';
		}

		return $result;
	}

	public function flush($limit = NULL)
	{
		return $this->try_copy_now(TRUE, $limit);
	}

	protected function pending_rows($table, $limit)
	{
		if ( ! $this->db->table_exists($table) || ! $this->has_column($table, 'sync_status'))
		{
			return array();
		}

		return $this->db
			->where_in('sync_status', array('pending', 'failed'))
			->order_by('id', 'ASC')
			->limit((int) $limit)
			->get($table)
			->result_array();
	}

	protected function push_reading($cloud, $row)
	{
		$uid = ! empty($row['record_uid']) ? $row['record_uid'] : $this->new_uid();
		$sql = 'INSERT INTO `water_readings`
			(`record_uid`, `water_level_m`, `distance_cm`, `sensor_height_cm`, `received_at`)
			VALUES (?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
				`water_level_m` = VALUES(`water_level_m`),
				`distance_cm` = VALUES(`distance_cm`),
				`sensor_height_cm` = VALUES(`sensor_height_cm`),
				`received_at` = VALUES(`received_at`)';

		$ok = $cloud->query($sql, array(
			$uid,
			$row['water_level_m'],
			$row['distance_cm'],
			$row['sensor_height_cm'],
			$row['received_at'],
		));

		return $this->mark_local('water_readings', (int) $row['id'], $uid, $ok, $cloud);
	}

	protected function push_user($cloud, $row)
	{
		$uid = ! empty($row['record_uid']) ? $row['record_uid'] : $this->new_uid();
		$sql = 'INSERT INTO `users`
			(`record_uid`, `username`, `name`, `role`, `password_hash`)
			VALUES (?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
				`name` = VALUES(`name`),
				`role` = VALUES(`role`),
				`password_hash` = VALUES(`password_hash`),
				`record_uid` = VALUES(`record_uid`)';

		$ok = $cloud->query($sql, array(
			$uid,
			$row['username'],
			$row['name'],
			$row['role'],
			$row['password_hash'],
		));

		return $this->mark_local('users', (int) $row['id'], $uid, $ok, $cloud);
	}

	protected function mark_local($table, $id, $uid, $ok, $cloud)
	{
		$error = '';
		if ( ! $ok)
		{
			$err = $cloud->error();
			$error = isset($err['message']) ? substr($err['message'], 0, 250) : 'cloud_write_failed';
		}

		$this->db->where('id', $id)->update($table, array(
			'record_uid'  => $uid,
			'sync_status' => $ok ? 'synced' : 'failed',
			'synced_at'   => $ok ? date('Y-m-d H:i:s') : NULL,
			'sync_error'  => $ok ? NULL : $error,
		));

		return (bool) $ok;
	}

	protected function cloud_db()
	{
		if ($this->cloud && $this->cloud->conn_id)
		{
			return $this->cloud;
		}

		$this->ensure_cloud_schema_exists();

		$previous = error_reporting();
		error_reporting($previous & ~E_WARNING);
		$cloud = @$this->load->database('cloud', TRUE);
		error_reporting($previous);

		if ( ! $cloud || empty($cloud->conn_id))
		{
			return NULL;
		}

		$cloud->query("CREATE TABLE IF NOT EXISTS `users` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`record_uid` CHAR(36) NOT NULL,
			`username` VARCHAR(64) NOT NULL,
			`name` VARCHAR(120) NOT NULL,
			`role` ENUM('admin','user') NOT NULL,
			`password_hash` VARCHAR(255) NOT NULL,
			`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `uk_users_username` (`username`),
			UNIQUE KEY `uk_users_uid` (`record_uid`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		$cloud->query("CREATE TABLE IF NOT EXISTS `water_readings` (
			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`record_uid` CHAR(36) NOT NULL,
			`water_level_m` DECIMAL(6,3) NOT NULL,
			`distance_cm` DECIMAL(8,2) DEFAULT NULL,
			`sensor_height_cm` DECIMAL(8,2) DEFAULT NULL,
			`received_at` INT UNSIGNED NOT NULL,
			`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `uk_water_readings_uid` (`record_uid`),
			KEY `idx_water_readings_received_at` (`received_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		$this->cloud = $cloud;
		return $cloud;
	}

	protected function ensure_cloud_schema_exists()
	{
		include APPPATH . 'config/database.php';
		$cloud_cfg = (isset($db) && isset($db['cloud'])) ? $db['cloud'] : array();
		$host = isset($cloud_cfg['hostname']) ? strtolower((string) $cloud_cfg['hostname']) : 'localhost';
		$schema = isset($cloud_cfg['database']) ? $cloud_cfg['database'] : 'MDRRMO_DULAG_CLOUD';
		$local_hosts = array('localhost', '127.0.0.1', '::1');
		if (in_array($host, $local_hosts, TRUE))
		{
			$this->db->query('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', $schema) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
		}
	}

	protected function ensure_local_schema()
	{
		if ($this->db->table_exists('water_readings'))
		{
			$this->add_column('water_readings', 'record_uid', "CHAR(36) NULL AFTER `id`");
			$this->add_column('water_readings', 'sync_status', "ENUM('pending','synced','failed') NOT NULL DEFAULT 'pending' AFTER `received_at`");
			$this->add_column('water_readings', 'synced_at', "DATETIME NULL AFTER `sync_status`");
			$this->add_column('water_readings', 'sync_error', "VARCHAR(255) NULL AFTER `synced_at`");
			$this->backfill_uids('water_readings');
			$this->add_unique('water_readings', 'uk_water_readings_uid', 'record_uid');
		}

		if ($this->db->table_exists('users'))
		{
			$this->add_column('users', 'record_uid', "CHAR(36) NULL AFTER `id`");
			$this->add_column('users', 'sync_status', "ENUM('pending','synced','failed') NOT NULL DEFAULT 'pending' AFTER `password_hash`");
			$this->add_column('users', 'synced_at', "DATETIME NULL AFTER `sync_status`");
			$this->add_column('users', 'sync_error', "VARCHAR(255) NULL AFTER `synced_at`");
			$this->backfill_uids('users');
			$this->add_unique('users', 'uk_users_uid', 'record_uid');
		}
	}

	protected function has_column($table, $column)
	{
		$query = $this->db->query('SHOW COLUMNS FROM `' . $table . '` LIKE ' . $this->db->escape($column));
		return $query && $query->num_rows() > 0;
	}

	protected function add_column($table, $column, $definition)
	{
		if ($this->has_column($table, $column))
		{
			return;
		}
		$this->db->query('ALTER TABLE `' . $table . '` ADD COLUMN `' . $column . '` ' . $definition);
	}

	protected function add_unique($table, $index, $column)
	{
		$found = $this->db->query('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ' . $this->db->escape($index));
		if ($found && $found->num_rows() > 0)
		{
			return;
		}
		$this->db->query('ALTER TABLE `' . $table . '` ADD UNIQUE KEY `' . $index . '` (`' . $column . '`)');
	}

	protected function backfill_uids($table)
	{
		$rows = $this->db->group_start()
			->where('record_uid', NULL)
			->or_where('record_uid', '')
			->group_end()
			->get($table)
			->result_array();

		foreach ($rows as $row)
		{
			$this->db->where('id', $row['id'])->update($table, array('record_uid' => $this->new_uid()));
		}
	}

	protected function cfg($key, $default = NULL)
	{
		$val = $this->config->item($key, 'sync');
		return ($val === NULL) ? $default : $val;
	}
}
