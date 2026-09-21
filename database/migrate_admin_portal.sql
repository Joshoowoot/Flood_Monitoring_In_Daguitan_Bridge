-- Optional: tables are also created automatically on first admin visit.
USE `MDRRMO_DULAG`;

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
	`is_published` TINYINT(1) NOT NULL DEFAULT 0,
	`push_sent` TINYINT(1) NOT NULL DEFAULT 0,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
