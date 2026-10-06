USE `MDRRMO_DULAG`;

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
