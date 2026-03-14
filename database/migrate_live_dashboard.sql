-- Migration: Add batch fields to courses and create recordings/resources tables

-- 1. Add fields for live batches to courses table
ALTER TABLE `courses` ADD COLUMN `batch_timing` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `courses` ADD COLUMN `live_link` VARCHAR(500) DEFAULT NULL;

-- 2. Create table for session recordings
CREATE TABLE IF NOT EXISTS `course_recordings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `recording_url` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `course_id` (`course_id`),
    CONSTRAINT `fk_recording_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create table for course resources
CREATE TABLE IF NOT EXISTS `course_resources` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `course_id` (`course_id`),
    CONSTRAINT `fk_resource_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
