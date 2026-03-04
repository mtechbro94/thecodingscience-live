<?php
require_once __DIR__ . '/includes/db.php';

try {
    echo "Starting migration for featured courses...\n";
    $pdo->exec("ALTER TABLE courses ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
    echo "Migration successful: Added is_featured to courses table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Migration skipped: Column already exists.\n";
    } else {
        echo "Migration failed: " . $e->getMessage() . "\n";
    }
}
?>