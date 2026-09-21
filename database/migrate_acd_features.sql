-- SMS opt-in, telemetry columns, audit log, SMS log (run on MDRRMO_DULAG).

USE `MDRRMO_DULAG`;

ALTER TABLE `users`
	ADD COLUMN `notify_sms` TINYINT(1) NOT NULL DEFAULT 1 AFTER `phone`;

ALTER TABLE `water_readings`
	ADD COLUMN `battery_pct` TINYINT UNSIGNED NULL AFTER `sensor_height_cm`,
	ADD COLUMN `rssi_dbm` SMALLINT NULL AFTER `battery_pct`,
	ADD COLUMN `solar_charging` TINYINT(1) NULL AFTER `rssi_dbm`,
	ADD COLUMN `firmware_version` VARCHAR(32) NULL AFTER `solar_charging`;

CREATE TABLE IF NOT EXISTS `audit_log` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sms_log` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INT UNSIGNED NULL,
	`phone` VARCHAR(20) NOT NULL,
	`message` VARCHAR(320) NOT NULL,
	`type` VARCHAR(32) NOT NULL,
	`ref_id` BIGINT UNSIGNED NULL,
	`status` ENUM('sent','failed','skipped') NOT NULL DEFAULT 'sent',
	`provider_ref` VARCHAR(64) NULL,
	`error_message` VARCHAR(255) NULL,
	`created_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_sms_type_ref` (`type`, `ref_id`),
	KEY `idx_sms_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
