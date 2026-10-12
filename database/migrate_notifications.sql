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

CREATE TABLE IF NOT EXISTS `push_subscriptions` (
	`endpoint_hash` CHAR(64) NOT NULL,
	`user_id` INT UNSIGNED NOT NULL,
	`audience` ENUM('admin','user') NOT NULL,
	`endpoint` TEXT NOT NULL,
	`public_key` VARCHAR(255) NOT NULL,
	`auth_token` VARCHAR(255) NOT NULL,
	`content_encoding` VARCHAR(32) NOT NULL DEFAULT 'aes128gcm',
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`endpoint_hash`),
	KEY `idx_push_subscriptions_user` (`user_id`, `audience`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
