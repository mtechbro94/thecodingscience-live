<?php
// index.php - Main Router (with fallback for direct file access)

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$path = '';

// Check if path is passed via query parameter (for PHP built-in server routing)
if (isset($_GET['_route_'])) {
    $path = $_GET['_route_'];
} else {
    // Get the current path from the URL
    $request_uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($request_uri, PHP_URL_PATH);
}

// Remove leading/trailing slashes for consistent matching
$path = trim($path, '/');

// For PHP built-in server: handle all routes through this index.php
if (empty($path)) {
    $path = 'home';
}

// Simple Router
switch ($path) {
    case '':
    case 'index.php':
    case 'home':
        require 'views/home.php';
        break;

    case 'about':
        require 'views/about.php';
        break;

    case 'services':
        require 'views/services.php';
        break;

    case 'courses':
        require 'views/courses.php';
        break;

    case 'internships':
        require 'views/internships.php';
        break;

    case 'contact':
        require 'views/contact.php';
        break;

    case 'login':
        require 'views/login.php';
        break;

    case 'register':
        require 'views/register.php';
        break;

    case 'logout':
        require 'views/logout.php';
        break;

    case 'forgot-password':
        require 'views/forgot-password.php';
        break;

    case 'reset-password':
        require 'views/reset-password.php';
        break;

    case 'razorpay-payment':
    case 'razorpay-payment.php':
        require 'views/razorpay_payment.php';
        break;

    case 'payment-callback':
        require 'views/payment_callback.php';
        break;

    case 'payment-success':
        require 'views/payment_success.php';
        break;

    case 'payment-failed':
        require 'views/payment_failed.php';
        break;

    case 'dashboard':
        require 'views/dashboard.php';
        break;

    case 'trainer-dashboard':
        require 'views/trainer_dashboard.php';
        break;

    case 'profile':
        require 'views/profile.php';
        break;

    case 'social-init':
    case 'social-init.php':
        require 'social_init.php';
        break;


    // Admin Routes
    case 'admin/dashboard':
    case 'admin/dashboard.php':
        require 'admin/dashboard.php';
        break;
    case 'admin/users':
    case 'admin/users.php':
        require 'admin/users.php';
        break;
    case 'admin/courses':
    case 'admin/courses.php':
        require 'admin/courses.php';
        break;
    case 'admin/course_form':
    case 'admin/course_form.php':
        require 'admin/course_form.php';
        break;
    case 'admin/enrollments':
    case 'admin/enrollments.php':
        require 'admin/enrollments.php';
        break;
    case 'admin/messages':
    case 'admin/messages.php':
        require 'admin/messages.php';
        break;
    case 'admin/settings':
    case 'admin/settings.php':
        require 'admin/settings.php';
        break;
    case 'admin/blogs':
    case 'admin/blogs.php':
        require 'admin/blogs.php';
        break;
    case 'admin/blog_form':
    case 'admin/blog_form.php':
        require 'admin/blog_form.php';
        break;


    case 'blogs':
        require 'views/blogs.php';
        break;

    default:
        // Dynamic Routes
        if (preg_match('/^course\/(\d+)$/', $path, $matches)) {
            $id = $matches[1];
            require 'views/course_detail.php';
        } elseif (preg_match('/^blog\/([a-z0-9-]+)$/', $path, $matches)) {
            $blog_slug = $matches[1];
            require 'views/blog_detail.php';
        } elseif (preg_match('/^enroll\/(\d+)$/', $path, $matches)) {
            $id = $matches[1];
            require 'views/enroll.php';
        } elseif (preg_match('/^razorpay-payment\/(\d+)$/', $path, $matches)) {
            $id = $matches[1];
            require 'views/razorpay_payment.php';
        } elseif (preg_match('/^verify-payment/', $path, $matches)) {
            require 'views/verify_payment.php';
        } elseif (preg_match('/^payment-success/', $path, $matches)) {
            require 'views/payment_success.php';
        } elseif (preg_match('/^payment-failed/', $path, $matches)) {
            require 'views/payment_failed.php';
        } elseif (preg_match('/^submit-payment\/(\d+)$/', $path, $matches)) {
            $id = $matches[1];
            require 'views/submit_payment.php';
        } elseif (preg_match('/^social-login\/([a-z]+)$/', $path, $match)) {
            require 'views/social_login.php';
        } elseif (preg_match('/^social-callback\/([a-z]+)$/', $path, $match)) {
            require 'views/social_callback.php';
        } elseif (preg_match('/^apply-internship\/(\d+)$/', $path, $matches)) {
            $id = $matches[1];
            require 'views/apply_internship.php';
        } else {

            http_response_code(404);
            if (file_exists('views/404.php')) {
                require 'views/404.php';
            } else {
                echo "<h1>404 Not Found</h1>";
                echo "<p>Path: " . htmlspecialchars($path) . "</p>";
            }

        }
        break;
}
?>