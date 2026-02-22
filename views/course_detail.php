<?php
// views/course_detail.php

// Get Course ID from URL
$course_id = isset($id) ? (int) $id : 0;

if ($course_id === 0) {
    redirect('/courses');
}

// Fetch Course Details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/courses');
}

$page_title = $course['name'];

// Check if user is enrolled (if logged in)
$is_enrolled = false;
if (is_logged_in()) {
    $user_id = current_user()['id'];
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'completed'");
    $stmt->execute([$user_id, $course_id]);
    $is_enrolled = $stmt->fetch() ? true : false;
}

// Fetch Reviews (Limit 5 for now)
$stmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM course_reviews r JOIN users u ON r.user_id = u.id WHERE r.course_id = ? AND r.is_approved = 1 ORDER BY r.created_at DESC LIMIT 5");
$stmt->execute([$course_id]);
$reviews = $stmt->fetchAll();

// Calculate Average Rating
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM course_reviews WHERE course_id = ? AND is_approved = 1");
$stmt->execute([$course_id]);
$rating_data = $stmt->fetch();
$avg_rating = $rating_data['avg_rating'] ? number_format($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'];

require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <?php if ($course['image']): ?>
                    <img src="<?php echo get_image_url($course['image']); ?>" alt="<?php echo $course['name']; ?>"
                        class="img-fluid rounded mb-4 shadow" loading="lazy" decoding="async">
                <?php endif; ?>

                <h1 class="mb-3">
                    <?php echo $course['name']; ?>
                </h1>
                <div class="mb-4">
                    <span class="badge bg-primary me-2">
                        <?php echo $course['level']; ?>
                    </span>
                    <span class="badge bg-info me-2"><i class="fas fa-clock"></i>
                        <?php echo $course['duration']; ?>
                    </span>
                    <span class="badge bg-success"><i class="fas fa-rupee-sign"></i> ₹
                        <?php echo number_format($course['price'], 2); ?>
                    </span>
                </div>

                <div class="course-description mb-4">
                    <h3><strong>About This Course</strong></h3>
                    <div style="line-height: 1.8; font-size: 1rem; color: #333;">
                        <?php echo nl2br($course['description']); ?>
                    </div>
                </div>

                <!-- Curriculum Section -->
                <?php
                $curriculum = json_decode($course['curriculum'], true);
                if ($curriculum && is_array($curriculum)):
                    ?>
                    <div class="course-curriculum mb-4">
                        <h3 class="mb-4"><strong>Course Curriculum</strong></h3>
                        <div class="accordion shadow-sm" id="curriculumAccordion">
                            <?php foreach ($curriculum as $index => $item): ?>
                                <?php if (is_array($item) && isset($item['title'])): ?>
                                    <!-- Module-wise Curriculum -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                            <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse<?php echo $index; ?>"
                                                aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                                aria-controls="collapse<?php echo $index; ?>">
                                                <div class="d-flex justify-content-between w-100 me-3">
                                                    <span><strong><?php echo htmlspecialchars($item['title']); ?></strong></span>
                                                    <?php if (isset($item['topics']) && is_array($item['topics'])): ?>
                                                        <span
                                                            class="badge bg-light text-dark rounded-pill"><?php echo count($item['topics']); ?>
                                                            Lessons</span>
                                                    <?php endif; ?>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo $index; ?>"
                                            class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                            aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#curriculumAccordion">
                                            <div class="accordion-body p-0">
                                                <ul class="list-group list-group-flush">
                                                    <?php if (isset($item['topics']) && is_array($item['topics'])): ?>
                                                        <?php foreach ($item['topics'] as $topic): ?>
                                                            <li class="list-group-item d-flex align-items-center py-3">
                                                                <i class="fas fa-play-circle text-primary me-3"></i>
                                                                <span><?php echo htmlspecialchars($topic); ?></span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <li class="list-group-item text-muted">No topics listed for this module.</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Simple List Curriculum (Fallback) -->
                                    <?php if ($index === 0): ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                    <strong>What you will learn</strong>
                                                </button>
                                            </h2>
                                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                                                data-bs-parent="#curriculumAccordion">
                                                <div class="accordion-body">
                                                    <ul class="list-group list-group-flush">
                                                    <?php endif; ?>
                                                    <li class="list-group-item">
                                                        <i class="fas fa-check-circle text-primary me-2"></i>
                                                        <?php echo htmlspecialchars($item); ?>
                                                    </li>
                                                    <?php if ($index === count($curriculum) - 1): ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow-lg sticky-top" style="top: 100px;">
                    <div class="card-body text-center">
                        <h3 class="card-title mb-3">Enroll Now</h3>
                        <div class="mb-3">
                            <h2 class="text-primary">₹
                                <?php echo number_format($course['price'], 2); ?>
                            </h2>
                            <small class="text-muted">One-time payment</small>
                        </div>

                        <?php if ($is_enrolled): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> You are enrolled!
                            </div>
                            <a href="/dashboard" class="btn btn-success w-100">
                                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                            </a>
                        <?php else: ?>
                            <form action="/enroll/<?php echo $course['id']; ?>" method="POST">
                                <button type="submit" class="btn btn-primary btn-lg w-100 enroll-btn py-3 shadow">
                                    <i class="fas fa-shopping-cart me-2"></i> Enroll Now
                                </button>
                            </form>
                            <?php if (!is_logged_in()): ?>
                                <p class="text-muted small mt-2">Login or register to get started</p>
                            <?php endif; ?>
                        <?php endif; ?>

                        <hr class="my-3">

                        <div class="text-start">
                            <h6><strong>Course Includes:</strong></h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>
                                    <?php echo $course['duration']; ?> of training
                                </li>
                                <li><i class="fas fa-check text-success me-2"></i> Live sessions</li>
                                <li><i class="fas fa-check text-success me-2"></i> Assignments & Projects</li>
                                <li><i class="fas fa-check text-success me-2"></i> Certificate</li>
                                <li><i class="fas fa-check text-success me-2"></i> Community access</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section mt-5">
            <h3 class="mb-4">
                <i class="fas fa-star text-warning"></i> Student Reviews
                <span class="badge bg-secondary ms-2">
                    <?php echo $total_reviews; ?>
                </span>
            </h3>

            <?php if ($total_reviews > 0): ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <h2 class="text-warning">
                                <?php echo $avg_rating; ?>
                            </h2>
                            <div class="star-rating mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i
                                        class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-secondary'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted">Based on
                                <?php echo $total_reviews; ?> reviews
                            </small>
                        </div>
                    </div>
                </div>

                <div id="reviewsList" class="mt-4">
                    <?php foreach ($reviews as $review): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-1">
                                            <?php echo htmlspecialchars($review['user_name']); ?>
                                        </h6>
                                        <div class="mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i
                                                    class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo $review['created_at']; ?>
                                        </small>
                                    </div>
                                </div>
                                <?php if ($review['review_text']): ?>
                                    <p class="card-text mt-2">
                                        <?php echo htmlspecialchars($review['review_text']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No reviews yet. Be the first to review this course!
                </div>
            <?php endif; ?>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <a href="/courses" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>