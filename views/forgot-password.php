<?php
// views/forgot-password.php

if (is_logged_in()) {
    redirect('/dashboard');
}

$page_title = "Forgot Password";

// Handle Forgot Password Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid request. Please try again.');
        redirect('/forgot-password');
    }

    if (!rate_limit_check('forgot_password', 5, 900)) {
        set_flash('danger', 'Too many reset attempts. Please wait a few minutes and try again.');
        redirect('/forgot-password');
    }

    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        set_flash('danger', 'Email is required');
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Block password reset for students (Google-only role)
            if ($user['role'] === 'student') {
                set_flash('info', 'Your account is linked to your Google account. Please use the "Continue with Google" button on the <a href="/login" class="alert-link">Login page</a>.');
                redirect('/forgot-password');
            }
            
            // Proceed with reset for admins
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $token_time = time() + 3600; // Token valid for 1 hour
            
            // Save token to database
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_time = ? WHERE id = ?");
            $stmt->execute([$token, $token_time, $user['id']]);
            
            // Send reset email
            $reset_link = SITE_URL . '/reset-password?token=' . $token . '&email=' . urlencode($email);
            
            $subject = "Password Reset - " . SITE_NAME;
            $message = "
            <html>
            <head>
                <title>Password Reset</title>
            </head>
            <body>
                <h2>Password Reset Request</h2>
                <p>Hello {$user['name']},</p>
                <p>We received a request to reset your password. Click the link below to reset your password:</p>
                <p><a href='{$reset_link}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
                <p>Or copy and paste this link in your browser:</p>
                <p>{$reset_link}</p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request a password reset, please ignore this email.</p>
                <br>
                <p>Best regards,<br>{$site_settings['site_name']}</p>
            </body>
            </html>
            ";
            
            if (send_email($email, $subject, $message, true)) {
                set_flash('success', 'If the account exists and is eligible, a password reset link has been sent.');
            } else {
                error_log('Forgot password email delivery failed for: ' . $email);
                set_flash('info', 'If the account exists and is eligible, a password reset link has been sent.');
            }
        } else {
            set_flash('info', 'If the account exists and is eligible, a password reset link has been sent.');
        }
    }
}

require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title mb-4 text-center"><i class="fas fa-key text-primary"></i> Forgot Password</h2>
                        <p class="text-muted text-center mb-4">Enter your email address and we'll send you a link to reset your password.</p>

                        <?php
                        $flash = get_flash();
                        if ($flash):
                            $category = $flash['type'] == 'error' ? 'danger' : $flash['type'];
                            ?>
                            <div class="alert alert-<?php echo $category; ?>">
                                <?php echo $flash['message']; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" class="form-control" name="email" required placeholder="your@email.com" autofocus>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-paper-plane"></i> Send Reset Link
                            </button>
                        </form>

                        <hr>

                        <p class="text-center">
                            Remember your password?
                            <a href="/login" class="text-primary fw-bold">Back to Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
