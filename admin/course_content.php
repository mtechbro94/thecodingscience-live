<?php
// admin/course_content.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_admin()) {
    redirect('/');
}

$course_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($course_id <= 0) {
    set_flash('danger', 'Invalid Course ID.');
    redirect('/admin/courses');
}

// Fetch Course Data
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    set_flash('danger', 'Course not found.');
    redirect('/admin/courses');
}

$page_title = "Manage Content: " . htmlspecialchars($course['name']);

// Handle Resource Upload
if (isset($_POST['add_resource'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid request.');
        redirect('/admin/course-content?id=' . $course_id);
    }

    $title = sanitize($_POST['resource_title'] ?? '');
    
    if (empty($title)) {
        set_flash('danger', 'Resource title is required.');
    } elseif (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['pdf', 'ppt', 'pptx', 'zip', 'rar', 'doc', 'docx', 'txt', 'csv', 'xlsx', 'png', 'jpg'];
        $filename = $_FILES['resource_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = 'res_' . uniqid() . '.' . $ext;
            $upload_dir = BASE_PATH . '/assets/uploads/resources/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $upload_dir . $new_filename)) {
                $stmt = $pdo->prepare("INSERT INTO course_resources (course_id, title, file_path, file_type) VALUES (?, ?, ?, ?)");
                $stmt->execute([$course_id, $title, $new_filename, $ext]);
                set_flash('success', 'Resource uploaded successfully.');
            } else {
                set_flash('danger', 'Failed to upload resource.');
            }
        } else {
            set_flash('danger', 'Invalid file type.');
        }
    } else {
        set_flash('danger', 'Please select a file to upload.');
    }
    redirect('/admin/course-content?id=' . $course_id);
}

// Handle Recording Add
if (isset($_POST['add_recording'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid request.');
        redirect('/admin/course-content?id=' . $course_id);
    }

    $title = sanitize($_POST['recording_title'] ?? '');
    $url = sanitize($_POST['recording_url'] ?? '');

    if (empty($title) || empty($url)) {
        set_flash('danger', 'All recording fields are required.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO course_recordings (course_id, title, recording_url) VALUES (?, ?, ?)");
        $stmt->execute([$course_id, $title, $url]);
        set_flash('success', 'Recording added successfully.');
    }
    redirect('/admin/course-content?id=' . $course_id);
}

// Handle Delete Resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_resource'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid request.');
        redirect('/admin/course-content?id=' . $course_id);
    }

    $res_id = (int)$_POST['delete_resource'];
    $stmt = $pdo->prepare("SELECT file_path FROM course_resources WHERE id = ? AND course_id = ?");
    $stmt->execute([$res_id, $course_id]);
    $res = $stmt->fetch();
    
    if ($res) {
        $file = BASE_PATH . '/assets/uploads/resources/' . $res['file_path'];
        if (file_exists($file)) unlink($file);
        
        $pdo->prepare("DELETE FROM course_resources WHERE id = ?")->execute([$res_id]);
        set_flash('success', 'Resource deleted.');
    }
    redirect('/admin/course-content?id=' . $course_id);
}

// Handle Delete Recording
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_recording'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid request.');
        redirect('/admin/course-content?id=' . $course_id);
    }

    $rec_id = (int)$_POST['delete_recording'];
    $pdo->prepare("DELETE FROM course_recordings WHERE id = ? AND course_id = ?")->execute([$rec_id, $course_id]);
    set_flash('success', 'Recording deleted.');
    redirect('/admin/course-content?id=' . $course_id);
}

// Fetch Existing Content
$stmt = $pdo->prepare("SELECT * FROM course_recordings WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$course_id]);
$recordings = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM course_resources WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$course_id]);
$resources = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Course Content</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/courses" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0 bg-light p-3">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white p-3 rounded me-3">
                    <i class="fas fa-graduation-cap fa-2x"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-bold"><?php echo htmlspecialchars($course['name']); ?></h4>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($course['duration']); ?> &bull; <?php echo htmlspecialchars($course['batch_timing'] ?: 'Timing not set'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recordings Management -->
    <div class="col-lg-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-video me-1"></i> Class Recordings</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordingModal text-white">
                    <i class="fas fa-plus"></i> Add Recording
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recordings)): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted small">No recordings added yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recordings as $rec): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($rec['title']); ?></div>
                                            <small class="text-muted"><?php echo date('d M Y', strtotime($rec['created_at'])); ?></small>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?php echo $rec['recording_url']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this recording?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                <input type="hidden" name="delete_recording" value="<?php echo $rec['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Resources Management -->
    <div class="col-lg-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt me-1"></i> Course Resources</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal text-white">
                    <i class="fas fa-upload"></i> Upload Resource
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Resource Name</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($resources)): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted small">No resources uploaded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($resources as $res): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($res['title']); ?></div>
                                            <small class="text-muted"><span class="badge bg-light text-dark border"><?php echo strtoupper($res['file_type']); ?></span> &bull; <?php echo date('d M Y', strtotime($res['created_at'])); ?></small>
                                        </td>
                                        <td class="text-end">
                                            <a href="/assets/uploads/resources/<?php echo $res['file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this resource?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                <input type="hidden" name="delete_resource" value="<?php echo $res['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Recording Modal -->
<div class="modal fade" id="addRecordingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="modal-header">
                <h5 class="modal-title">Add Class Recording</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Recording Title</label>
                    <input type="text" name="recording_title" class="form-control" placeholder="e.g. Session 1: Web Fundamentals" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Recording Link (Youtube/Vimeo/Drive)</label>
                    <input type="url" name="recording_url" class="form-control" placeholder="https://..." required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="add_recording" class="btn btn-primary">Save Recording</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Resource Modal -->
<div class="modal fade" id="addResourceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="modal-header">
                <h5 class="modal-title">Upload Resource</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Resource Title</label>
                    <input type="text" name="resource_title" class="form-control" placeholder="e.g. Intro to PHP PDF" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select File</label>
                    <input type="file" name="resource_file" class="form-control" required>
                    <small class="text-muted">Allowed types: PDF, PPT, ZIP, DOCX, TXT, etc.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="add_resource" class="btn btn-primary">Upload Now</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
