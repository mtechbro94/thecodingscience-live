<?php
// views/courses.php

// Fetch All Courses
$stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
$courses = $stmt->fetchAll();

$page_title = "Our Courses";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-3">Explore Our Courses</h1>
            <p class="lead text-muted">Master the latest technologies with our industry-aligned curriculum.</p>
        </div>

        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm course-card">
                        <div class="course-image-wrapper">
                            <?php if ($course['image']): ?>
                                <img src="<?php echo get_image_url($course['image']); ?>" class="card-img-top"
                                    alt="<?php echo htmlspecialchars($course['name']); ?>"
                                    style="height: 200px; object-fit: cover;">

                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-code fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <span class="badge bg-primary course-badge">
                                <?php echo $course['level']; ?>
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <?php echo $course['name']; ?>
                            </h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php
                                $desc = $course['summary'] ?: $course['description'];
                                echo substr($desc, 0, 120) . '...';
                                ?>
                            </p>
                            <div class="course-meta mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small><i class="fas fa-clock text-primary me-1"></i>
                                        <?php echo $course['duration']; ?>
                                    </small>
                                    <small><i class="fas fa-signal text-success me-1"></i>
                                        <?php echo $course['level']; ?>
                                    </small>
                                </div>
                                <h5 class="text-primary mb-0">₹
                                    <?php echo number_format($course['price'], 2); ?>
                                </h5>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-3">
                            <a href="/course/<?php echo $course['id']; ?>" class="btn btn-outline-primary w-100">View
                                Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- No Courses Found -->
        <?php if (empty($courses)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No courses found</h3>
                <p>Check back later for new courses.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>