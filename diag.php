<?php
// diag.php
require_once 'includes/db.php';
header('Content-Type: text/plain');

try {
    echo "Checking table: trainer_positions\n";
    $stmt = $pdo->query("DESCRIBE trainer_positions");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Default: {$row['Default']}\n";
    }
    
    echo "\nChecking recently added rows:\n";
    $stmt = $pdo->query("SELECT id, title, created_at FROM trainer_positions ORDER BY created_at DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Title: {$row['title']} | Created: {$row['created_at']}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
