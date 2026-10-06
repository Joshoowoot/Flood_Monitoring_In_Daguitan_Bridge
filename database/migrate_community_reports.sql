USE `MDRRMO_DULAG`;

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
