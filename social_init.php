<?php
// social_init.php
require_once 'config.php';
require_once 'includes/db.php';

try {
    echo "<h1>Social Auth Initialization</h1>";

    // Add columns for social auth
    $sql = "ALTER TABLE users 
            ADD COLUMN oauth_provider VARCHAR(50) DEFAULT NULL AFTER profile_image,
            ADD COLUMN oauth_id VARCHAR(255) DEFAULT NULL AFTER oauth_provider,
            MODIFY COLUMN password_hash VARCHAR(255) DEFAULT NULL";

    $pdo->exec($sql);
    echo "<p style='color: green;'>Successfully updated users table.</p>";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "<p style='color: orange;'>Columns already exist. Nothing to do.</p>";
    } else {
        echo "<p style='color: red;'>Error updating table: " . $e->getMessage() . "</p>";
    }
}

echo "<p><a href='/admin/dashboard'>Go to Dashboard</a></p>";
?>