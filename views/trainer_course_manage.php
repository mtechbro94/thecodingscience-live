<?php
// views/trainer_course_manage.php

if (!is_logged_in()) {
    set_flash('danger', 'Please login to access this area.');
    redirect('/login');
}

$user = current_user();
$course_id = isset($id) ? (int)$id : 0;

if ($course_id <= 0) {
    set_flash('danger', 'Invalid course ID.');
    redirect('/trainer-dashboard');
}

// Fetch Course Data & Verify Authorization
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    set_flash('danger', 'Course not found.');
    redirect('/trainer-dashboard');
}

// Security: Check if trainer is assigned to this course (unless admin)
if ($user['role'] !== 'admin' && $course['trainer_id'] != $user['id']) {
    set_flash('danger', 'You are not authorized to manage this course.');
    redirect('/trainer-dashboard');
}

// Fetch Recordings
$stmt = $pdo->prepare("SELECT * FROM course_recordings WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$course_id]);
$recordings = $stmt->fetchAll();

// Fetch Resources
$stmt = $pdo->prepare("SELECT * FROM course_resources WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$course_id]);
$resources = $stmt->fetchAll();

$page_title = "Manage Class: " . htmlspecialchars($course['name']);
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="/trainer-dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Manage Class</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0">Manage Live Session: <span class="text-primary"><?php echo htmlspecialchars($course['name']); ?></span></h2>
            </div>
            <a href="/trainer-dashboard" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <?php $flash = get_flash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column: Live Details -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-video me-2 text-primary"></i> Live Session Settings</h5>
                    </div>
                    <div class="card-body">
                        <form action="/trainer-actions" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="update_live_settings">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Live Class Link (Zoom/Meet/etc.)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-link"></i></span>
                                    <input type="url" name="live_link" class="form-control" placeholder="https://zoom.us/j/..." 
                                           value="<?php echo htmlspecialchars($course['live_link'] ?? ''); ?>">
                                </div>
                                <small class="text-muted">This link will be visible to all enrolled students in their dashboard.</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Batch Timing Description</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-clock"></i></span>
                                    <input type="text" name="batch_timing" class="form-control" placeholder="e.g. Mon-Fri, 7:00 PM - 8:30 PM" 
                                           value="<?php echo htmlspecialchars($course['batch_timing'] ?? ''); ?>">
                                </div>
                                <small class="text-muted">Clearly mention the days and time of the class.</small>
                            </div>

                            <div class="d-grid shadow-sm">
                                <button type="submit" class="btn btn-primary fw-bold py-2">
                                    <i class="fas fa-save me-2"></i> Update Live Settings
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-light-subtle py-3">
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-info-circle me-2"></i>
                            Changes are reflected instantly for students.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Recordings and Resources -->
            <div class="col-lg-7">
                <!-- Recordings -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-play-circle me-2 text-danger"></i> Class Recordings</h5>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addRecordingModal">
                            <i class="fas fa-plus me-1"></i> Add Recording
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recordings)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-film fa-3x text-muted opacity-25 mb-3"></i>
                                <p class="text-muted mb-0">No recordings uploaded yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <tbody>
                                        <?php foreach ($recordings as $rec): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold"><?php echo htmlspecialchars($rec['title']); ?></div>
                                                    <small class="text-muted"><?php echo date('d M Y', strtotime($rec['created_at'])); ?></small>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?php echo $rec['recording_url']; ?>" target="_blank" class="btn btn-outline-primary">Watch</a>
                                                        <form action="/trainer-actions" method="POST" class="d-inline"
                                                           onsubmit="return confirm('Delete this recording?')">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                            <input type="hidden" name="action" value="delete_recording">
                                                            <input type="hidden" name="id" value="<?php echo $rec['id']; ?>">
                                                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                                            <button type="submit" class="btn btn-outline-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resources -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-file-download me-2 text-info"></i> Study Materials</h5>
                        <button type="button" class="btn btn-info text-white btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                            <i class="fas fa-upload me-1"></i> Upload Resource
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($resources)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-folder fa-3x text-muted opacity-25 mb-3"></i>
                                <p class="text-muted mb-0">No study materials shared yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <tbody>
                                        <?php foreach ($resources as $res): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold"><?php echo htmlspecialchars($res['title']); ?></div>
                                                    <small class="text-muted"><?php echo strtoupper($res['file_type']); ?> · <?php echo date('d M Y', strtotime($res['created_at'])); ?></small>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="btn-group btn-group-sm">
                                                        <form action="/trainer-actions" method="POST" class="d-inline"
                                                           onsubmit="return confirm('Delete this resource?')">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                            <input type="hidden" name="action" value="delete_resource">
                                                            <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                                            <button type="submit" class="btn btn-outline-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Recording Modal -->
<div class="modal fade" id="addRecordingModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/trainer-actions" method="POST" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="action" value="add_recording">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <div class="modal-header">
                <h5 class="modal-title">Add Session Recording</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Video Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Session 1: Introduction to PHP" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Recording URL (YouTube/Vimeo/Drive)</label>
                    <input type="url" name="recording_url" class="form-control" placeholder="https://..." required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Recording</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Resource Modal -->
<div class="modal fade" id="addResourceModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/trainer-actions" method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="action" value="add_resource">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <div class="modal-header">
                <h5 class="modal-title">Upload Study Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Resource Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Week 1 Lecture Slides" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select File (PDF, ZIP, DOCX)</label>
                    <input type="file" name="resource_file" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info text-white">Upload File</button>
            </div>
        </form>
    </div>
</div>

<style>
.bg-light-subtle { background-color: #f8fafc !important; }
.card { border-radius: 12px; }
.breadcrumb-item a { text-decoration: none; color: #6366f1; }
</style>

<?php require_once 'includes/footer.php'; ?>
