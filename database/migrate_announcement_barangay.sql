-- Run once on installations that already have the announcements table.
USE `MDRRMO_DULAG`;

ALTER TABLE `announcements`
	ADD COLUMN `barangay` VARCHAR(80) NULL DEFAULT NULL AFTER `level`;
