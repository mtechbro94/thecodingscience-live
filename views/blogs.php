<?php
// views/blogs.php

// Fetch All Published Blogs
$stmt = $pdo->query("SELECT * FROM blogs WHERE is_published = 1 ORDER BY created_at DESC");
$blogs = $stmt->fetchAll();

$page_title = "Our Blog";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-3">Insights from The Coding Science</h1>
            <p class="lead text-muted">Stay updated with the latest trends in tech and AI.</p>
        </div>

        <div class="row">
            <?php foreach ($blogs as $blog): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-lift">
                        <div class="overflow-hidden">
                            <img src="<?php echo get_image_url($blog['image']); ?>" class="card-img-top zoom-effect"
                                alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                style="height: 220px; object-fit: cover;">
                        </div>

                        <div class="card-body p-4">
                            <h5 class="card-title font-weight-bold mb-3">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </h5>
                            <p class="card-text text-muted mb-4">
                                <?php echo htmlspecialchars($blog['excerpt']); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 px-4 pb-4">
                            <a href="/blog/<?php echo $blog['slug']; ?>" class="btn btn-outline-primary rounded-pill">Read
                                More</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($blogs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                <h3>No articles published yet</h3>
                <p>Check back soon for new content.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>