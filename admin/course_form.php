<?php
// admin/course_form.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_admin()) {
    redirect('/');
}

$page_title = "Manage Course";
$course = [];
$errors = [];
$success = "";

// Get Course ID if editing
$course_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $course_id > 0;

// Fetch Course Data if editing
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    if (!$course) {
        set_flash('danger', 'Course not found.');
        redirect('/admin/courses');
    }
    $page_title = "Edit Course: " . htmlspecialchars($course['name']);
} else {
    $page_title = "Add New Course";
}

// Fetch Trainers for dropdown
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'trainer' AND is_approved = 1 ORDER BY name");
$trainers = $stmt->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = $_POST['description'] ?? ''; // Allow HTML
    $summary = sanitize($_POST['summary'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $duration = sanitize($_POST['duration'] ?? '');
    $batch_timing = sanitize($_POST['batch_timing'] ?? '');
    $live_link = sanitize($_POST['live_link'] ?? '');
    $level = sanitize($_POST['level'] ?? '');
    $trainer_id = !empty($_POST['trainer_id']) ? (int) $_POST['trainer_id'] : null;
    $curriculum = $_POST['curriculum'] ?? '[]'; // JSON string
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Validation
    if (empty($name))
        $errors[] = "Course Name is required";
    if ($price < 0)
        $errors[] = "Price cannot be negative";

    // Validate JSON
    json_decode($curriculum);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "Invalid Curriculum JSON format";
    }

    // Handle File Upload
    $image = $course['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('course_', true) . '.' . $ext;
            $upload_dir = BASE_PATH . '/assets/images/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                $image = $new_filename;
            } else {
                $errors[] = "Failed to upload image. Error: " . error_get_last()['message'];
            }
        } else {
            $errors[] = "Invalid file type. Allowed: " . implode(', ', $allowed);
        }
    }

    if (empty($errors)) {
        try {
            if ($is_edit) {
                // Update
                $sql = "UPDATE courses SET name = ?, description = ?, summary = ?, price = ?, duration = ?, batch_timing = ?, live_link = ?, level = ?, trainer_id = ?, image = ?, curriculum = ?, is_featured = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $summary, $price, $duration, $batch_timing, $live_link, $level, $trainer_id, $image, $curriculum, $is_featured, $course_id]);
                set_flash('success', 'Course updated successfully.');
            } else {
                // Insert
                $sql = "INSERT INTO courses (name, description, summary, price, duration, batch_timing, live_link, level, trainer_id, image, curriculum, is_featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $summary, $price, $duration, $batch_timing, $live_link, $level, $trainer_id, $image, $curriculum, $is_featured]);
                set_flash('success', 'Course created successfully.');
            }
            redirect('/admin/courses');
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <?php echo $is_edit ? 'Edit Course' : 'Add New Course'; ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/courses" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li>
                    <?php echo $error; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="mb-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Course Name *</label>
                        <input type="text" class="form-control" name="name" required
                            value="<?php echo htmlspecialchars($course['name'] ?? ($_POST['name'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Summary (Short Description)</label>
                        <input type="text" class="form-control" name="summary" maxlength="200"
                            value="<?php echo htmlspecialchars($course['summary'] ?? ($_POST['summary'] ?? '')); ?>">
                        <small class="text-muted">Displayed on course cards. Max 200 chars.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Description</label>
                        <textarea class="form-control" name="description"
                            rows="6"><?php echo htmlspecialchars($course['description'] ?? ($_POST['description'] ?? '')); ?></textarea>
                        <small class="text-muted">HTML tags allowed.</small>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Curriculum</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Modules (JSON Format)</label>
                        <textarea class="form-control font-monospace" name="curriculum"
                            rows="10"><?php echo htmlspecialchars($course['curriculum'] ?? ($_POST['curriculum'] ?? '[{"title": "Module 1: Introduction", "topics": ["Overview", "Setup"]}, {"title": "Module 2: Advanced Topics", "topics": ["Performance", "Security"]}]')); ?></textarea>
                        <small class="text-muted">Format:
                            <code>[{"title": "Module Name", "topics": ["Topic 1", "Topic 2"]}]</code> or a simple list
                            <code>["Topic 1", "Topic 2"]</code></small>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Course Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Price (₹) *</label>
                        <input type="number" class="form-control" name="price" step="0.01" min="0" required
                            value="<?php echo htmlspecialchars($course['price'] ?? ($_POST['price'] ?? '0')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Duration</label>
                        <input type="text" class="form-control" name="duration" placeholder="e.g. 8 Weeks"
                            value="<?php echo htmlspecialchars($course['duration'] ?? ($_POST['duration'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Batch Timing</label>
                        <input type="text" class="form-control" name="batch_timing" placeholder="e.g. Mon-Fri, 7 PM - 8 PM"
                            value="<?php echo htmlspecialchars($course['batch_timing'] ?? ($_POST['batch_timing'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Live Class Link (Zoom/Meet)</label>
                        <input type="url" class="form-control" name="live_link" placeholder="https://zoom.us/j/..."
                            value="<?php echo htmlspecialchars($course['live_link'] ?? ($_POST['live_link'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <select class="form-select" name="level">
                            <?php $lvl = $course['level'] ?? ($_POST['level'] ?? 'Beginner'); ?>
                            <option value="Beginner" <?php echo $lvl === 'Beginner' ? 'selected' : ''; ?>>Beginner
                            </option>
                            <option value="Intermediate" <?php echo $lvl === 'Intermediate' ? 'selected' : ''; ?>>
                                Intermediate</option>
                            <option value="Advanced" <?php echo $lvl === 'Advanced' ? 'selected' : ''; ?>>Advanced
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned Trainer</label>
                        <select class="form-select" name="trainer_id">
                            <option value="">-- Select Trainer --</option>
                            <?php
                            $tid = $course['trainer_id'] ?? ($_POST['trainer_id'] ?? '');
                            foreach ($trainers as $trainer):
                                ?>
                                <option value="<?php echo $trainer['id']; ?>" <?php echo $tid == $trainer['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($trainer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3 border-top pt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?php echo ($course['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="is_featured">Featured Course</label>
                        </div>
                        <small class="text-muted">Featured courses appear prominently on the home page.</small>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Course Image</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($course['image'])): ?>
                        <img src="<?php echo get_image_url($course['image']); ?>" class="img-fluid rounded mb-3"
                            style="max-height: 200px;">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="image" class="form-label">Upload Image</label>
                        <input class="form-control" type="file" id="image" name="image" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Course
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>