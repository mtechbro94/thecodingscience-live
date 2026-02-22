<?php
// migrate_profile_image.php
require_once 'config.php';
require_once 'includes/db.php';

try {
    $sql = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER bio";
    $pdo->exec($sql);
    echo "Successfully added profile_image column to users table.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Column profile_image already exists.\n";
    } else {
        echo "Error adding column: " . $e->getMessage() . "\n";
    }
}
?>