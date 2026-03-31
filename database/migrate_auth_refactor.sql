-- Migration: Authentication System Refactor
-- Date: 2026-03-31
-- Purpose: Add trainer username, OTP verification, and Gmail OAuth ID fields

-- Add new columns to users table
ALTER TABLE `users` ADD COLUMN `username` VARCHAR(100) UNIQUE DEFAULT NULL AFTER `email`;
ALTER TABLE `users` ADD COLUMN `gmail_id` VARCHAR(255) UNIQUE DEFAULT NULL AFTER `username`;
ALTER TABLE `users` ADD COLUMN `otp_code` VARCHAR(6) DEFAULT NULL AFTER `gmail_id`;
ALTER TABLE `users` ADD COLUMN `otp_expires_at` DATETIME DEFAULT NULL AFTER `otp_code`;
ALTER TABLE `users` ADD COLUMN `otp_verified` TINYINT(1) DEFAULT 0 AFTER `otp_expires_at`;
ALTER TABLE `users` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Create indices for better query performance
CREATE INDEX idx_username ON `users`(`username`);
CREATE INDEX idx_gmail_id ON `users`(`gmail_id`);
CREATE INDEX idx_otp_code ON `users`(`otp_code`);

-- Create OTP tokens table for tracking (optional backup)
CREATE TABLE IF NOT EXISTS `otp_tokens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `email` VARCHAR(120) NOT NULL,
  `otp_code` VARCHAR(6) NOT NULL,
  `purpose` ENUM('login', 'registration', 'password_reset') DEFAULT 'login',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_purpose` (`email`, `purpose`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `otp_code` (`otp_code`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
