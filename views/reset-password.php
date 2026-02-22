<?php
// views/reset-password.php

if (is_logged_in()) {
    redirect('/dashboard');
}

$page_title = "Reset Password";

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

// Validate token
$error = '';
$success = false;

if (empty($token) || empty($email)) {
    $error = 'Invalid reset link. Please request a new password reset.';
} else {
    // Check token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND reset_token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $error = 'Invalid reset link. Please request a new password reset.';
    } elseif ($user['reset_token_time'] < time()) {
        $error = 'Reset link has expired. Please request a new password reset.';
    }
}

// Handle Password Reset Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        set_flash('danger', 'Password is required');
    } elseif (strlen($password) < 6) {
        set_flash('danger', 'Password must be at least 6 characters');
    } elseif ($password !== $confirm_password) {
        set_flash('danger', 'Passwords do not match');
    } else {
        // Update password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_time = NULL WHERE id = ?");
        $stmt->execute([$password_hash, $user['id']]);
        
        set_flash('success', 'Password has been reset successfully! You can now login with your new password.');
        redirect('/login');
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
                        <h2 class="card-title mb-4 text-center"><i class="fas fa-lock text-primary"></i> Reset Password</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                            <p class="text-center">
                                <a href="/forgot-password" class="btn btn-primary">Request New Reset Link</a>
                            </p>
                        <?php else: ?>
                            <p class="text-muted text-center mb-4">Enter your new password below.</p>

                            <?php
                            $flash = get_flash();
                            if ($flash):
                                ?>
                                <div class="alert alert-<?php echo $flash['type']; ?>">
                                    <?php echo $flash['message']; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-lock"></i> New Password</label>
                                    <input type="password" class="form-control" name="password" required placeholder="Minimum 6 characters" minlength="6">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label"><i class="fas fa-lock"></i> Confirm Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required placeholder="Re-enter password">
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="fas fa-save"></i> Reset Password
                                </button>
                            </form>
                        <?php endif; ?>

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
