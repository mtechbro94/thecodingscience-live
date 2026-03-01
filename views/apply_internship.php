<?php
// views/apply_internship.php

$internship_id = isset($id) ? (int) $id : 0;

$internships = [
    1 => ['role' => 'Web Development Trainer', 'company' => 'The Coding Science', 'duration' => '1 Month Internship', 'price' => 2999, 'color' => 'primary'],
    2 => ['role' => 'Python Trainer', 'company' => 'The Coding Science', 'duration' => '1 Month Internship', 'price' => 2999, 'color' => 'success'],
    3 => ['role' => 'Data Science & AI Trainer', 'company' => 'The Coding Science', 'duration' => '1 Month Internship', 'price' => 2999, 'color' => 'warning']
];

if (!isset($internships[$internship_id])) {
    set_flash('danger', 'Internship not found.');
    redirect('/internships');
}

$internship = $internships[$internship_id];

// Handle Application Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        redirect('/login?redirect=/apply-internship/' . $internship_id);
    }
    
    $user = current_user();
    
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $college = sanitize($_POST['college'] ?? '');
    $year = sanitize($_POST['year'] ?? '');
    $skills = sanitize($_POST['skills'] ?? '');
    $github = sanitize($_POST['github'] ?? '');
    $linkedin = sanitize($_POST['linkedin'] ?? '');
    
    // Handle resume upload
    $resume_path = null;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = BASE_PATH . '/assets/uploads/resumes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx'];
        
        if (in_array($file_ext, $allowed_exts) && $_FILES['resume']['size'] <= 5242880) {
            $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
            $resume_path = 'assets/uploads/resumes/' . $new_filename;
            move_uploaded_file($_FILES['resume']['tmp_name'], BASE_PATH . '/' . $resume_path);
        } else {
            $errors[] = "Resume must be PDF, DOC, or DOCX and less than 5MB";
        }
    }
    
    $errors = [];
    
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($phone)) $errors[] = "Phone is required";
    if (empty($college)) $errors[] = "College is required";
    if (empty($year)) $errors[] = "Year is required";
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO internship_applications (user_id, internship_id, internship_role, name, email, phone, college, year, skills, github, linkedin, resume, status, applied_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$user['id'], $internship_id, $internship['role'], $name, $email, $phone, $college, $year, $skills, $github, $linkedin, $resume_path]);
            
            // Send confirmation email
            $to = $email;
            $subject = "Internship Application Received - " . SITE_NAME;
            
            $message = "
            <html>
            <head>
                <title>Application Received</title>
            </head>
            <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: #007bff; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0;'>Application Received!</h1>
                </div>
                <div style='padding: 20px; border: 1px solid #ddd;'>
                    <p>Dear <strong>$name</strong>,</p>
                    
                    <p>Thank you for applying for the <strong>{$internship['role']}</strong> position at The Coding Science.</p>
                    
                    <p>We have received your application and our team will review it shortly. If your profile matches our requirements, we will get back to you within 2-3 business days.</p>
                    
                    <h3>Application Details:</h3>
                    <ul>
                        <li><strong>Position:</strong> {$internship['role']}</li>
                        <li><strong>Applied On:</strong> " . date('F j, Y') . "</li>
                    </ul>
                    
                    <p>In the meantime, feel free to explore our courses at: " . SITE_URL . "/courses</p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                    
                    <p style='color: #666; font-size: 12px;'>
                        Best regards,<br>
                        The Coding Science Team<br>
                        " . SITE_URL . "
                    </p>
                </div>
            </body>
            </html>
            ";
            
            $email_sent = send_email($to, $subject, $message, true);
            
            set_flash('success', 'Application submitted successfully! ' . ($email_sent ? 'A confirmation email has been sent to your email address.' : ''));
            redirect('/dashboard');
        } catch (PDOException $e) {
            $error = "Application failed: " . $e->getMessage();
        }
    }
}

$page_title = "Apply for Internship";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-<?php echo $internship['color']; ?> text-white p-4 text-center">
                        <h2 class="mb-0"><i class="fas fa-briefcase me-2"></i> Apply for Internship</h2>
                        <p class="mb-0 mt-2"><?php echo $internship['role']; ?></p>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Internship Details -->
                        <div class="bg-light p-3 rounded mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Company:</strong> <?php echo $internship['company']; ?></p>
                                    <p class="mb-0"><strong>Duration:</strong> <?php echo $internship['duration']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-0"><strong>Fee:</strong> <span class="text-success">₹<?php echo number_format($internship['price']); ?>/month</span></p>
                                </div>
                            </div>
                            <div class="mt-2 text-center">
                                <span class="badge bg-success"><i class="fas fa-star me-1"></i>Get Full-Time Opportunity After 1 Month!</span>
                            </div>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $e): ?>
                                    <p class="mb-0"><?php echo $e; ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <h5 class="mb-3">Personal Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="name" required value="<?php echo is_logged_in() ? htmlspecialchars(current_user()['name']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" required value="<?php echo is_logged_in() ? htmlspecialchars(current_user()['email']) : ''; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">College/University *</label>
                                    <input type="text" class="form-control" name="college" required placeholder="Your college name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Current Year *</label>
                                    <select class="form-select" name="year" required>
                                        <option value="">Select Year</option>
                                        <option value="1st Year">1st Year</option>
                                        <option value="2nd Year">2nd Year</option>
                                        <option value="3rd Year">3rd Year</option>
                                        <option value="4th Year">4th Year</option>
                                        <option value="Graduate">Graduate</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Skills</label>
                                    <input type="text" class="form-control" name="skills" placeholder="e.g., Python, HTML, CSS, JavaScript">
                                </div>
                            </div>

                            <!-- Resume Upload -->
                            <div class="mb-3">
                                <label class="form-label">Resume (PDF, DOC, DOCX - Max 5MB)</label>
                                <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx">
                                <small class="text-muted">Upload your resume for better chances of selection</small>
                            </div>

                            <h5 class="mb-3 mt-4">Links (Optional)</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">GitHub Profile</label>
                                    <input type="url" class="form-control" name="github" placeholder="https://github.com/username">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">LinkedIn Profile</label>
                                    <input type="url" class="form-control" name="linkedin" placeholder="https://linkedin.com/in/username">
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i> Submit Application
                                </button>
                                <a href="/internships" class="btn btn-outline-secondary">Back to Internships</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
