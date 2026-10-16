USE `MDRRMO_DULAG`;

CREATE TABLE IF NOT EXISTS `announcement_images` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`announcement_id` INT UNSIGNED NOT NULL,
	`image_name` VARCHAR(64) NOT NULL,
	`position` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_announcement_images_name` (`image_name`),
	KEY `idx_announcement_images_parent` (`announcement_id`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
