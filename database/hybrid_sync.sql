-- Hybrid sync: local MDRRMO_DULAG is always the source of truth.
-- Cloud copy MDRRMO_DULAG_CLOUD receives rows when the internet is up.

CREATE DATABASE IF NOT EXISTS `MDRRMO_DULAG_CLOUD`
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_unicode_ci;

USE `MDRRMO_DULAG_CLOUD`;

CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `water_readings` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
