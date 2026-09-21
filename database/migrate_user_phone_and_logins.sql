-- Run this on your EXISTING MDRRMO_DULAG database (phpMyAdmin → SQL tab).
-- If a statement errors because the column/index/table already exists, skip that line and continue.

USE `MDRRMO_DULAG`;

-- 1) Mobile number on user accounts
ALTER TABLE `users`
	ADD COLUMN `phone` VARCHAR(20) NULL AFTER `name`;

-- 2) Last sign-in timestamp on user accounts
ALTER TABLE `users`
	ADD COLUMN `last_login_at` DATETIME NULL AFTER `password_hash`;

-- 3) One mobile number per account (NULL allowed for staff with no phone)
ALTER TABLE `users`
	ADD UNIQUE KEY `uk_users_phone` (`phone`);

-- 4) History of every successful login (name, phone, role, IP, etc.)
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

-- Optional: add default accounts only if they are not in the table yet
INSERT INTO `users` (`record_uid`, `username`, `name`, `role`, `password_hash`)
SELECT UUID(), 'MDRRMO_DULAG', 'MDRRMO Dulag', 'admin', '$2y$10$JyOP4GVWDSnZiNDMr1lrA.dZQf6HmXrnR1x5c/gXiu7rAv.2nkeNG'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE LOWER(`username`) = LOWER('MDRRMO_DULAG'));

INSERT INTO `users` (`record_uid`, `username`, `name`, `role`, `password_hash`)
SELECT UUID(), 'John Rouque B. Abina', 'John Rouque B. Abina', 'user', '$2y$10$93VD7fPzU2ldvL6lD8SU3.Vs8zhLoHOQIA8MOsrbIUL4JXWzOINma'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE LOWER(`username`) = LOWER('John Rouque B. Abina'));
