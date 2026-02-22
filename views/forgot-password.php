<?php
// views/forgot-password.php

if (is_logged_in()) {
    redirect('/dashboard');
}

$page_title = "Forgot Password";

// Handle Forgot Password Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        set_flash('danger', 'Email is required');
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
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
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: " . SMTP_USER . "\r\n";
            
            if (mail($email, $subject, $message, $headers)) {
                set_flash('success', 'Password reset link has been sent to your email.');
            } else {
                // For demo purposes, show the link
                set_flash('info', 'Reset link: <a href="' . $reset_link . '">Click here to reset password</a> (Email sending failed - for demo)');
            }
        } else {
            set_flash('danger', 'No account found with this email address.');
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
