<?php
// views/student_login.php
// Student login with Gmail OAuth

if (is_logged_in() && !is_trainer()) {
    redirect('/dashboard');
} elseif (is_logged_in() && is_trainer()) {
    redirect('/trainer_dashboard');
}

$page_title = "Student Login";
require_once 'includes/header.php';
?>

<section class="login-page" style="min-height: 100vh; display: flex; align-items: center; padding: 40px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="premium-card p-2" style="animation: fadeInUp 0.8s ease;">
                    <div class="card-body p-4">
                        <div class="text-center mb-5">
                            <h2 class="fw-bold mb-2">Student Login</h2>
                            <p class="text-muted">Sign in to access your learning dashboard</p>
                        </div>

                        <!-- Google OAuth Button -->
                        <button type="button" class="btn btn-light btn-lg w-100 border border-2" id="googleLoginBtn" 
                            onclick="startGoogleLogin()" style="border-color: #e5e7eb; border-radius: 12px;">
                            <svg class="me-2" width="20" height="20" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" />
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                            </svg>
                            <span class="fw-bold">Continue with Google</span>
                        </button>

                        <div class="premium-card p-3 mt-4" style="background: rgba(99, 102, 241, 0.05); border-color: rgba(99, 102, 241, 0.1);">
                            <p class="mb-2 text-primary"><i class="fas fa-lock me-2"></i> <strong>Secure & Easy</strong></p>
                            <small class="text-muted">We use Google Sign-In to keep your account secure. You'll need a Google account to proceed.</small>
                        </div>

                        <hr class="my-4">

                        <p class="text-center text-muted small">
                            Are you a trainer? 
                            <a href="/trainer_login" class="text-primary fw-bold text-decoration-none">
                                Login as Trainer
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Alert Messages -->
                <div id="alertContainer" class="mt-3"></div>

            </div>
        </div>
    </div>
</section>

<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
// Initialize Google Sign-In
let googleClientId = '<?php echo htmlspecialchars(GOOGLE_CLIENT_ID); ?>';

function startGoogleLogin() {
    if (!googleClientId) {
        showAlert('danger', 'Google Sign-In is not configured. Please contact support.');
        return;
    }

    google.accounts.id.initialize({
        client_id: googleClientId,
        callback: handleGoogleResponse,
        ux_mode: 'popup'
    });

    google.accounts.id.renderButton(document.getElementById('googleLoginBtn'), {
        type: 'standard',
        size: 'large',
        text: 'continue_with',
        width: '500'
    });

    google.accounts.id.prompt((notification) => {
        if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
            // Trigger manual button click
            const btn = document.getElementById('googleLoginBtn');
            if (btn) btn.click();
        }
    });
}

async function handleGoogleResponse(response) {
    if (!response.credential) {
        showAlert('danger', 'Failed to get Google credentials');
        return;
    }

    // Decode JWT token
    const jwt = response.credential;
    const payload = JSON.parse(atob(jwt.split('.')[1]));

    const btn = document.getElementById('googleLoginBtn');
    btn.disabled = true;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing in...';

    try {
        // Send to backend for verification
        const res = await fetch('/api/student_auth.php?action=verify_gmail', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                access_token: jwt,
                gmail_id: payload.sub,
                email: payload.email,
                name: payload.name
            })
        });

        const data = await res.json();

        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => {
                window.location.href = data.redirect || '/dashboard';
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Login failed');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred during login. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => startGoogleLogin(), 500);
});
</script>

<?php require_once 'includes/footer.php'; ?>
