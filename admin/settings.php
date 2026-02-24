<?php
// admin/settings.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';




if (!is_admin()) {
    redirect('/');
}

$page_title = "Site Settings";
$success = "";
$errors = [];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        try {
            $stmt = $pdo->prepare("INSERT INTO site_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
            $stmt->execute([$key, $value, $value]);
        } catch (PDOException $e) {
            $errors[] = "Failed to update $key: " . $e->getMessage();
        }
    }

    if (empty($errors)) {
        set_flash('success', 'Settings updated successfully.');
        redirect('/admin/settings');
    }
}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM site_settings");
$current_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Site Settings</h1>
</div>

<form method="POST" class="mb-5">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">General Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Site Name</label>
                        <input type="text" class="form-control" name="settings[site_name]"
                            value="<?php echo htmlspecialchars($current_settings['site_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Site URL</label>
                        <input type="url" class="form-control" name="settings[site_url]"
                            value="<?php echo htmlspecialchars($current_settings['site_url'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Email</label>
                        <input type="email" class="form-control" name="settings[contact_email]"
                            value="<?php echo htmlspecialchars($current_settings['contact_email'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" class="form-control" name="settings[contact_phone]"
                            value="<?php echo htmlspecialchars($current_settings['contact_phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Address</label>
                        <textarea class="form-control" name="settings[contact_address]"
                            rows="2"><?php echo htmlspecialchars($current_settings['contact_address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Hero Section</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Hero Title</label>
                        <input type="text" class="form-control" name="settings[hero_title]"
                            value="<?php echo htmlspecialchars($current_settings['hero_title'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hero Subtitle</label>
                        <textarea class="form-control" name="settings[hero_subtitle]"
                            rows="3"><?php echo htmlspecialchars($current_settings['hero_subtitle'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Social Media Links</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-facebook text-primary me-2"></i> Facebook URL</label>
                        <input type="url" class="form-control" name="settings[facebook_url]"
                            value="<?php echo htmlspecialchars($current_settings['facebook_url'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-instagram text-danger me-2"></i> Instagram
                            URL</label>
                        <input type="url" class="form-control" name="settings[instagram_url]"
                            value="<?php echo htmlspecialchars($current_settings['instagram_url'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-youtube text-danger me-2"></i> YouTube URL</label>
                        <input type="url" class="form-control" name="settings[youtube_url]"
                            value="<?php echo htmlspecialchars($current_settings['youtube_url'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-linkedin text-primary me-2"></i> LinkedIn URL</label>
                        <input type="url" class="form-control" name="settings[linkedin_url]"
                            value="<?php echo htmlspecialchars($current_settings['linkedin_url'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fab fa-google me-2"></i>Google SEO Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Google Search Console Verification Meta Tag</label>
                        <input type="text" class="form-control" name="settings[google_search_console_verification]" 
                            value="<?php echo htmlspecialchars($current_settings['google_search_console_verification'] ?? ''); ?>" 
                            placeholder="e.g., xyz123abc...">
                        <small class="text-muted">Get this from Google Search Console → Settings → Verification</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Google Analytics 4 Measurement ID</label>
                        <input type="text" class="form-control" name="settings[google_analytics_id]" 
                            value="<?php echo htmlspecialchars($current_settings['google_analytics_id'] ?? ''); ?>" 
                            placeholder="e.g., G-XXXXXXXXXX">
                        <small class="text-muted">Get this from Google Analytics → Admin → Data Streams</small>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Enrollment Instructions (Markdown)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Displayed on Payment Page</label>
                        <textarea class="form-control" name="settings[enrollment_instructions]"
                            rows="8"><?php echo htmlspecialchars($current_settings['enrollment_instructions'] ?? ''); ?></textarea>
                        <small class="text-muted">You can use Markdown here.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save me-2"></i> Save All Settings
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>