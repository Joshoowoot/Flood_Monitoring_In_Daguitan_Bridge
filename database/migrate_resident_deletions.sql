USE `MDRRMO_DULAG`;

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
