<?php
// views/404.php
$page_title = "Page Not Found";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 100px;">
    <div class="container text-center">
        <div class="display-1 text-muted mb-4">404</div>
        <h1 class="mb-4">Oops! Page Not Found</h1>
        <p class="lead text-muted mb-5">The page you're looking for doesn't exist or has been moved.</p>
        <a href="/" class="btn btn-primary btn-lg">
            <i class="fas fa-home me-2"></i> Go Back Home
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>