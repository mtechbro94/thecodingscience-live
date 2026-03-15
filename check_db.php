<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query('DESCRIBE trainer_positions');
    echo "Table: trainer_positions\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Field: {$row['Field']} | Type: {$row['Type']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
