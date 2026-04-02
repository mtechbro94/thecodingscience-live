<?php
// admin/career_track_form.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';



if (!is_admin()) {
    redirect('/');
}

$page_title = "Manage Career Track";
$track = [];
$errors = [];
$success = "";

$track_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $track_id > 0;

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM career_tracks WHERE id = ?");
    $stmt->execute([$track_id]);
    $track = $stmt->fetch();

    if (!$track) {
        set_flash('danger', 'Career track not found.');
        redirect('/admin/career_tracks');
    }
    $page_title = "Edit Track: " . htmlspecialchars($track['name']);
} else {
    $page_title = "Add New Career Track";
}

$stmt = $pdo->query("SELECT id, name, price FROM courses ORDER BY name");
$courses = $stmt->fetchAll();

$selected_courses = [];
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT course_id, sort_order, is_required FROM career_track_courses WHERE track_id = ? ORDER BY sort_order");
    $stmt->execute([$track_id]);
    $selected_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $selected_course_ids = array_column($selected_courses, 'course_id');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please refresh and try again.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $description = $_POST['description'] ?? '';
    $summary = sanitize($_POST['summary'] ?? '');
    $duration = sanitize($_POST['duration'] ?? '');
    $original_price = (float) ($_POST['original_price'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $badge = sanitize($_POST['badge'] ?? '');
    $badge_color = sanitize($_POST['badge_color'] ?? 'primary');
    $outcomes = $_POST['outcomes'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $curriculum = $_POST['curriculum'] ?? '[]';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $selected_course_ids = $_POST['courses'] ?? [];

    if (empty($name)) $errors[] = "Track Name is required";
    
    if (empty($slug)) {
        $slug = generate_slug($name);
    } else {
        $slug = generate_slug($slug);
    }

    if ($is_edit) {
        $stmt = $pdo->prepare("SELECT id FROM career_tracks WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $track_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM career_tracks WHERE slug = ?");
        $stmt->execute([$slug]);
    }
    if ($stmt->fetch()) {
        $errors[] = "Slug already exists. Please use a different slug.";
    }

    if ($price < 0) $errors[] = "Price cannot be negative";

    json_decode($curriculum);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "Invalid Curriculum JSON format";
    }

    $image = $track['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('track_', true) . '.' . $ext;
            $upload_dir = BASE_PATH . '/assets/images/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                $image = $new_filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        } else {
            $errors[] = "Invalid file type. Allowed: " . implode(', ', $allowed);
        }
    }

    if (empty($errors)) {
        try {
            if ($is_edit) {
                $sql = "UPDATE career_tracks SET name=?, slug=?, description=?, summary=?, duration=?, original_price=?, price=?, badge=?, badge_color=?, image=?, outcomes=?, requirements=?, curriculum=?, is_active=?, is_featured=?, sort_order=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $slug, $description, $summary, $duration, $original_price, $price, $badge, $badge_color, $image, $outcomes, $requirements, $curriculum, $is_active, $is_featured, $sort_order, $track_id]);
                
                $stmt = $pdo->prepare("DELETE FROM career_track_courses WHERE track_id = ?");
                $stmt->execute([$track_id]);
            } else {
                $sql = "INSERT INTO career_tracks (name, slug, description, summary, duration, original_price, price, badge, badge_color, image, outcomes, requirements, curriculum, is_active, is_featured, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $slug, $description, $summary, $duration, $original_price, $price, $badge, $badge_color, $image, $outcomes, $requirements, $curriculum, $is_active, $is_featured, $sort_order]);
                $track_id = $pdo->lastInsertId();
            }

            foreach ($selected_course_ids as $index => $course_id) {
                $stmt = $pdo->prepare("INSERT INTO career_track_courses (track_id, course_id, sort_order, is_required) VALUES (?, ?, ?, 1)");
                $stmt->execute([$track_id, $course_id, $index]);
            }

            set_flash('success', 'Career track ' . ($is_edit ? 'updated' : 'created') . ' successfully.');
            redirect('/admin/career_tracks');
        } catch (PDOException $e) {
            error_log("Career track save failed: " . $e->getMessage());
            $errors[] = "Failed to save the career track. Please try again.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $is_edit ? 'Edit Career Track' : 'Add New Career Track'; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/career_tracks" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tracks
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="mb-5">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Track Name *</label>
                        <input type="text" class="form-control" name="name" required
                            value="<?php echo htmlspecialchars($track['name'] ?? ($_POST['name'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug *</label>
                        <input type="text" class="form-control" name="slug" 
                            value="<?php echo htmlspecialchars($track['slug'] ?? ($_POST['slug'] ?? '')); ?>"
                            placeholder="auto-generated-from-name">
                        <small class="text-muted">URL: /career-track/{slug}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Summary (Short Description)</label>
                        <input type="text" class="form-control" name="summary" maxlength="255"
                            value="<?php echo htmlspecialchars($track['summary'] ?? ($_POST['summary'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Description</label>
                        <textarea class="form-control" name="description" rows="6"><?php echo htmlspecialchars($track['description'] ?? ($_POST['description'] ?? '')); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Courses in This Track</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Select Courses</label>
                        <select class="form-select" name="courses[]" multiple size="8">
                            <?php 
                            $selected = $selected_course_ids ?? ($_POST['courses'] ?? []);
                            foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>" <?php echo in_array($course['id'], $selected) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['name']); ?> (₹<?php echo number_format($course['price']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple courses</small>
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
                        <textarea class="form-control font-monospace" name="curriculum" rows="8"><?php echo htmlspecialchars($track['curriculum'] ?? ($_POST['curriculum'] ?? '[]')); ?></textarea>
                        <small class="text-muted">Format: <code>[{"title": "Module Name", "topics": ["Topic 1", "Topic 2"]}]</code></small>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Learning Outcomes & Requirements</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">What You'll Learn (one per line)</label>
                        <textarea class="form-control" name="outcomes" rows="5" placeholder="Build real-world projects&#10;Master industry tools&#10;Get job-ready skills"><?php echo htmlspecialchars($track['outcomes'] ?? ($_POST['outcomes'] ?? '')); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Requirements/Prerequisites (one per line)</label>
                        <textarea class="form-control" name="requirements" rows="4" placeholder="Basic programming knowledge&#10;Computer with internet"><?php echo htmlspecialchars($track['requirements'] ?? ($_POST['requirements'] ?? '')); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Pricing</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Original Price (₹)</label>
                        <input type="number" class="form-control" name="original_price" step="0.01" min="0"
                            value="<?php echo htmlspecialchars($track['original_price'] ?? ($_POST['original_price'] ?? '0')); ?>">
                        <small class="text-muted">Leave blank or 0 for free</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sale Price (₹) *</label>
                        <input type="number" class="form-control" name="price" step="0.01" min="0" required
                            value="<?php echo htmlspecialchars($track['price'] ?? ($_POST['price'] ?? '0')); ?>">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Track Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Duration</label>
                        <input type="text" class="form-control" name="duration" placeholder="e.g. 6 Months"
                            value="<?php echo htmlspecialchars($track['duration'] ?? ($_POST['duration'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Badge Text</label>
                        <input type="text" class="form-control" name="badge" placeholder="e.g. Bestseller, New"
                            value="<?php echo htmlspecialchars($track['badge'] ?? ($_POST['badge'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Badge Color</label>
                        <select class="form-select" name="badge_color">
                            <?php $colors = ['primary', 'success', 'warning', 'danger', 'info', 'secondary']; ?>
                            <?php $selected_color = $track['badge_color'] ?? ($_POST['badge_color'] ?? 'primary'); ?>
                            <?php foreach ($colors as $color): ?>
                                <option value="<?php echo $color; ?>" <?php echo $selected_color === $color ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($color); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="<?php echo $track['sort_order'] ?? ($_POST['sort_order'] ?? '0'); ?>">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                            <?php echo ($track['is_active'] ?? ($_POST['is_active'] ?? 1)) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" 
                            <?php echo ($track['is_featured'] ?? ($_POST['is_featured'] ?? 0)) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Featured</label>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Track Image</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($track['image'])): ?>
                        <img src="<?php echo get_image_url($track['image']); ?>" class="img-fluid rounded mb-3" style="max-height: 200px;">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="image" class="form-label">Upload Image</label>
                        <input class="form-control" type="file" id="image" name="image" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Career Track
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
