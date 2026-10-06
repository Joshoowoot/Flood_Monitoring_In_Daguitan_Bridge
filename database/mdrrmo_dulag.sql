CREATE DATABASE IF NOT EXISTS `MDRRMO_DULAG`
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_unicode_ci;

USE `MDRRMO_DULAG`;

CREATE TABLE IF NOT EXISTS `users` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_uid` CHAR(36) NOT NULL,
	`username` VARCHAR(64) NOT NULL,
	`name` VARCHAR(120) NOT NULL,
	`phone` VARCHAR(20) NULL,
	`notify_sms` TINYINT(1) NOT NULL DEFAULT 1,
	`barangay` VARCHAR(80) NULL,
	`role` ENUM('admin','user') NOT NULL,
	`password_hash` VARCHAR(255) NOT NULL,
	`last_login_at` DATETIME NULL,
	`sync_status` ENUM('pending','synced','failed') NOT NULL DEFAULT 'pending',
	`synced_at` DATETIME NULL,
	`sync_error` VARCHAR(255) NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uk_users_username` (`username`),
	UNIQUE KEY `uk_users_uid` (`record_uid`),
	UNIQUE KEY `uk_users_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_logins` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INT UNSIGNED NOT NULL,
	`record_uid` CHAR(36) NULL,
	`username` VARCHAR(64) NOT NULL,
	`name` VARCHAR(120) NOT NULL,
	`phone` VARCHAR(20) NULL,
	`role` ENUM('admin','user') NOT NULL,
	`ip_address` VARCHAR(45) NULL,
	`user_agent` VARCHAR(255) NULL,
	`logged_in_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_user_logins_user_id` (`user_id`),
	KEY `idx_user_logins_logged_in_at` (`logged_in_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `water_readings` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_uid` CHAR(36) NOT NULL,
	`water_level_m` DECIMAL(6,3) NOT NULL,
	`distance_cm` DECIMAL(8,2) DEFAULT NULL,
	`sensor_height_cm` DECIMAL(8,2) DEFAULT NULL,
	`battery_pct` TINYINT UNSIGNED NULL,
	`rssi_dbm` SMALLINT NULL,
	`solar_charging` TINYINT(1) NULL,
	`firmware_version` VARCHAR(32) NULL,
	`received_at` INT UNSIGNED NOT NULL,
	`sync_status` ENUM('pending','synced','failed') NOT NULL DEFAULT 'pending',
	`synced_at` DATETIME NULL,
	`sync_error` VARCHAR(255) NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uk_water_readings_uid` (`record_uid`),
	KEY `idx_water_readings_received_at` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `flood_alerts` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `announcements` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(160) NOT NULL,
	`body` TEXT NOT NULL,
	`level` ENUM('info','yellow','red') NOT NULL DEFAULT 'info',
	`barangay` VARCHAR(80) NULL DEFAULT NULL,
	`is_published` TINYINT(1) NOT NULL DEFAULT 0,
	`push_sent` TINYINT(1) NOT NULL DEFAULT 0,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `community_reports` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INT UNSIGNED NOT NULL,
	`barangay` VARCHAR(80) NOT NULL,
	`report_type` ENUM('flooding','blocked_drainage','rising_water','other_hazard') NOT NULL,
	`landmark` VARCHAR(160) NOT NULL,
	`description` TEXT NOT NULL,
	`photo_name` VARCHAR(64) NULL,
	`status` ENUM('received','reviewing','action_taken','resolved') NOT NULL DEFAULT 'received',
	`admin_note` TEXT NULL,
	`reviewed_by` VARCHAR(80) NULL,
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_community_reports_user_created` (`user_id`, `created_at`),
	KEY `idx_community_reports_status_created` (`status`, `created_at`),
	KEY `idx_community_reports_barangay_created` (`barangay`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `resident_deletions` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_uid` CHAR(36) NOT NULL,
	`sync_status` ENUM('pending','synced','failed') NOT NULL DEFAULT 'pending',
	`sync_error` VARCHAR(255) NULL,
	`created_at` DATETIME NOT NULL,
	`synced_at` DATETIME NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uk_resident_deletions_uid` (`record_uid`),
	KEY `idx_resident_deletions_status` (`sync_status`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`audience` ENUM('admin','user') NOT NULL,
	`type` VARCHAR(32) NOT NULL,
	`title` VARCHAR(160) NOT NULL,
	`body` TEXT NOT NULL,
	`link` VARCHAR(255) NULL,
	`ref_id` BIGINT UNSIGNED NULL,
	`recipient_user_id` INT UNSIGNED NULL,
	`created_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_notifications_audience_created` (`audience`, `created_at`),
	KEY `idx_notifications_recipient` (`recipient_user_id`),
	KEY `idx_notifications_type_ref` (`type`, `ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notification_reads` (
	`notification_id` BIGINT UNSIGNED NOT NULL,
	`user_id` INT UNSIGNED NOT NULL,
	`read_at` DATETIME NOT NULL,
	PRIMARY KEY (`notification_id`, `user_id`),
	KEY `idx_notification_reads_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

INSERT INTO `users` (`record_uid`, `username`, `name`, `role`, `password_hash`)
SELECT UUID(), 'MDRRMO_DULAG', 'MDRRMO Dulag', 'admin', '$2y$10$JyOP4GVWDSnZiNDMr1lrA.dZQf6HmXrnR1x5c/gXiu7rAv.2nkeNG'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'MDRRMO_DULAG');

INSERT INTO `users` (`record_uid`, `username`, `name`, `role`, `password_hash`)
SELECT UUID(), 'John Rouque B. Abina', 'John Rouque B. Abina', 'user', '$2y$10$93VD7fPzU2ldvL6lD8SU3.Vs8zhLoHOQIA8MOsrbIUL4JXWzOINma'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'John Rouque B. Abina');
