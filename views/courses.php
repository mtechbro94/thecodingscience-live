<?php
// views/courses.php

$page_title = "Our Courses";
require_once 'includes/header.php';

// Fetch courses from database
$stmt = $pdo->query("SELECT * FROM courses ORDER BY id ASC");
$courses = $stmt->fetchAll();

// Debug
echo '<!-- Courses found: ' . count($courses) . ' -->';

// Decode curriculum for each course
foreach ($courses as &$course) {
    if (!empty($course['curriculum'])) {
        $course['curriculum'] = json_decode($course['curriculum'], true);
    } else {
        $course['curriculum'] = [];
    }
}
unset($course);

// Career Tracks Data (from database)
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

function getLevelBadgeClass($level) {
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
    padding-top: 56.25%; /* 16:9 aspect ratio */
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
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    border-color: var(--primary-color);
}
.course-card .btn {
    white-space: nowrap;
}
@media (max-width: 400px) {
    .course-card .btn {
        font-size: 0.7rem !important;
        padding: 0.25rem 0.5rem !important;
    }
}
.level-badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
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
}
.combo-card {
    overflow: visible;
}
.combo-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
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
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
}
@media (max-width: 576px) {
    .combo-badge {
        top: -10px;
        padding: 0.4rem 1rem;
        font-size: 0.75rem;
    }
}
.original-price {
    text-decoration: line-through;
    color: #94a3b8;
    font-size: 0.9rem;
}
.combo-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-color);
}
.save-badge {
    background: #dcfce7;
    color: #166534;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.course-list-item {
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
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
.modal-header.bg-primary { background: var(--primary-color) !important; }
.modal-header.bg-success { background: var(--success-color) !important; }
.modal-header.bg-info { background: var(--info-color) !important; }
.modal-header.bg-warning { background: #f59e0b !important; }
.modal-header.bg-danger { background: var(--danger-color) !important; }
.modal-header.bg-dark { background: var(--dark-color) !important; }
</style>

<!-- Individual Courses Section -->
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-3 fw-bold">Our Courses</h1>
            <p class="lead text-muted">Master the latest technologies with our industry-aligned curriculum - from foundation to advanced level with job ready skillset.</p>
        </div>

        <!-- Level Legend -->
        <div class="d-flex justify-content-center gap-3 mb-5 flex-wrap">
            <span class="level-legend-badge bg-success"><i class="fas fa-star me-1"></i> Beginner</span>
            <span class="level-legend-badge bg-primary"><i class="fas fa-layer-group me-1"></i> Intermediate</span>
            <span class="level-legend-badge bg-danger"><i class="fas fa-rocket me-1"></i> Advanced</span>
        </div>

        <div class="row g-4">
            <?php foreach ($courses as $index => $course): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 course-card shadow-sm">
                        <?php if (!empty($course['image'])): ?>
                            <div class="course-image-wrapper">
                                <img src="/assets/images/<?php echo $course['image']; ?>" alt="<?php echo $course['name']; ?>" class="course-card-img">
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
                                <span class="fw-bold fs-6 text-primary" style="white-space: nowrap;">₹<?php echo number_format($course['price']); ?></span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#curriculumModal<?php echo $index; ?>">
                                        <i class="fas fa-list-alt me-1"></i> View Curriculum
                                    </button>
                                    <a href="/enroll?course=<?php echo urlencode($course['name']); ?>" class="btn btn-primary btn-sm py-1 px-3" style="font-size: 0.85rem;">Enroll Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Curriculum Modal -->
                <div class="modal fade" id="curriculumModal<?php echo $index; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-<?php echo $course['color']; ?> text-white">
                                <h5 class="modal-title fw-bold">
                                    <?php echo $course['name']; ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($course['image'])): ?>
                                    <div class="mb-3">
                                        <img src="/assets/images/<?php echo $course['image']; ?>" alt="<?php echo $course['name']; ?>" class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                                    </div>
                                <?php endif; ?>
                                <div class="mb-4">
                                    <span class="badge bg-<?php echo $course['color']; ?>"><?php echo $course['level']; ?></span>
                                    <span class="badge bg-secondary ms-2"><?php echo count($course['curriculum']); ?> Modules</span>
                                </div>
                                
                                <div class="curriculum-timeline">
                                    <?php foreach ($course['curriculum'] as $modIndex => $module): ?>
                                        <div class="curriculum-module mb-4">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="module-number bg-<?php echo $course['color']; ?> text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; min-width: 32px;">
                                                    <?php echo $modIndex + 1; ?>
                                                </div>
                                                <h6 class="fw-bold mb-0"><?php echo $module['module']; ?></h6>
                                            </div>
                                            <div class="module-topics ms-5">
                                                <?php foreach ($module['topics'] as $topic): ?>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-check-circle text-<?php echo $course['color']; ?> me-2" style="font-size: 0.8rem;"></i>
                                                        <span class="text-muted"><?php echo $topic; ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php if ($modIndex < count($course['curriculum']) - 1): ?>
                                            <div class="curriculum-line bg-<?php echo $course['color']; ?>-subtle mx-5 mb-4" style="height: 2px;"></div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <a href="/enroll?course=<?php echo urlencode($course['name']); ?>" class="btn btn-primary">
                                    <i class="fas fa-graduation-cap me-1"></i> Enroll Now - ₹<?php echo number_format($course['price']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Combo Programs Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">
                <span class="section-header">Career Track Programs</span>
            </h2>
            <p class="lead text-muted">Bundle courses together and save big! Our recommended learning pathways for your career success.</p>
            <span class="badge bg-warning text-dark fs-6 mt-2"><i class="fas fa-star me-1"></i> Best Value Programs</span>
        </div>

        <div class="row g-4 justify-content-center">
            <?php if (empty($career_tracks)): ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">Career track programs coming soon!</p>
                </div>
            <?php else: ?>
            <?php foreach ($career_tracks as $track): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 combo-card shadow-sm position-relative">
                        <?php if (!empty($track['badge'])): ?>
                        <span class="combo-badge bg-<?php echo $track['badge_color']; ?> text-white">
                            <i class="fas fa-star me-1"></i> <?php echo htmlspecialchars($track['badge']); ?>
                        </span>
                        <?php endif; ?>
                        <div class="card-body p-4 <?php echo !empty($track['badge']) ? 'pt-5' : ''; ?>">
                            <h5 class="card-title fw-bold text-center mb-3"><?php echo htmlspecialchars($track['name']); ?></h5>
                            
                            <div class="mb-4">
                                <p class="text-muted small mb-2 fw-semibold">Includes:</p>
                                <?php if (!empty($track['courses_list'])): ?>
                                    <?php foreach ($track['courses_list'] as $course_item): ?>
                                        <div class="course-list-item">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <?php echo htmlspecialchars($course_item['name']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <p class="card-text text-muted small text-center mb-3"><?php echo htmlspecialchars($track['summary'] ?? ''); ?></p>
                            
                            <div class="text-center mb-3">
                                <?php if ($track['original_price'] > 0): ?>
                                    <span class="original-price">₹<?php echo number_format($track['original_price']); ?></span>
                                    <span class="save-badge ms-2">Save ₹<?php echo number_format($track['original_price'] - $track['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top gap-3">
                                <span class="combo-price">₹<?php echo number_format($track['price']); ?></span>
                                <a href="/enroll?track=<?php echo urlencode($track['slug']); ?>" class="btn btn-<?php echo $track['badge_color']; ?> px-4" style="font-size: 0.9rem; white-space: nowrap;">
                                    Get This Bundle
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Why Choose Combos -->
        <div class="mt-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="bg-primary-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-percentage text-primary fs-4"></i>
                        </div>
                        <h6 class="fw-bold">Save Upto 40%</h6>
                        <p class="text-muted small mb-0">Get significant discounts when you bundle courses together</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="bg-success-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-certificate text-success fs-4"></i>
                        </div>
                        <h6 class="fw-bold">Complete Certification</h6>
                        <p class="text-muted small mb-0">Get certificates for each course upon completion</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="bg-warning-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-infinity text-warning fs-4"></i>
                        </div>
                        <h6 class="fw-bold">Lifetime Access</h6>
                        <p class="text-muted small mb-0">Access course materials anytime, forever</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
