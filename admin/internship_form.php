<?php
// admin/internship_form.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_admin()) {
    redirect('/');
}

$page_title = "Manage Internship";
$internship = [];
$errors = [];
$success = "";

// Get Internship ID if editing
$internship_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $internship_id > 0;

// Fetch Internship Data if editing
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM internships WHERE id = ?");
    $stmt->execute([$internship_id]);
    $internship = $stmt->fetch();

    if (!$internship) {
        set_flash('danger', 'Internship not found.');
        redirect('/admin/internships');
    }
    $page_title = "Edit Internship: " . htmlspecialchars($internship['title']);
} else {
    $page_title = "Add New Internship";
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = $_POST['description'] ?? ''; // Allow HTML
    $duration = sanitize($_POST['duration'] ?? '');
    $skills_covered = sanitize($_POST['skills_covered'] ?? '');
    $category = sanitize($_POST['category'] ?? 'industrial');
    $google_form_link = filter_var($_POST['google_form_link'] ?? '', FILTER_VALIDATE_URL);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (empty($title)) {
        $errors[] = "Internship Title is required";
    }
    if (empty($duration)) {
        $errors[] = "Duration is required";
    }
    if (empty($category) || !in_array($category, ['teaching', 'industrial'])) {
        $errors[] = "Invalid category selected.";
    }
    if (!empty($_POST['google_form_link']) && !$google_form_link) {
        $errors[] = "Invalid Google Form Link URL.";
    }


    if (empty($errors)) {
        try {
            if ($is_edit) {
                // Update
                $sql = "UPDATE internships SET title = ?, description = ?, duration = ?, skills_covered = ?, category = ?, google_form_link = ?, is_active = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $description, $duration, $skills_covered, $category, $google_form_link, $is_active, $internship_id]);
                set_flash('success', 'Internship updated successfully.');
            } else {
                // Insert
                $sql = "INSERT INTO internships (title, description, duration, skills_covered, category, google_form_link, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $description, $duration, $skills_covered, $category, $google_form_link, $is_active]);
                set_flash('success', 'Internship created successfully.');
            }
            redirect('/admin/internships');
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <?php echo $is_edit ? 'Edit Internship' : 'Add New Internship'; ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/internships" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Internships
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

<form method="POST" class="mb-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Internship Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Internship Title *</label>
                        <input type="text" class="form-control" name="title" required
                            value="<?php echo htmlspecialchars($internship['title'] ?? ($_POST['title'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select class="form-select" name="category" required>
                            <?php $cat = $internship['category'] ?? ($_POST['category'] ?? 'industrial'); ?>
                            <option value="industrial" <?php echo $cat === 'industrial' ? 'selected' : ''; ?>>Industrial Internship</option>
                            <option value="teaching" <?php echo $cat === 'teaching' ? 'selected' : ''; ?>>Teaching Internship</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea class="form-control" name="description" rows="4"
                            ><?php echo htmlspecialchars($internship['description'] ?? ($_POST['description'] ?? '')); ?></textarea>
                        <small class="text-muted">A brief overview of the internship. HTML tags allowed.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Duration *</label>
                        <input type="text" class="form-control" name="duration" placeholder="e.g. 3 Months, 8 Weeks" required
                            value="<?php echo htmlspecialchars($internship['duration'] ?? ($_POST['duration'] ?? '')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Skills Covered (Comma-separated)</label>
                        <input type="text" class="form-control" name="skills_covered" placeholder="e.g. Python, Machine Learning, SQL"
                            value="<?php echo htmlspecialchars($internship['skills_covered'] ?? ($_POST['skills_covered'] ?? '')); ?>">
                        <small class="text-muted">List key skills students will gain, separated by commas.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Google Form Link</label>
                        <input type="url" class="form-control" name="google_form_link" placeholder="https://docs.google.com/forms/d/e/..."
                            value="<?php echo htmlspecialchars($internship['google_form_link'] ?? ($_POST['google_form_link'] ?? '')); ?>">
                        <small class="text-muted">The direct link to the Google Form for application.</small>
                    </div>

                    <div class="mb-3 border-top pt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo ($internship['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="is_active">Internship Active</label>
                        </div>
                        <small class="text-muted">Toggle to make the internship visible on the frontend.</small>
                    </div>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Internship
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>