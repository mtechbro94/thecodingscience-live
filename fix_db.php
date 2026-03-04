<?php
// fix_db.php
require_once 'config.php';
require_once 'includes/db.php';

try {
    echo "Checking database columns...<br>";

    // Check users table for otp_expiry
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('otp_expiry', $columns)) {
        echo "Adding otp_expiry to users table...<br>";
        $pdo->exec("ALTER TABLE users ADD COLUMN otp_expiry DATETIME DEFAULT NULL AFTER otpcode");
    }

    // Check courses table for is_featured
    $stmt = $pdo->query("DESCRIBE courses");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('is_featured', $columns)) {
        echo "Adding is_featured to courses table...<br>";
        $pdo->exec("ALTER TABLE courses ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER trainer_id");
    }

    echo "Successfully updated database columns!<br>";
    echo "<a href='/'>Go to Home</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>