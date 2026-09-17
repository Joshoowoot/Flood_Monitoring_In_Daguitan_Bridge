CREATE DATABASE IF NOT EXISTS `MDRRMO_DULAG`
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_unicode_ci;

USE `MDRRMO_DULAG`;

CREATE TABLE IF NOT EXISTS `users` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_uid` CHAR(36) NOT NULL,
	`username` VARCHAR(64) NOT NULL,
	`name` VARCHAR(120) NOT NULL,
	`role` ENUM('admin','user') NOT NULL,
	`password_hash` VARCHAR(255) NOT NULL,
	`sync_status` ENUM('pending','synced','failed') NOT NULL DEFAULT 'pending',
	`synced_at` DATETIME NULL,
	`sync_error` VARCHAR(255) NULL,
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
	`sync_status` ENUM('pending','synced','failed') NOT NULL DEFAULT 'pending',
	`synced_at` DATETIME NULL,
	`sync_error` VARCHAR(255) NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uk_water_readings_uid` (`record_uid`),
	KEY `idx_water_readings_received_at` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`record_uid`, `username`, `name`, `role`, `password_hash`)
SELECT UUID(), 'admin', 'MDRRMO Administrator', 'admin', '$2y$10$/BvgafsVGC7yCpPhDVl42ONarf1OMRMOmGMw6GDfsgf7dGT/gbUCS'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'admin');

INSERT INTO `users` (`record_uid`, `username`, `name`, `role`, `password_hash`)
SELECT UUID(), 'resident', 'Community Resident', 'user', '$2y$10$CAvebBXG0SyM/nd9/5ZZBeAnY6utv3U.IO.8ei27HL3v1bPcV18PS'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'resident');
