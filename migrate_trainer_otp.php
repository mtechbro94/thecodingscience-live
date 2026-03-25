<?php
// migrate_trainer_otp.php
require_once 'includes/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS `trainer_otps` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(120) NOT NULL,
      `role` varchar(20) NOT NULL DEFAULT 'trainer',
      `otp_code` varchar(10) NOT NULL,
      `password_hash` varchar(255) DEFAULT NULL,
      `type` enum('login', 'signup', 'forgot_password') NOT NULL,
      `expires_at` datetime NOT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `email_role` (`email`, `role`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'trainer_otps' created successfully!\n";
    
    // Also update users unique key if not already done (precautionary)
    // $pdo->exec("ALTER TABLE users DROP INDEX IF EXISTS email;");
    // $pdo->exec("ALTER TABLE users ADD UNIQUE INDEX email_role (email, role);");
    
    echo "Migration successful!";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
