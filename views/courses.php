<?php
// views/courses.php

$page_title = "Our Courses";
require_once 'includes/header.php';

// Fetch search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$level = isset($_GET['level']) ? sanitize($_GET['level']) : '';

// Build Query
$query = "SELECT * FROM courses WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ? OR summary LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($level)) {
    $query .= " AND level LIKE ?";
    $params[] = "%$level%";
}

$query .= " ORDER BY id ASC";

// Fetch courses from database
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Fetch active coupons to display on page
$now = date('Y-m-d H:i:s');
$stmt_coupon = $pdo->prepare("SELECT * FROM coupons WHERE is_active = 1 AND (valid_from IS NULL OR valid_from <= ?) AND (valid_until IS NULL OR valid_until >= ?) ORDER BY discount_value DESC LIMIT 3");
$stmt_coupon->execute([$now, $now]);
$active_coupons = $stmt_coupon->fetchAll();

// Decode curriculum for each course
foreach ($courses as &$course) {
    if (!empty($course['curriculum'])) {
        $course['curriculum'] = json_decode($course['curriculum'], true);
    } else {
        $course['curriculum'] = [];
    }
}
unset($course);

// Career Tracks Data (from database) - only show if no active filters or if relevant
$career_tracks = [];
if (empty($search) && empty($level)) {
    $stmt = $pdo->query("SELECT * FROM career_tracks WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");
    $career_tracks = $stmt->fetchAll();

    foreach ($career_tracks as &$track) {
        $stmt = $pdo->prepare("
            SELECT c.id, c.name, c.price 
            FROM courses c 
            JOIN career_track_courses cc ON c.id = cc.course_id 
            WHERE cc.track_id = ? 
            ORDER BY cc.sort_order
        ");
        $stmt->execute([$track['id']]);
        $track['courses_list'] = $stmt->fetchAll();
    }
    unset($track);
}

function getLevelBadgeClass($level)
{
    if (strpos($level, 'Beginner') !== false && strpos($level, 'Intermediate') !== false) {
        return 'bg-success';
    } elseif (strpos($level, 'Beginner') !== false) {
        return 'bg-info';
    } elseif (strpos($level, 'Intermediate to Advanced') !== false) {
        return 'bg-warning text-dark';
    } elseif (strpos($level, 'Intermediate') !== false) {
        return 'bg-primary';
    }
    return 'bg-secondary';
}
?>

<style>
    .bg-primary-subtle { background-color: #e0e7ff !important; }
    .bg-success-subtle { background-color: #dcfce7 !important; }
    .bg-warning-subtle { background-color: #fef3c7 !important; }
    .bg-info-subtle { background-color: #cff4fc !important; }
    .bg-dark-subtle { background-color: #e9ecef !important; }

    .course-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        transition: all 0.3s ease;
        overflow: visible;
        display: flex;
        flex-direction: column;
    }

    .course-card .course-image-wrapper {
        position: relative;
        width: 100%;
        padding-top: 56.25%;
        overflow: hidden;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    .course-card .course-card-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .course-card .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .course-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
        border-color: var(--primary-color);
    }

    .level-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        z-index: 2;
    }

    .level-legend-badge {
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }

    .combo-card {
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        transition: all 0.3s ease;
        background: linear-gradient(to bottom, #ffffff, #f8fafc);
        overflow: visible;
    }

    .combo-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
    }

    .combo-badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.5rem 1.5rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.85rem;
        white-space: nowrap;
        z-index: 10;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    }

    .original-price {
        text-decoration: line-through;
        color: #94a3b8;
        font-size: 1rem;
        font-weight: 500;
    }

    .combo-price {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--primary-color);
    }

    .combo-price .currency {
        font-size: 1rem;
        font-weight: 600;
        vertical-align: super;
    }

    .combo-original-price {
        font-size: 1.1rem;
        font-weight: 600;
        color: #6b7280;
        text-decoration: line-through;
    }

    .save-percent {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
    }

    .course-list-item {
        background: rgba(0,0,0,0.03);
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    [data-theme="dark"] .course-list-item {
        background: rgba(255,255,255,0.05);
    }

    .section-header {
        position: relative;
        display: inline-block;
    }

    .section-header::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    /* Course Filters Styling */
    .course-filters-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .course-filters-box {
        background: #1e293b;
        border-color: #334155;
    }

    .filter-label {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        color: var(--secondary-color);
    }
</style>

<!-- Individual Courses Section -->
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <!-- Active Coupon Banner -->
        <?php if (!empty($active_coupons)): ?>
            <div class="coupon-banner-wrapper mb-5">
                <?php foreach ($active_coupons as $coupon): ?>
                    <?php
                    $discount_label = $coupon['discount_type'] === 'percentage'
                        ? $coupon['discount_value'] . '% OFF'
                        : '₹' . number_format($coupon['discount_value']) . ' OFF';
                    $expiry_text = '';
                    if (!empty($coupon['valid_until'])) {
                        $expiry_text = 'Expires ' . date('d M Y', strtotime($coupon['valid_until']));
                    }
                    ?>
                    <div class="coupon-banner d-flex flex-wrap align-items-center justify-content-between gap-3 p-4 rounded-4 mb-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="coupon-icon d-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); color: white;">
                                <i class="fas fa-tag fa-lg"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-semibold fs-6 text-white">🎉 Special Offer — Use code to get <strong><?php echo $discount_label; ?></strong>!</p>
                                <?php if (!empty($coupon['min_purchase'])): ?>
                                    <small class="text-white-50">Min. purchase ₹<?php echo number_format($coupon['min_purchase']); ?><?php echo $expiry_text ? ' &bull; ' . $expiry_text : ''; ?></small>
                                <?php elseif ($expiry_text): ?>
                                    <small class="text-white-50"><?php echo $expiry_text; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="coupon-code-box" style="background: rgba(255,255,255,0.15); border: 2px dashed rgba(255,255,255,0.5); border-radius: 10px; padding: 0.4rem 1.2rem; letter-spacing: 3px;">
                                <span class="coupon-code-text fw-bold text-white"><?php echo htmlspecialchars($coupon['code']); ?></span>
                            </div>
                            <button class="btn btn-light btn-sm fw-semibold px-3 copy-coupon-btn"
                                onclick="copyCoupon('<?php echo htmlspecialchars($coupon['code']); ?>', this)">
                                <i class="fas fa-copy me-1"></i> Copy
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <style>
                .coupon-banner { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #2563eb 100%); border: 1px solid rgba(255,255,255,0.15); }
                .copy-coupon-btn { transition: all 0.2s; border-radius: 8px; }
                .copy-coupon-btn:hover { background: #f8faff; transform: scale(1.05); }
            </style>

            <script>
            function copyCoupon(code, btn) {
                navigator.clipboard.writeText(code).then(function() {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                    btn.classList.remove('btn-light');
                    btn.classList.add('btn-success');
                    setTimeout(function() {
                        btn.innerHTML = orig;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-light');
                    }, 2000);
                });
            }
            </script>
        <?php endif; ?>

        <div class="text-center mb-5">
            <h1 class="mb-3 fw-bold">Our Courses</h1>
            <p class="lead text-muted">Master the latest technologies with our industry-aligned curriculum.</p>
        </div>

        <!-- Search and Filter Bar -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="course-filters-box shadow-sm">
                    <form action="/courses" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="search" class="filter-label">Search Courses</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" id="search" class="form-control border-start-0 shadow-none" 
                                    placeholder="e.g. Web Development, Python..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="level" class="filter-label">Skill Level</label>
                            <select name="level" id="level" class="form-select shadow-none">
                                <option value="">All Levels</option>
                                <option value="Beginner" <?php echo $level === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                                <option value="Intermediate" <?php echo $level === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="Advanced" <?php echo $level === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <?php if (!empty($search) || !empty($level)): ?>
                                <a href="/courses" class="btn btn-outline-secondary px-3" title="Clear Filters">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php if (empty($courses)): ?>
                <div class="col-12 text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted opacity-25"></i>
                    </div>
                    <h3 class="fw-bold">No courses found</h3>
                    <p class="text-muted">We couldn't find any courses matching "<strong><?php echo htmlspecialchars($search); ?></strong>". Try different keywords or clear filters.</p>
                    <a href="/courses" class="btn btn-primary mt-3">View All Courses</a>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $index => $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 course-card shadow-sm">
                            <?php if (!empty($course['image'])): ?>
                                    <div class="course-image-wrapper">
                                        <img src="/assets/images/<?php echo $course['image']; ?>" alt="<?php echo $course['name']; ?>"
                                            class="course-card-img">
                                        <?php
                                        $level_text = $course['level'];
                                        $level_class = 'bg-success';
                                        if (strpos($level_text, 'Intermediate') !== false && strpos($level_text, 'Advanced') === false) {
                                            $level_class = 'bg-primary';
                                        } elseif (strpos($level_text, 'Advanced') !== false) {
                                            $level_class = 'bg-danger';
                                        }
                                        ?>
                                        <span class="position-absolute top-0 end-0 m-2 level-badge <?php echo $level_class; ?>">
                                            <?php echo $level_text; ?>
                                        </span>
                                    </div>
                            <?php endif; ?>
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold mb-3"><?php echo $course['name']; ?></h5>
                                <p class="card-text text-muted small mb-4"><?php echo $course['description']; ?></p>
                                <div class="d-flex align-items-center justify-content-between pt-3 border-top gap-2">
                                    <span class="fw-bold fs-6 text-primary"
                                        style="white-space: nowrap;">₹<?php echo number_format($course['price']); ?></span>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2"
                                            style="font-size: 0.75rem;" data-bs-toggle="modal"
                                            data-bs-target="#curriculumModal<?php echo $index; ?>">
                                            <i class="fas fa-list-alt me-1"></i> Curriculum
                                        </button>
                                        <a href="/enroll/<?php echo $course['id']; ?>"
                                            class="btn btn-primary btn-sm py-1 px-3" style="font-size: 0.85rem;">Enroll Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Curriculum Modal -->
                    <div class="modal fade" id="curriculumModal<?php echo $index; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title fw-bold"><?php echo $course['name']; ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="curriculum-timeline">
                                        <?php foreach ($course['curriculum'] as $modIndex => $module): ?>
                                            <div class="curriculum-module mb-4">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="module-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; min-width: 32px;">
                                                        <?php echo $modIndex + 1; ?>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark"><?php echo $module['module']; ?></h6>
                                                </div>
                                                <div class="module-topics ms-5">
                                                    <?php foreach ($module['topics'] as $topic): ?>
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-check-circle text-primary me-2" style="font-size: 0.8rem;"></i>
                                                            <span class="text-muted"><?php echo $topic; ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="/enroll/<?php echo $course['id']; ?>" class="btn btn-primary">Enroll - ₹<?php echo number_format($course['price']); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Combo Programs Section -->
<?php if (!empty($career_tracks)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3"><span class="section-header">Career Track Programs</span></h2>
            <p class="lead text-muted">Bundle courses together and save big!</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($career_tracks as $track): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 combo-card shadow-sm position-relative">
                        <?php if (!empty($track['badge'])): ?>
                            <span class="combo-badge bg-<?php echo $track['badge_color']; ?> text-white"><i class="fas fa-star me-1"></i> <?php echo htmlspecialchars($track['badge']); ?></span>
                        <?php endif; ?>
                        <div class="card-body p-4 <?php echo !empty($track['badge']) ? 'pt-5' : ''; ?>">
                            <h5 class="card-title fw-bold text-center mb-3"><?php echo htmlspecialchars($track['name']); ?></h5>
                            <div class="mb-4">
                                <?php if (!empty($track['courses_list'])): ?>
                                    <?php foreach ($track['courses_list'] as $course_item): ?>
                                        <div class="course-list-item"><i class="fas fa-check-circle text-success me-2"></i><?php echo htmlspecialchars($course_item['name']); ?></div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="text-center mb-3">
                                <?php if ($track['original_price'] > 0 && $track['original_price'] > $track['price']): ?>
                                    <span class="combo-original-price">₹<?php echo number_format($track['original_price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <div class="combo-price"><span class="currency">₹</span><?php echo number_format($track['price']); ?></div>
                                <a href="/enroll?track=<?php echo urlencode($track['slug']); ?>" class="btn btn-primary px-4">Get Bundle</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>