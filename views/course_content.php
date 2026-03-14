<?php
// views/course_content.php

if (!is_logged_in()) {
    set_flash('danger', 'Please login to access course content.');
    redirect('/login');
}

$user = current_user();
$course_id = isset($id) ? (int)$id : 0;

if ($course_id <= 0) {
    set_flash('danger', 'Invalid course access.');
    redirect('/dashboard');
}

// Check Enrollment (only completed payments get access)
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'completed'");
$stmt->execute([$user['id'], $course_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    set_flash('warning', 'You must be enrolled in this course to view its content.');
    redirect('/dashboard');
}

// Fetch Course Data
$stmt = $pdo->prepare("SELECT c.*, u.name as trainer_name FROM courses c LEFT JOIN users u ON c.trainer_id = u.id WHERE c.id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    set_flash('danger', 'Course details not found.');
    redirect('/dashboard');
}

// Fetch Recordings
$stmt = $pdo->prepare("SELECT * FROM course_recordings WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$course_id]);
$recordings = $stmt->fetchAll();

// Fetch Resources
$stmt = $pdo->prepare("SELECT * FROM course_resources WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$course_id]);
$resources = $stmt->fetchAll();

$page_title = $course['name'] . " - Course Dashboard";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <!-- Dashboard Header -->
        <div class="row align-items-center mb-5">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($course['name']); ?></li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-0"><?php echo htmlspecialchars($course['name']); ?></h1>
                <p class="text-muted lead mt-2 mb-0">Learn from the best and master your skills.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/dashboard" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to My Courses
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Course Info & Live Class -->
            <div class="col-lg-8">
                <!-- Section 1: Course Info -->
                <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i> Course Information</h5>
                    </div>
                    <div class="card-body bg-light-subtle">
                        <div class="row g-4">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-subtle text-primary p-3 rounded-3 me-3">
                                        <i class="fas fa-user-tie fa-lg"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block uppercase small fw-bold">Instructor</small>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($course['trainer_name'] ?: 'Expert Trainer'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info-subtle text-info p-3 rounded-3 me-3">
                                        <i class="fas fa-clock fa-lg"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block uppercase small fw-bold">Duration</small>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($course['duration']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning-subtle text-warning p-2 rounded-2 me-3">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block small">Current Batch Timing</small>
                                            <span class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($course['batch_timing'] ?: 'Timing details coming soon'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Join Live Class -->
                <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
                    <div class="card-body p-4 p-lg-5 text-center">
                        <div class="mb-4">
                            <i class="fas fa-chalkboard-teacher fa-4x opacity-50"></i>
                        </div>
                        <h2 class="fw-bold mb-3">Join Your Live Class</h2>
                        <p class="lead mb-4 opacity-75">Connect with your instructor and classmates in real-time. Make sure to join 5 minutes before the start time.</p>
                        
                        <?php if ($course['live_link']): ?>
                            <a href="<?php echo $course['live_link']; ?>" target="_blank" class="btn btn-light btn-lg px-5 fw-bold shadow-sm">
                                <i class="fas fa-video me-2"></i> Join Live Meeting Now
                            </a>
                        <?php else: ?>
                            <div class="alert alert-light border-0 py-3 mb-0">
                                <i class="fas fa-info-circle me-1 text-primary"></i> 
                                The meeting link has not been set yet. Please check back closer to your class time.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section 3: Class Recordings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-play-circle me-2 text-primary"></i> Session Recordings</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($recordings)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-film fa-3x text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted">No session recordings available yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recordings as $rec): ?>
                                    <div class="list-group-item p-4 border-start border-4 border-primary-subtle">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                            <div>
                                                <h6 class="fw-bold mb-1 fs-5"><?php echo htmlspecialchars($rec['title']); ?></h6>
                                                <div class="d-flex gap-3 small text-muted">
                                                    <span><i class="fas fa-calendar-day me-1"></i> <?php echo date('d M Y', strtotime($rec['created_at'])); ?></span>
                                                    <span><i class="fas fa-check-double me-1 text-success"></i> Recorded Session</span>
                                                </div>
                                            </div>
                                            <a href="<?php echo $rec['recording_url']; ?>" target="_blank" class="btn btn-primary d-flex align-items-center">
                                                <i class="fas fa-play me-2"></i> Watch Now
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Resources -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 position-sticky" style="top: 100px;">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-folder-open me-2 text-primary"></i> Study Materials</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($resources)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-copy fa-3x text-muted opacity-25 mb-3"></i>
                                <p class="text-muted small">Resources will be uploaded here by the admin.</p>
                            </div>
                        <?php else: ?>
                            <div class="d-grid gap-3">
                                <?php foreach ($resources as $res): ?>
                                    <?php 
                                    $icon = 'fa-file-alt';
                                    $color = 'primary';
                                    if (in_array($res['file_type'], ['pdf'])) { $icon = 'fa-file-pdf'; $color = 'danger'; }
                                    elseif (in_array($res['file_type'], ['ppt', 'pptx'])) { $icon = 'fa-file-powerpoint'; $color = 'warning'; }
                                    elseif (in_array($res['file_type'], ['zip', 'rar'])) { $icon = 'fa-file-archive'; $color = 'secondary'; }
                                    ?>
                                    <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between hover-shadow transition-all bg-white">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-<?php echo $color; ?>-subtle text-<?php echo $color; ?> p-2 rounded me-3">
                                                <i class="fas <?php echo $icon; ?> fa-lg"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold small text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($res['title']); ?></div>
                                                <small class="text-muted"><?php echo strtoupper($res['file_type']); ?> file</small>
                                            </div>
                                        </div>
                                        <a href="/assets/uploads/resources/<?php echo $res['file_path']; ?>" download class="btn btn-link text-decoration-none p-2">
                                            <i class="fas fa-download text-muted"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-light border-0 py-3 text-center">
                        <p class="text-muted small mb-0">Total Resources: <strong><?php echo count($resources); ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.bg-light-subtle { background-color: #f8fafc !important; }
.bg-primary-subtle { background-color: rgba(99, 102, 241, 0.1) !important; color: #6366f1 !important; }
.bg-info-subtle { background-color: rgba(6, 182, 212, 0.1) !important; color: #06b6d4 !important; }
.bg-warning-subtle { background-color: rgba(245, 158, 11, 0.1) !important; color: #f59e0b !important; }
.bg-danger-subtle { background-color: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; }

.hover-shadow:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.transition-all { transition: all 0.3s ease; }

[data-theme="dark"] .bg-light-subtle { background-color: #1e293b !important; }
[data-theme="dark"] .card { background-color: #1e293b; color: #cbd5e1; border-color: #334155; }
[data-theme="dark"] .list-group-item { background-color: #1e293b; color: #cbd5e1; border-color: #334155; }
[data-theme="dark"] .breadcrumb-item.active { color: #94a3b8; }
[data-theme="dark"] h1, [data-theme="dark"] h2, [data-theme="dark"] h3, [data-theme="dark"] h4, [data-theme="dark"] h5, [data-theme="dark"] h6 { color: #f1f5f9 !important; }
[data-theme="dark"] .text-dark { color: #f1f5f9 !important; }
[data-theme="dark"] .text-muted { color: #94a3b8 !important; }
[data-theme="dark"] .border { border-color: #334155 !important; }
</style>

<?php require_once 'includes/footer.php'; ?>
