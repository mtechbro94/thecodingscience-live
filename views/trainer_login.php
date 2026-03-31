<?php
// views/trainer_login.php
// Trainer login with Email/Username + Password + OTP verification

if (is_logged_in() && is_trainer()) {
    redirect('/trainer_dashboard');
} elseif (is_logged_in()) {
    redirect('/dashboard');
}

$page_title = "Trainer Login";
require_once 'includes/header.php';
?>

<section class="login-page" style="min-height: 100vh; display: flex; align-items: center; padding: 40px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <!-- Step 1: Credentials -->
                <div id="credentialsStep" class="premium-card p-2" style="animation: fadeInUp 0.8s ease;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary mb-2">Trainer Login</h2>
                            <p class="text-muted">Sign in to your trainer account</p>
                        </div>

                        <form id="trainerForm" onsubmit="handleTrainerSubmit(event)">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="trainerEmail" 
                                    placeholder="your.email@example.com" required autocomplete="email">
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Password</label>
                                    <a href="/forgot-password" class="text-primary small text-decoration-none">Forgot Password?</a>
                                </div>
                                <input type="password" class="form-control form-control-lg" id="trainerPassword" 
                                    placeholder="••••••••" required autocomplete="current-password">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold" id="sendOtpBtn" style="border-radius: 12px;">
                                <i class="fas fa-envelope me-2"></i>Send OTP
                            </button>
                        </form>

                        <hr class="my-4">

                        <p class="text-center text-muted small">
                            New to our platform? 
                            <a href="#" onclick="showStudentLogin()" class="text-primary fw-bold text-decoration-none">
                                Are you a student?
                            </a>
                        </p>

                        <p class="text-center text-muted small">
                            Interested in becoming a trainer? 
                            <a href="/contact" class="text-primary fw-bold text-decoration-none">Contact us</a>
                        </p>
                    </div>
                </div>

                <!-- Step 2: OTP Verification (Hidden) -->
                <div id="otpStep" class="premium-card p-2 d-none" style="animation: fadeInUp 0.8s ease;">
                    <div class="card-body p-4">
                        <button type="button" class="btn btn-link text-primary p-0 mb-3" onclick="resetTrainerForm()">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </button>

                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <div style="font-size: 48px; color: #4f46e5;">
                                    <i class="fas fa-envelope-open-text"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold mb-2">Verify Your Email</h3>
                            <p class="text-muted">We've sent a 6-digit code to <br><strong id="displayEmail"></strong></p>
                        </div>

                        <form id="otpForm" onsubmit="handleVerifyOTP(event)">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Verification Code</label>
                                <input type="text" class="form-control form-control-lg text-center fw-bold" 
                                    id="otpCode" placeholder="000000" maxlength="6" 
                                    style="letter-spacing: 4px; font-size: 24px;" required inputmode="numeric">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold" id="verifyOtpBtn" style="border-radius: 12px;">
                                <i class="fas fa-check me-2"></i>Verify & Login
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted small mb-2">Didn't receive the code?</p>
                            <button type="button" class="btn btn-link text-primary fw-bold p-0" id="resendBtn" onclick="resendOTP()">
                                Resend OTP
                            </button>
                            <small class="d-block text-muted">Code expires in <span id="timer">10:00</span></small>
                        </div>
                    </div>
                </div>

                <!-- Alert Messages -->
                <div id="alertContainer" class="mt-3"></div>

            </div>
        </div>

        <!-- Info Box -->
        <div class="row justify-content-center mt-5" style="animation: fadeInUp 0.8s ease 0.2s both;">
            <div class="col-md-6 col-lg-5">
                <div class="premium-card p-3" style="background: rgba(99, 102, 241, 0.05); border-color: rgba(99, 102, 241, 0.1);">
                    <p class="mb-2 text-primary"><i class="fas fa-shield-alt me-2"></i> <strong>Secure Login</strong></p>
                    <small class="text-muted">Your account is protected by OTP verification. Never share your verification code with anyone.</small>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
let trainerEmail = '';
let otpTimer = null;

async function handleTrainerSubmit(e) {
    e.preventDefault();
    
    const email = document.getElementById('trainerEmail').value.trim();
    const password = document.getElementById('trainerPassword').value;
    const btn = document.getElementById('sendOtpBtn');

    if (!email || !password) {
        showAlert('danger', 'Please fill in all fields');
        return;
    }

    // Disable button and show loading
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';

    try {
        const response = await fetch('/api/trainer_auth.php?action=send_otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                email: email,
                password: password
            })
        });

        const data = await response.json();

        if (data.success) {
            trainerEmail = email;
            document.getElementById('displayEmail').textContent = email;
            document.getElementById('credentialsStep').classList.add('d-none');
            document.getElementById('otpStep').classList.remove('d-none');
            startOtpTimer();
            showAlert('success', 'OTP sent! Check your email.');
        } else {
            showAlert('danger', data.message || 'Failed to send OTP');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function handleVerifyOTP(e) {
    e.preventDefault();
    
    const otp = document.getElementById('otpCode').value.trim();
    const btn = document.getElementById('verifyOtpBtn');

    if (!otp || otp.length !== 6) {
        showAlert('danger', 'Please enter a valid 6-digit code');
        return;
    }

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';

    try {
        const response = await fetch('/api/trainer_auth.php?action=verify_otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                email: trainerEmail,
                otp: otp
            })
        });

        const data = await response.json();

        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => {
                window.location.href = data.redirect || '/trainer_dashboard';
            }, 1500);
        } else {
            showAlert('danger', data.message || 'OTP verification failed');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function startOtpTimer() {
    let timeLeft = 600; // 10 minutes
    clearInterval(otpTimer);

    const updateTimer = () => {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('timer').textContent = 
            `${minutes}:${seconds.toString().padStart(2, '0')}`;

        if (timeLeft <= 0) {
            clearInterval(otpTimer);
            document.getElementById('resendBtn').disabled = false;
            document.getElementById('resendBtn').innerHTML = 'OTP Expired - Resend';
        }
        timeLeft--;
    };

    updateTimer();
    otpTimer = setInterval(updateTimer, 1000);
}

async function resendOTP() {
    const btn = document.getElementById('resendBtn');
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';

    try {
        const response = await fetch('/api/trainer_auth.php?action=send_otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                email: trainerEmail,
                password: document.getElementById('trainerPassword').value
            })
        });

        const data = await response.json();
        if (data.success) {
            showAlert('success', 'OTP resent successfully!');
            startOtpTimer();
        } else {
            showAlert('danger', data.message || 'Failed to resend OTP');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function resetTrainerForm() {
    document.getElementById('trainerForm').reset();
    document.getElementById('otpForm').reset();
    document.getElementById('credentialsStep').classList.remove('d-none');
    document.getElementById('otpStep').classList.add('d-none');
    document.getElementById('alertContainer').innerHTML = '';
    clearInterval(otpTimer);
}

function showAlert(type, message) {
    const container = document.getElementById('alertContainer');
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show border-0" role="alert">
            ${type === 'danger' ? '<i class="fas fa-exclamation-circle me-2"></i>' : '<i class="fas fa-check-circle me-2"></i>'}
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function showStudentLogin() {
    window.location.href = '/student_login';
}

// Auto-format OTP input
document.addEventListener('DOMContentLoaded', () => {
    const otpInput = document.getElementById('otpCode');
    if (otpInput) {
        otpInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
