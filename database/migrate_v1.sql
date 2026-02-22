CREATE TABLE IF NOT EXISTS `site_settings` (
  `key` varchar(50) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `site_settings` (`key`, `value`) VALUES
('site_name', 'The Coding Science'),
('site_url', 'http://thecodingscience.com'),
('contact_email', 'academy@thecodingscience.com'),
('contact_phone', '+91 7006196821'),
('contact_address', 'Ananwan, Kupwara Jammu and Kashmir'),
('facebook_url', ''),
('instagram_url', ''),
('youtube_url', ''),
('linkedin_url', ''),
('hero_title', 'Master The Coding Science'),
('hero_subtitle', 'Join our community of developers and start your journey today.'),
('enrollment_instructions', 'Please pay the course fee to the UPI ID: **thecodingscience@upi**. After payment, enter your Transaction ID (UTR) below for verification.');
