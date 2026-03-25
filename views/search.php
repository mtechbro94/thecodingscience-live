<?php
// views/search.php
$query = sanitize($_GET['q'] ?? '');
$results = [];

if (!empty($query)) {
    $results = search_content($query);
}

$page_title = "Search Results for '" . htmlspecialchars($query) . "'";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="fw-bold">Search Results</h2>
                <p class="text-muted">Showing results for: <span class="text-primary fw-bold"><?php echo htmlspecialchars($query); ?></span></p>
                <hr>
            </div>
        </div>

        <?php if (empty($results)): ?>
            <div class="row justify-content-center text-center py-5">
                <div class="col-md-6">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted opacity-25"></i>
                    </div>
                    <h4>No results found</h4>
                    <p class="text-muted">We couldn't find anything matching your search. Try different keywords or browse our categories.</p>
                    <a href="/courses" class="btn btn-primary rounded-pill px-4 mt-3">Browse All Courses</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($results as $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 hover-lift overflow-hidden">
                            <div class="position-relative">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?php echo get_image_url($item['image']); ?>" alt="Result" class="card-img-top" style="height: 180px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                        <i class="fas fa-<?php echo ($item['type'] === 'course') ? 'graduation-cap' : 'file-alt'; ?> fa-3x text-muted opacity-25"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-<?php echo ($item['type'] === 'course') ? 'primary' : 'success'; ?> rounded-pill shadow-sm px-3 uppercase small">
                                        <?php echo ucfirst($item['type']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h5>
                                <p class="card-text text-muted small">
                                    <?php 
                                    $excerpt = strip_tags($item['content']);
                                    echo htmlspecialchars(substr($excerpt, 0, 120)) . (strlen($excerpt) > 120 ? '...' : ''); 
                                    ?>
                                </p>
                            </div>
                            <div class="card-footer bg-white border-0 p-4 pt-0 text-end">
                                <a href="<?php echo ($item['type'] === 'course') ? '/course-detail/' . $item['id'] : '/blog-detail/' . $item['id']; ?>" class="btn btn-link p-0 text-primary fw-bold text-decoration-none">
                                    Learn More <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
