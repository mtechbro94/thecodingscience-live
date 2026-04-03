<?php
// admin/success_story_form.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_admin()) {
    redirect('/');
}

function success_story_image_full_path($relative_path)
{
    if (empty($relative_path)) {
        return null;
    }

    $normalized = ltrim(str_replace('\\', '/', $relative_path), '/');
    if (strpos($normalized, 'success-stories/') !== 0) {
        return null;
    }

    return BASE_PATH . '/assets/images/' . $normalized;
}

$page_title = "Manage Success Story";
$story = [];
$errors = [];
$has_photo_path = table_has_column('success_stories', 'photo_path');

// Get Story ID if editing
$story_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $story_id > 0;

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM success_stories WHERE id = ?");
    $stmt->execute([$story_id]);
    $story = $stmt->fetch();

    if (!$story) {
        set_flash('danger', 'Success story not found.');
        redirect('/admin/success_stories');
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please refresh and try again.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $rating = (int) ($_POST['rating'] ?? 5);
    $avatar_bg = sanitize($_POST['avatar_bg'] ?? 'bg-primary');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $remove_photo = isset($_POST['remove_photo']);
    $photo_path = $story['photo_path'] ?? null;

    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($title)) $errors[] = "Title is required";
    if (empty($content)) $errors[] = "Content is required";
    if ($rating < 1 || $rating > 5) $errors[] = "Rating must be between 1 and 5";

    if ($has_photo_path && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_error = $_FILES['photo']['error'];
        if ($file_error !== UPLOAD_ERR_OK) {
            $errors[] = "Photo upload failed. Please try again.";
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Photo is too large. Maximum size is 2MB.";
        } else {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed, true)) {
                $errors[] = "Invalid photo type. Allowed: jpg, jpeg, png, webp.";
            } else {
                $upload_dir = BASE_PATH . '/assets/images/success-stories/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $new_filename = 'success_story_' . ($story_id ?: 'new') . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $new_filename)) {
                    $old_file = success_story_image_full_path($photo_path);
                    if ($old_file && file_exists($old_file)) {
                        @unlink($old_file);
                    }
                    $photo_path = 'success-stories/' . $new_filename;
                } else {
                    $errors[] = "Failed to upload photo. Check directory permissions.";
                }
            }
        }
    } elseif ($has_photo_path && $remove_photo && !empty($photo_path)) {
        $old_file = success_story_image_full_path($photo_path);
        if ($old_file && file_exists($old_file)) {
            @unlink($old_file);
        }
        $photo_path = null;
    }

    if (empty($errors)) {
        try {
            if ($is_edit) {
                if ($has_photo_path) {
                    $sql = "UPDATE success_stories SET name = ?, title = ?, content = ?, rating = ?, photo_path = ?, avatar_bg = ?, is_active = ?, sort_order = ? WHERE id = ?";
                    $params = [$name, $title, $content, $rating, $photo_path, $avatar_bg, $is_active, $sort_order, $story_id];
                } else {
                    $sql = "UPDATE success_stories SET name = ?, title = ?, content = ?, rating = ?, avatar_bg = ?, is_active = ?, sort_order = ? WHERE id = ?";
                    $params = [$name, $title, $content, $rating, $avatar_bg, $is_active, $sort_order, $story_id];
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                set_flash('success', 'Success story updated successfully.');
            } else {
                if ($has_photo_path) {
                    $sql = "INSERT INTO success_stories (name, title, content, rating, photo_path, avatar_bg, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = [$name, $title, $content, $rating, $photo_path, $avatar_bg, $is_active, $sort_order];
                } else {
                    $sql = "INSERT INTO success_stories (name, title, content, rating, avatar_bg, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $params = [$name, $title, $content, $rating, $avatar_bg, $is_active, $sort_order];
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                set_flash('success', 'Success story created successfully.');
            }
            redirect('/admin/success_stories');
        } catch (PDOException $e) {
            error_log("Success story save failed: " . $e->getMessage());
            $errors[] = "Failed to save the success story. Please try again.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $is_edit ? 'Edit Success Story' : 'Create Success Story'; ?></h1>
    <a href="/admin/success_stories" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Success Stories
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Student Name *</label>
                        <input type="text" class="form-control form-control-lg" name="name" required
                               value="<?php echo htmlspecialchars($story['name'] ?? ($_POST['name'] ?? '')); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Job Title / Role *</label>
                        <input type="text" class="form-control" name="title" required
                               value="<?php echo htmlspecialchars($story['title'] ?? ($_POST['title'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Success Story / Feedback *</label>
                        <textarea class="form-control" name="content" rows="4" required><?php echo htmlspecialchars($story['content'] ?? ($_POST['content'] ?? '')); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h5 class="mb-0">Settings</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Rating (1-5)</label>
                        <select class="form-select" name="rating">
                            <?php for($i=5; $i>=1; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo (($story['rating'] ?? 5) == $i) ? 'selected' : ''; ?>><?php echo $i; ?> Stars</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Avatar Background</label>
                        <select class="form-select" name="avatar_bg">
                            <option value="bg-primary" <?php echo (($story['avatar_bg'] ?? 'bg-primary') == 'bg-primary') ? 'selected' : ''; ?>>Blue</option>
                            <option value="bg-success" <?php echo (($story['avatar_bg'] ?? 'bg-primary') == 'bg-success') ? 'selected' : ''; ?>>Green</option>
                            <option value="bg-info" <?php echo (($story['avatar_bg'] ?? 'bg-primary') == 'bg-info') ? 'selected' : ''; ?>>Cyan</option>
                            <option value="bg-warning" <?php echo (($story['avatar_bg'] ?? 'bg-primary') == 'bg-warning') ? 'selected' : ''; ?>>Yellow</option>
                            <option value="bg-danger" <?php echo (($story['avatar_bg'] ?? 'bg-primary') == 'bg-danger') ? 'selected' : ''; ?>>Red</option>
                            <option value="bg-dark" <?php echo (($story['avatar_bg'] ?? 'bg-primary') == 'bg-dark') ? 'selected' : ''; ?>>Black</option>
                        </select>
                    </div>

                    <?php if ($has_photo_path): ?>
                        <div class="mb-3">
                            <label class="form-label">Candidate Photo</label>
                            <input type="file" class="form-control" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <small class="text-muted">Optional. Recommended square image, max 2MB.</small>
                        </div>

                        <?php if (!empty($story['photo_path'])): ?>
                            <div class="mb-3">
                                <img src="<?php echo htmlspecialchars(get_image_url($story['photo_path'])); ?>" alt="<?php echo htmlspecialchars($story['name']); ?>" class="img-thumbnail mb-2" style="width: 96px; height: 96px; object-fit: cover;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo">
                                    <label class="form-check-label" for="remove_photo">Remove current photo</label>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="<?php echo $story['sort_order'] ?? 0; ?>">
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               <?php echo ($story['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">Active (Show on Home)</label>
                    </div>
                </div>
            </div>

            <div class="d-grid shadow-sm">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i> Save Story
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
