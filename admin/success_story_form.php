<?php
// admin/success_story_form.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_admin()) {
    redirect('/');
}

$page_title = "Manage Success Story";
$story = [];
$errors = [];

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
    $name = sanitize($_POST['name'] ?? '');
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $rating = (int) ($_POST['rating'] ?? 5);
    $avatar_bg = sanitize($_POST['avatar_bg'] ?? 'bg-primary');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int) ($_POST['sort_order'] ?? 0);

    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($title)) $errors[] = "Title is required";
    if (empty($content)) $errors[] = "Content is required";

    if (empty($errors)) {
        try {
            if ($is_edit) {
                $sql = "UPDATE success_stories SET name = ?, title = ?, content = ?, rating = ?, avatar_bg = ?, is_active = ?, sort_order = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $title, $content, $rating, $avatar_bg, $is_active, $sort_order, $story_id]);
                set_flash('success', 'Success story updated successfully.');
            } else {
                $sql = "INSERT INTO success_stories (name, title, content, rating, avatar_bg, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $title, $content, $rating, $avatar_bg, $is_active, $sort_order]);
                set_flash('success', 'Success story created successfully.');
            }
            redirect('/admin/success_stories');
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
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

<form method="POST">
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

<?php require_once __DIR__ . '/includes/header.php'; ?>
