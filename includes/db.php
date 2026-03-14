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

    // Global site settings (loaded once per request, needed for header/footer)
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