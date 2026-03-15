<?php
// admin/trainer_position_form.php - Create/Edit Trainer Position

require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!is_admin()) {
    redirect('/login');
}

$page_title = "Add Trainer Position";
$position = null;
$position_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Schema Debug (Once)
if (!file_exists('schema_debug_log.txt')) {
    try {
        $stmt = $pdo->query("DESCRIBE trainer_positions");
        $schema = "Schema for trainer_positions:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $schema .= "Field: {$row['Field']} | Type: {$row['Type']}\n";
        }
        file_put_contents('schema_debug_log.txt', $schema);
    } catch (Exception $e) {
        file_put_contents('schema_debug_log.txt', "Schema fetch failed: " . $e->getMessage());
    }
}

// Fetch position if editing
if ($position_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM trainer_positions WHERE id = ?");
        $stmt->execute([$position_id]);
        $position = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($position) {
            $page_title = "Edit Trainer Position";
        }
    } catch (PDOException $e) {
        set_flash('danger', 'Error fetching position: ' . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = $_POST['description'] ?? '';
    $expertise_required = $_POST['expertise_required'] ?? '';
    $minimum_experience = !empty($_POST['minimum_experience']) ? intval($_POST['minimum_experience']) : null;
    $location = sanitize($_POST['location'] ?? '');
    $employment_type = sanitize($_POST['employment_type'] ?? 'Full-time');
    $stipend_info = $_POST['stipend_info'] ?? '';
    $growth_opportunities = $_POST['growth_opportunities'] ?? '';
    $requirement_details = $_POST['requirement_details'] ?? '';
    $application_link = trim($_POST['application_link'] ?? ''); // Don't use sanitize() here as it uses htmlspecialchars
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (empty($title) || empty($description)) {
        set_flash('danger', 'Title and description are required');
        redirect("/admin/trainer_position_form" . ($position_id ? "?id=$position_id" : ""));
    }

    // Try to add http if missing
    if (!empty($application_link) && !preg_match("~^(?:f|ht)tps?://~i", $application_link)) {
        $application_link = "https://" . $application_link;
    }

    if (!empty($application_link) && !filter_var($application_link, FILTER_VALIDATE_URL)) {
        set_flash('danger', 'Invalid application link URL: ' . htmlspecialchars($application_link));
        redirect("/admin/trainer_position_form" . ($position_id ? "?id=$position_id" : ""));
    }

    try {
        if ($position_id) {
            // Update existing position
            $stmt = $pdo->prepare("
                UPDATE trainer_positions 
                SET title = ?, description = ?, expertise_required = ?, minimum_experience = ?,
                    location = ?, employment_type = ?, stipend_info = ?, growth_opportunities = ?,
                    requirement_details = ?, application_link = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $description, $expertise_required, $minimum_experience,
                $location, $employment_type, $stipend_info, $growth_opportunities,
                $requirement_details, $application_link, $is_active, $position_id
            ]);
            set_flash('success', 'Trainer position updated successfully!');
        } else {
            // Create new position
            $stmt = $pdo->prepare("
                INSERT INTO trainer_positions 
                (title, description, expertise_required, minimum_experience, location, employment_type,
                 stipend_info, growth_opportunities, requirement_details, application_link, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $description, $expertise_required, $minimum_experience,
                $location, $employment_type, $stipend_info, $growth_opportunities,
                $requirement_details, $application_link, $is_active
            ]);
            set_flash('success', 'Trainer position created successfully!');
        }
        redirect('/admin/trainer_positions');
    } catch (PDOException $e) {
        $error_msg = 'Database Error: ' . $e->getMessage();
        file_put_contents('error_log_debug.txt', date('[Y-m-d H:i:s] ') . $error_msg . PHP_EOL, FILE_APPEND);
        set_flash('danger', $error_msg);
    }
}

require_once 'includes/header.php';
?>

<!-- EasyMDE Markdown Editor -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.js"></script>

<div class="container mt-5 mb-5" style="max-width: 900px;">
    <h2 class="mb-4"><i class="fas fa-briefcase"></i> <?php echo $page_title; ?></h2>

    <?php
    $flash = get_flash();
    if ($flash) {
        echo '<div class="alert alert-' . htmlspecialchars($flash['type']) . ' alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($flash['message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    ?>

    <form method="POST" class="needs-validation">
        <div class="card">
            <div class="card-body">
                <!-- Basic Info -->
                <h5 class="card-title mb-4">Position Details</h5>
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="title" class="form-label">Position Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required
                            value="<?php echo $position ? htmlspecialchars($position['title']) : ''; ?>"
                            placeholder="e.g., Senior Web Development Trainer">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="employment_type" class="form-label">Employment Type *</label>
                        <select class="form-control" id="employment_type" name="employment_type" required>
                            <option value="Full-time" <?php echo ($position && $position['employment_type'] === 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                            <option value="Part-time" <?php echo ($position && $position['employment_type'] === 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                            <option value="Freelance" <?php echo ($position && $position['employment_type'] === 'Freelance') ? 'selected' : ''; ?>>Freelance</option>
                            <option value="Contractual" <?php echo ($position && $position['employment_type'] === 'Contractual') ? 'selected' : ''; ?>>Contractual</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location"
                            value="<?php echo $position ? htmlspecialchars($position['location'] ?? '') : ''; ?>"
                            placeholder="e.g., Remote, Bangalore, Delhi">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="minimum_experience" class="form-label">Minimum Experience (Years)</label>
                        <input type="number" class="form-control" id="minimum_experience" name="minimum_experience" min="0"
                            value="<?php echo $position ? htmlspecialchars($position['minimum_experience'] ?? '') : ''; ?>"
                            placeholder="e.g., 3">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Position Description * (Markdown Supported)</label>
                    <textarea id="description" name="description" required><?php echo $position ? $position['description'] : ''; ?></textarea>
                </div>
                <script>
                    var mde = new EasyMDE({
                        element: document.getElementById("description"),
                        spellChecker: false,
                        autoDownloadFontAwesome: false,
                        toolbar: ["bold", "italic", "heading", "|" , "quote", "unordered-list", "ordered-list", "|" , "link", "image", "|" , "preview", "side-by-side", "fullscreen", "|" , "guide"],
                        placeholder: "Describe the role, responsibilities, and what makes this position unique. Use markdown for formatting."
                    });
                </script>

                <!-- Requirements -->
                <h5 class="card-title mt-5 mb-3">Requirements & Expertise</h5>

                <div class="mb-3">
                    <label for="expertise_required" class="form-label">Required Expertise (Comma-separated)</label>
                    <textarea class="form-control" id="expertise_required" name="expertise_required" rows="3"
                        placeholder="e.g., JavaScript, React, Node.js, Database Design"><?php echo $position ? htmlspecialchars($position['expertise_required'] ?? '') : ''; ?></textarea>
                    <small class="form-text text-muted">List technical skills required, separated by commas</small>
                </div>

                <div class="mb-3">
                    <label for="requirement_details" class="form-label">Detailed Requirements</label>
                    <textarea class="form-control" id="requirement_details" name="requirement_details" rows="4"
                        placeholder="Additional requirements, qualifications, certifications, etc."><?php echo $position ? htmlspecialchars($position['requirement_details'] ?? '') : ''; ?></textarea>
                </div>

                <!-- Compensation & Growth -->
                <h5 class="card-title mt-5 mb-3">Compensation & Growth</h5>

                <div class="mb-3">
                    <label for="stipend_info" class="form-label">Stipend & Compensation Info</label>
                    <textarea class="form-control" id="stipend_info" name="stipend_info" rows="3"
                        placeholder="e.g., Starting with monthly stipend INR 50,000, transitioning to permanent role based on performance"><?php echo $position ? htmlspecialchars($position['stipend_info'] ?? '') : ''; ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="growth_opportunities" class="form-label">Growth & Development Opportunities</label>
                    <textarea class="form-control" id="growth_opportunities" name="growth_opportunities" rows="3"
                        placeholder="Career progression, skill development, leadership opportunities, etc."><?php echo $position ? htmlspecialchars($position['growth_opportunities'] ?? '') : ''; ?></textarea>
                </div>

                <!-- Application -->
                <h5 class="card-title mt-5 mb-3">Application Link</h5>

                <div class="mb-3">
                    <label for="application_link" class="form-label">Application/Form Link *</label>
                    <input type="text" class="form-control" id="application_link" name="application_link" required
                        value="<?php echo $position ? htmlspecialchars($position['application_link']) : ''; ?>"
                        placeholder="https://forms.google.com/...">
                    <small class="form-text text-muted">Link to Google Form or application portal</small>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                            <?php echo (!$position || $position['is_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Active (Visible to users)
                        </label>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> <?php echo $position_id ? 'Update Position' : 'Create Position'; ?>
                </button>
                <a href="/admin/trainer_positions" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
