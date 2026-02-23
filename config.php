<?php
// config.php - Main Configuration File

require_once __DIR__ . '/includes/env_loader.php';
loadEnv(__DIR__ . '/.env');

// Environment (development/production)
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');

// Error Reporting logic
if (ENVIRONMENT === 'local' || ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Base Path for includes
define('BASE_PATH', __DIR__);

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: '');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Site Configuration
define('SITE_NAME', getenv('SITE_NAME') ?: 'The Coding Science');
define('SITE_URL', getenv('SITE_URL') ?: 'https://thecodingscience.com');
define('CONTACT_EMAIL', 'csdsofficial249@gmail.com');
define('CONTACT_PHONE', '+917006196821');

// Payment Gateway Configuration
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');

// Email Configuration (Gmail SMTP)
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 465);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>