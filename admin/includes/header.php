<?php
// admin/includes/header.php

// Ensure user is admin
require_once BASE_PATH . '/includes/functions.php';



if (!is_admin()) {
    set_flash('danger', 'Access denied. Administrator privileges required.');
    redirect('/login');
}

$profile_img = $_SESSION['user_profile_image'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($page_title) ? $page_title . ' - Admin Panel' : 'Admin Panel'; ?>
    </title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }

        .sidebar a:hover,
        .sidebar a.active {
            color: #fff;
            background-color: #495057;
        }

        .content {
            padding: 20px;
        }
    </style>
</head>

<body>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white" style="width: 280px;">
            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4 fw-bold">TCS Admin</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="/admin/dashboard"
                        class="nav-link <?php echo ($page_title == 'Dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="/admin/users" class="nav-link <?php echo ($page_title == 'Users') ? 'active' : ''; ?>">
                        <i class="fas fa-users me-2"></i> Users
                    </a>
                </li>
                <li>
                    <a href="/admin/courses" class="nav-link <?php echo ($page_title == 'Courses') ? 'active' : ''; ?>">
                        <i class="fas fa-book me-2"></i> Courses
                    </a>
                </li>
                <li>
                    <a href="/admin/enrollments"
                        class="nav-link <?php echo ($page_title == 'Enrollments') ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap me-2"></i> Enrollments
                    </a>
                </li>
                <li>
                    <a href="/admin/messages"
                        class="nav-link <?php echo ($page_title == 'Messages') ? 'active' : ''; ?>">
                        <i class="fas fa-envelope me-2"></i> Messages
                    </a>
                </li>
                <li>
                    <a href="/admin/blogs"
                        class="nav-link <?php echo ($page_title == 'Blog' || $page_title == 'Manage Blog') ? 'active' : ''; ?>">
                        <i class="fas fa-pen-nib me-2"></i> Blog
                    </a>
                </li>
                <li>
                    <a href="/admin/coupons"
                        class="nav-link <?php echo ($page_title == 'Manage Coupons') ? 'active' : ''; ?>">
                        <i class="fas fa-ticket-alt me-2"></i> Coupons
                    </a>
                </li>
                <li>
                    <a href="/admin/career_tracks"
                        class="nav-link <?php echo ($page_title == 'Career Track Programs') ? 'active' : ''; ?>">
                        <i class="fas fa-road me-2"></i> Career Tracks
                    </a>
                </li>
                <li>
                    <a href="/admin/internships"
                        class="nav-link <?php echo (strpos($page_title, 'Internships') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-briefcase me-2"></i> Student Internships
                    </a>
                </li>

                <li>
                    <a href="/admin/settings"
                        class="nav-link <?php echo ($page_title == 'Site Settings') ? 'active' : ''; ?>">
                        <i class="fas fa-cog me-2"></i> Site Settings
                    </a>
                </li>
                <li>
                    <a href="/admin/success_stories"
                        class="nav-link <?php echo (strpos($page_title, 'Success Story') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-star me-2"></i> Success Stories
                    </a>
                </li>

            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                    id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if ($profile_img): ?>
                        <img src="<?php echo get_avatar(['profile_image' => $profile_img]); ?>" alt="Profile"
                            class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                    <?php else: ?>
                        <div class="avatar-circle avatar-circle-sm bg-primary me-2">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <strong>
                        <?php echo $_SESSION['user_name']; ?>
                    </strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="/">View Site</a></li>
                    <li><a class="dropdown-item" href="/profile">Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="/logout">Sign out</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-100 content">
            <!-- Flash Messages -->
            <?php
            $flash = get_flash();
            if ($flash):
                ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
