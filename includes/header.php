<?php
// includes/header.php
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($page_title) ? $page_title . ' - ' . get_setting('site_name', SITE_NAME) : get_setting('site_name', SITE_NAME); ?>
    </title>

    <!-- Favicon -->
    <?php
    $favicon = get_setting('site_favicon', '');
    $favicon_url = !empty($favicon) ? '/assets/images/' . htmlspecialchars($favicon) : '/assets/images/favicon.ico';
    $favicon_ext = pathinfo($favicon, PATHINFO_EXTENSION);
    $favicon_type = in_array($favicon_ext, ['png', 'gif', 'webp']) ? 'image/' . $favicon_ext : 'image/x-icon';
    ?>
    <link rel="icon" type="<?php echo $favicon_type; ?>" href="<?php echo $favicon_url; ?>">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo isset($og_url) ? $og_url : SITE_URL . $_SERVER['REQUEST_URI']; ?>">


    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Custom CSS -->
    <!-- AOS Animations CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Theme Initialization (Prevent Flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- Custom CSS (with versioning) -->
    <link rel="stylesheet" href="/assets/css/style.css?v=1.1.0">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo isset($og_type) ? $og_type : 'website'; ?>">
    <meta property="og:url" content="<?php echo isset($og_url) ? $og_url : SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title"
        content="<?php echo isset($og_title) ? htmlspecialchars($og_title) : (isset($page_title) ? $page_title . ' - ' . get_setting('site_name', SITE_NAME) : get_setting('site_name', SITE_NAME)); ?>">
    <meta property="og:description"
        content="<?php echo isset($og_description) ? htmlspecialchars($og_description) : 'Transform your career with specialized industry-led training in Web Development, Python, and AI.'; ?>">
    <meta property="og:image" content="<?php
    $logo = get_setting('site_logo', '');
    echo isset($og_image) ? $og_image : (!empty($logo) ? SITE_URL . '/assets/images/' . $logo : SITE_URL . '/assets/images/logo.png');
    ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo isset($og_url) ? $og_url : SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="twitter:title"
        content="<?php echo isset($og_title) ? htmlspecialchars($og_title) : (isset($page_title) ? $page_title : get_setting('site_name', SITE_NAME)); ?>">
    <meta property="twitter:description"
        content="<?php echo isset($og_description) ? htmlspecialchars($og_description) : 'Transform your career with specialized industry-led training in Web Development, Python, and AI.'; ?>">
    <meta property="twitter:image" content="<?php
    $logo = get_setting('site_logo', '');
    echo isset($og_image) ? $og_image : (!empty($logo) ? SITE_URL . '/assets/images/' . $logo : SITE_URL . '/assets/images/logo.png');
    ?>">

    <?php if (isset($extra_css))
        echo $extra_css; ?>

    <!-- Google Search Console Verification -->
    <?php
    $gsc_verification = get_setting('google_search_console_verification', '');
    if (!empty($gsc_verification)): ?>
        <meta name="google-site-verification" content="<?php echo htmlspecialchars($gsc_verification); ?>">
    <?php endif; ?>

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0TMYFTPESW"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-0TMYFTPESW');
    </script>

</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-custom">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="/">
                <?php
                $logo = get_setting('site_logo', '');
                if (!empty($logo)): ?>
                    <img src="/assets/images/<?php echo htmlspecialchars($logo); ?>" alt="Logo" class="navbar-logo me-2">
                <?php endif; ?>
                <strong>
                    <?php echo get_setting('site_name', SITE_NAME); ?>
                </strong>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">About</a>
                    </li>
                    <!-- Courses with hover popup -->
                    <li class="nav-item dropdown nav-hover-dropdown">
                        <a class="nav-link dropdown-toggle" href="/courses" id="coursesDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Courses
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-mega" aria-labelledby="coursesDropdown">
                            <div class="dropdown-mega-inner">
                                <h6 class="dropdown-header text-primary">Our Courses</h6>
                                <a class="dropdown-item" href="/courses">Crash Course in Computer Science</a>
                                <a class="dropdown-item" href="/courses">Programming with Python</a>
                                <a class="dropdown-item" href="/courses">Full Stack Web Development</a>
                                <a class="dropdown-item" href="/courses">Data Science from Scratch</a>
                                <a class="dropdown-item" href="/courses">Machine Learning and AI Foundations</a>
                                <a class="dropdown-item" href="/courses">Ethical Hacking and Cybersecurity</a>
                                <div class="dropdown-cta text-center mt-3">
                                    <a href="/courses" class="btn btn-sm btn-primary">View All Courses</a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Internships with hover popup -->
                    <li class="nav-item dropdown nav-hover-dropdown">
                        <a class="nav-link dropdown-toggle" href="/internships" id="internshipsDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Internships
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-mega"
                            aria-labelledby="internshipsDropdown">
                            <div class="dropdown-mega-inner">
                                <h6 class="dropdown-header text-success">Student Internship Programs</h6>
                                <?php
                                // Fetch and display active internships dynamically
                                $student_internships = get_internships_by_category('industrial');
                                if (!empty($student_internships)):
                                    foreach ($student_internships as $internship):
                                        echo '<a class="dropdown-item" href="/internships">' . htmlspecialchars($internship['title']) . '</a>';
                                    endforeach;
                                else:
                                    echo '<span class="dropdown-item disabled">No internships available</span>';
                                endif;
                                ?>
                                <div class="dropdown-cta text-center mt-3">
                                    <a href="/internships" class="btn btn-sm btn-success">View All Internships</a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/career">Career</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                    <li class="nav-item nav-divider"></li>
                    <?php if (is_logged_in()):
                        $user = current_user(); ?>
                        <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?php echo get_avatar($user); ?>" alt="Avatar" class="rounded-circle me-2"
                                    style="width: 32px; height: 32px; object-fit: cover; border: 1px solid #ddd;">
                            <?php else: ?>
                                <div class="avatar-circle avatar-circle-sm bg-primary me-2">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($user['name']); ?>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/dashboard"><i
                                        class="fas fa-chart-line me-2"></i>Dashboard</a></li>
                            <?php if (is_admin()): ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/admin/dashboard"><i
                                            class="fas fa-tachometer-alt me-2"></i>Admin Panel</a></li>
                            <?php endif; ?>


                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="/logout"><i
                                        class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary btn-lg ms-2 px-4" href="/login">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Search Form -->
                <form action="/index.php" method="GET" class="d-flex mx-lg-4 my-2 my-lg-0">
                    <input type="hidden" name="_route_" value="search">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control border-end-0 border-primary-subtle" placeholder="Search courses..." aria-label="Search" style="border-radius: 20px 0 0 20px; width: 150px;" required>
                        <button class="btn btn-outline-primary border-start-0" type="submit" style="border-radius: 0 20px 20px 0;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <div class="navbar-social">
                    <?php if (get_setting('facebook_url')): ?>
                        <a class="social-link facebook-link" href="<?php echo get_setting('facebook_url'); ?>"
                            title="Facebook" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-facebook"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (get_setting('linkedin_url')): ?>
                        <a class="social-link linkedin-link" href="<?php echo get_setting('linkedin_url'); ?>"
                            title="LinkedIn" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-linkedin"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (get_setting('instagram_url')): ?>
                        <a class="social-link instagram-link" href="<?php echo get_setting('instagram_url'); ?>"
                            title="Instagram" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (get_setting('youtube_url')): ?>
                        <a class="social-link youtube-link" href="<?php echo get_setting('youtube_url'); ?>" title="YouTube"
                            target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php
    $flash = get_flash();
    if ($flash):
        $category = $flash['type'] == 'error' ? 'danger' : $flash['type'];
        ?>
        <div class="container mt-5 pt-4">
            <div class="alert alert-<?php echo $category; ?> alert-dismissible fade show" role="alert">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">