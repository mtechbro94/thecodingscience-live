<?php
require_once __DIR__ . '/includes/db.php';

try {
    echo "Starting migration...\n";
    $pdo->exec("ALTER TABLE users ADD COLUMN otp_code VARCHAR(6) DEFAULT NULL, ADD COLUMN otp_expiry DATETIME DEFAULT NULL");
    echo "Migration successful: Added otp_code and otp_expiry to users table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Migration skipped: Columns already exist.\n";
    } else {
        echo "Migration failed: " . $e->getMessage() . "\n";
    }
}
?>