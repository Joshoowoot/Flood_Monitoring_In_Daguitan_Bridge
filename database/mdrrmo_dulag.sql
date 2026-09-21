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
SELECT UUID(), 'MDRRMO_DULAG', 'MDRRMO Dulag', 'admin', '$2y$10$JyOP4GVWDSnZiNDMr1lrA.dZQf6HmXrnR1x5c/gXiu7rAv.2nkeNG'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'MDRRMO_DULAG');

INSERT INTO `users` (`record_uid`, `username`, `name`, `role`, `password_hash`)
SELECT UUID(), 'John Rouque B. Abina', 'John Rouque B. Abina', 'user', '$2y$10$93VD7fPzU2ldvL6lD8SU3.Vs8zhLoHOQIA8MOsrbIUL4JXWzOINma'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'John Rouque B. Abina');
