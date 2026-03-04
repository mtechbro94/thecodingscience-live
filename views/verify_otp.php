<?php
// views/verify_otp.php

if (is_logged_in()) {
    redirect('/dashboard');
}

$email = $_SESSION['pending_verification_email'] ?? '';

if (empty($email)) {
    redirect('/register');
}

$page_title = "Verify Email";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['resend'])) {
        // Handle Resend OTP
        try {
            $otp = generate_otp();
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt = $pdo->prepare("UPDATE users SET otpcode = ?, otp_expiry = ? WHERE email = ? AND is_active = 0");
            $stmt->execute([$otp, $expiry, $email]);

            $subject = "Your New Verification Code - " . SITE_NAME;
            $message = "
                <h2>Email Verification</h2>
                <p>You requested a new verification code. Please use the following One-Time Password (OTP):</p>
                <h1 style='color: #0d6efd; letter-spacing: 5px;'>" . $otp . "</h1>
                <p>This OTP is valid for 15 minutes.</p>
            ";

            if (send_email($email, $subject, $message)) {
                set_flash('success', 'A new verification code has been sent to your email.');
            } else {
                set_flash('danger', 'Failed to send verification email.');
            }
        } catch (PDOException $e) {
            set_flash('danger', 'Error: ' . $e->getMessage());
        }
    } else {
        // Handle OTP Verification
        $otp_input = trim($_POST['otp'] ?? '');

        if (empty($otp_input)) {
            set_flash('danger', 'Please enter the verification code.');
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, otpcode, otp_expiry, role FROM users WHERE email = ? AND is_active = 0");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    $now = date('Y-m-d H:i:s');
                    if ($user['otpcode'] === $otp_input) {
                        if ($user['otp_expiry'] >= $now) {
                            // Success! Activate user
                            $stmt = $pdo->prepare("UPDATE users SET is_active = 1, otpcode = NULL, otp_expiry = NULL WHERE id = ?");
                            $stmt->execute([$user['id']]);

                            unset($_SESSION['pending_verification_email']);
                            set_flash('success', 'Email verified successfully! You can now login.');
                            redirect('/login');
                        } else {
                            set_flash('danger', 'This verification code has expired. Please request a new one.');
                        }
                    } else {
                        set_flash('danger', 'Invalid verification code. Please try again.');
                    }
                } else {
                    set_flash('danger', 'Account already active or not found.');
                    redirect('/login');
                }
            } catch (PDOException $e) {
                set_flash('danger', 'Error: ' . $e->getMessage());
            }
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
                        <h2 class="card-title mb-4 text-center">Verify Your Email</h2>
                        <p class="text-muted text-center mb-4">We've sent a 6-digit verification code to<br><strong>
                                <?php echo htmlspecialchars($email); ?>
                            </strong></p>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="form-label d-block text-center">Enter Verification Code</label>
                                <input type="text" class="form-control form-control-lg text-center fw-bold" name="otp"
                                    required maxlength="6" pattern="\d{6}" placeholder="000000" autofocus
                                    style="letter-spacing: 10px; font-size: 2rem;">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-check-circle me-2"></i> Verify Account
                            </button>
                        </form>

                        <form method="POST" action="" class="text-center">
                            <p class="text-muted mb-2">Didn't receive the code?</p>
                            <button type="submit" name="resend" class="btn btn-link text-primary p-0">
                                <i class="fas fa-redo me-1"></i> Resend Verification Code
                            </button>
                        </form>

                        <hr class="my-4">
                        <div class="text-center">
                            <a href="/register" class="text-muted small">Change Email Address</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>