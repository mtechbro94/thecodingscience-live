<?php
require_once 'config.php';
require_once 'includes/db.php';

// Check if already done or if we need to drop
try {
    // 1. Drop existing unique index on email
    try {
        $pdo->exec("ALTER TABLE `users` DROP INDEX `email` ");
        echo "Dropped single email unique index.<br>";
    } catch (Exception $e) {
        echo "Note: Index 'email' might not exist or name is different. Skipping drop.<br>";
    }

    // 2. Add composite unique index on (email, role)
    try {
        $pdo->exec("ALTER TABLE `users` ADD UNIQUE KEY `email_role` (`email`, `role`) ");
        echo "Added composite unique index (email, role).<br>";
    } catch (Exception $e) {
        echo "Note: Composite index already exists. Skipping add.<br>";
    }

    echo "<b>Migration successful!</b>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
