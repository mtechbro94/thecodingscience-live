<?php
// includes/db.php - Database Connection

require_once __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Check if site_settings table exists, create if not
    $tableExists = $pdo->query("SHOW TABLES LIKE 'site_settings'")->rowCount() > 0;

    if (!$tableExists) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (
            `key` varchar(50) NOT NULL,
            `value` text DEFAULT NULL,
            PRIMARY KEY (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Insert default settings
        $pdo->exec("INSERT IGNORE INTO `site_settings` (`key`, `value`) VALUES
            ('site_name', '" . addslashes(SITE_NAME) . "'),
            ('site_url', '" . addslashes(SITE_URL) . "'),
            ('contact_email', '" . addslashes(CONTACT_EMAIL) . "'),
            ('contact_phone', '" . addslashes(CONTACT_PHONE) . "'),
            ('contact_address', 'Your Address Here'),
            ('facebook_url', ''),
            ('instagram_url', ''),
            ('youtube_url', ''),
            ('linkedin_url', ''),
            ('hero_title', 'Master The Coding Science'),
            ('hero_subtitle', 'Join our community of developers and start your journey today.'),
            ('enrollment_instructions', 'Please pay the course fee to the UPI ID: **thecodingscience@upi**. After payment, enter your Transaction ID (UTR) below for verification.')");
    }

    // Global site settings
    $site_settings = [];
    $stmt = $pdo->query("SELECT `key`, `value` FROM site_settings");
    while ($row = $stmt->fetch()) {
        $site_settings[$row['key']] = $row['value'];
    }

} catch (\PDOException $e) {

    // In production, log this instead of showing it
    if (ENVIRONMENT === 'local' || ENVIRONMENT === 'development') {
        die("Database Connection Failed: " . $e->getMessage());
    } else {
        error_log("Database Connection Failed: " . $e->getMessage());
        die("Service temporarily unavailable. Please try again later.");
    }
}
?>