<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Audit_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->ensure_schema();
	}

	public function ensure_schema()
	{
		if ($this->db->table_exists('audit_log'))
		{
			return;
		}
		$this->db->query("CREATE TABLE IF NOT EXISTS `audit_log` (
			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`user_id` INT UNSIGNED NULL,
			`username` VARCHAR(64) NULL,
			`action` VARCHAR(48) NOT NULL,
			`entity` VARCHAR(48) NULL,
			`entity_id` BIGINT UNSIGNED NULL,
			`summary` VARCHAR(255) NOT NULL,
			`ip_address` VARCHAR(45) NULL,
			`created_at` DATETIME NOT NULL,
			PRIMARY KEY (`id`),
			KEY `idx_audit_created` (`created_at`),
			KEY `idx_audit_action` (`action`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}

	public function log($action, $summary, $entity = NULL, $entity_id = NULL)
	{
		if ( ! $this->db->table_exists('audit_log'))
		{
			return FALSE;
		}
		$ci = get_instance();
		return $this->db->insert('audit_log', array(
			'user_id'    => (int) $ci->session->userdata('auth_id') ?: NULL,
			'username'   => (string) $ci->session->userdata('auth_user'),
			'action'     => substr((string) $action, 0, 48),
			'entity'     => $entity ? substr((string) $entity, 0, 48) : NULL,
			'entity_id'  => $entity_id !== NULL ? (int) $entity_id : NULL,
			'summary'    => substr((string) $summary, 0, 255),
			'ip_address' => $ci->input->ip_address() !== FALSE ? $ci->input->ip_address() : NULL,
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

	public function list_recent($limit = 40)
	{
		if ( ! $this->db->table_exists('audit_log'))
		{
			return array();
		}
		return $this->db
			->order_by('created_at', 'DESC')
			->limit(max(1, (int) $limit))
			->get('audit_log')
			->result_array();
	}
}
