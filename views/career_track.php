<?php
// views/career_track.php

$track_slug = isset($track_slug) ? $track_slug : '';

if (empty($track_slug)) {
    redirect('/courses');
}

$stmt = $pdo->prepare("SELECT * FROM career_tracks WHERE slug = ? AND is_active = 1");
$stmt->execute([$track_slug]);
$track = $stmt->fetch();

if (!$track) {
    redirect('/courses');
}

$page_title = $track['name'];

$stmt = $pdo->prepare("
    SELECT c.* 
    FROM courses c 
    JOIN career_track_courses cc ON c.id = cc.course_id 
    WHERE cc.track_id = ? 
    ORDER BY cc.sort_order
");
$stmt->execute([$track['id']]);
$courses = $stmt->fetchAll();

$curriculum = !empty($track['curriculum']) ? json_decode($track['curriculum'], true) : [];
$outcomes = !empty($track['outcomes']) ? explode("\n", trim($track['outcomes'])) : [];
$requirements = !empty($track['requirements']) ? explode("\n", trim($track['requirements'])) : [];

require_once 'includes/header.php';
?>

<style>
.original-price {
    text-decoration: line-through;
    color: #94a3b8;
    font-size: 1.1rem;
}
.save-badge {
    background: #dcfce7;
    color: #166534;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}
</style>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($track['badge'])): ?>
                    <span class="badge bg-<?php echo $track['badge_color']; ?> mb-3">
                        <i class="fas fa-star me-1"></i> <?php echo htmlspecialchars($track['badge']); ?>
                    </span>
                <?php endif; ?>

                <h1 class="mb-3"><?php echo htmlspecialchars($track['name']); ?></h1>
                
                <?php if (!empty($track['summary'])): ?>
                    <p class="lead text-muted mb-4"><?php echo htmlspecialchars($track['summary']); ?></p>
                <?php endif; ?>

                <div class="mb-4">
                    <?php if (!empty($track['duration'])): ?>
                        <span class="badge bg-info me-2"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($track['duration']); ?></span>
                    <?php endif; ?>
                    <span class="badge bg-success"><i class="fas fa-book me-1"></i> <?php echo count($courses); ?> Courses</span>
                </div>

                <?php if (!empty($track['image'])): ?>
                    <img src="<?php echo get_image_url($track['image']); ?>" alt="<?php echo $track['name']; ?>"
                        class="img-fluid rounded mb-4 shadow" loading="lazy">
                <?php endif; ?>

                <?php if (!empty($track['description'])): ?>
                    <div class="mb-4">
                        <h3><strong>About This Career Track</strong></h3>
                        <div style="line-height: 1.8; font-size: 1rem; color: #333;">
                            <?php echo nl2br(htmlspecialchars($track['description'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($outcomes)): ?>
                    <div class="mb-4">
                        <h4 class="mb-3"><strong>What You'll Learn</strong></h4>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($outcomes as $outcome): ?>
                                <?php if (trim($outcome)): ?>
                                    <li class="list-group-item bg-transparent">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <?php echo htmlspecialchars(trim($outcome)); ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($requirements)): ?>
                    <div class="mb-4">
                        <h4 class="mb-3"><strong>Requirements</strong></h4>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($requirements as $req): ?>
                                <?php if (trim($req)): ?>
                                    <li class="list-group-item bg-transparent">
                                        <i class="fas fa-info-circle text-primary me-2"></i>
                                        <?php echo htmlspecialchars(trim($req)); ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($curriculum)): ?>
                    <div class="mb-4">
                        <h4 class="mb-3"><strong>Curriculum</strong></h4>
                        <div class="accordion" id="curriculumAccordion">
                            <?php foreach ($curriculum as $index => $module): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#module<?php echo $index; ?>">
                                            <strong><?php echo htmlspecialchars($module['title'] ?? $module['module'] ?? 'Module ' . ($index + 1)); ?></strong>
                                        </button>
                                    </h2>
                                    <div id="module<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>">
                                        <div class="accordion-body">
                                            <?php if (!empty($module['topics'])): ?>
                                                <ul class="mb-0">
                                                    <?php foreach ($module['topics'] as $topic): ?>
                                                        <li class="mb-1"><?php echo htmlspecialchars($topic); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <h4 class="mb-3"><strong>Courses Included</strong></h4>
                    <div class="row g-3">
                        <?php foreach ($courses as $course): ?>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($course['name']); ?></h6>
                                        <p class="card-text small text-muted mb-2"><?php echo substr($course['summary'] ?? '', 0, 80); ?>...</p>
                                        <span class="badge bg-secondary">₹<?php echo number_format($course['price']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <?php if ($track['original_price'] > 0): ?>
                                <span class="original-price">₹<?php echo number_format($track['original_price']); ?></span>
                                <span class="save-badge ms-2">Save ₹<?php echo number_format($track['original_price'] - $track['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <h2 class="text-center mb-4">
                            <strong>₹<?php echo number_format($track['price']); ?></strong>
                        </h2>

                        <div class="d-grid gap-2">
                            <a href="/enroll?track=<?php echo urlencode($track['slug']); ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-graduation-cap me-2"></i> Enroll Now
                            </a>
                        </div>

                        <div class="mt-4">
                            <p class="small text-muted mb-2"><i class="fas fa-check text-success me-2"></i>Lifetime Access</p>
                            <p class="small text-muted mb-2"><i class="fas fa-check text-success me-2"></i>Certificate of Completion</p>
                            <p class="small text-muted mb-0"><i class="fas fa-check text-success me-2"></i>Project-Based Learning</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
