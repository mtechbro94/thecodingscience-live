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

    case 'courses':
        require 'views/courses.php';
        break;

    case 'enroll':
        require 'views/enroll.php';
        break;

    case 'internships':
        require 'views/internships.php';
        break;

    case 'career':
        require 'views/career.php';
        break;

    case 'contact':
        require 'views/contact.php';
        break;

    case 'login':
        if (!isset($_GET['role']) && !isset($_POST['login_role']) && !is_logged_in()) {
            require 'views/auth_selection.php';
        } else {
            require 'views/login.php';
        }
        break;

    case 'register':
        if (!isset($_GET['role']) && !isset($_POST['register_role']) && !is_logged_in()) {
            require 'views/auth_selection.php';
        } else {
            require 'views/register.php';
        }
        break;

    case 'verify-otp':
        require 'views/verify_otp.php';
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

    case 'trainer-blogs':
        require 'views/trainer_blogs.php';
        break;

    case 'trainer-blog':
        require 'views/trainer_blog_form.php';
        break;

    case 'profile':
        require 'views/profile.php';
        break;

    case 'social-init':
    case 'social-init.php':
        if (file_exists('social_init.php')) {
            require 'social_init.php';
        } else {
            set_flash('danger', 'Social login is temporarily unavailable.');
            redirect('/login');
        }
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
    case 'admin/course-content':
    case 'admin/course_content.php':
        require 'admin/course_content.php';
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
    case 'admin/coupons':
    case 'admin/coupons.php':
        require 'admin/coupons.php';
        break;
    case 'admin/career_tracks':
    case 'admin/career_tracks.php':
        require 'admin/career_tracks.php';
        break;
    case 'admin/career_track_form':
    case 'admin/career_track_form.php':
        require 'admin/career_track_form.php';
        break;
    case 'admin/success_stories':
    case 'admin/success_stories.php':
        require 'admin/success_stories.php';
        break;
    case 'admin/success_story_form':
    case 'admin/success_story_form.php':
        require 'admin/success_story_form.php';
        break;
    case 'admin/internships':
    case 'admin/internships.php':
        require 'admin/internships.php';
        break;
    case 'admin/internship_form':
    case 'admin/internship_form.php':
        require 'admin/internship_form.php';
        break;
    case 'admin/trainer_positions':
    case 'admin/trainer_positions.php':
        require 'admin/trainer_positions.php';
        break;
    case 'admin/trainer_position_form':
    case 'admin/trainer_position_form.php':
        require 'admin/trainer_position_form.php';
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
        } elseif (preg_match('/^course-content\/(\d+)$/', $path, $matches)) {
            $id = $matches[1];
            require 'views/course_content.php';
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
        } elseif (preg_match('/^career-track\/([a-z0-9-]+)$/', $path, $matches)) {
            $track_slug = $matches[1];
            require 'views/career_track.php';
        } elseif (preg_match('/^apply\/(internship|career)\/(\d+)$/', $path, $matches)) {
            $type = $matches[1];
            $id = $matches[2];
            require 'views/apply_handler.php';
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