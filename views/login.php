<?php
// views/login.php

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/dashboard');
}

$page_title = "Login";

// Handle Login Form Submission (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $selected_role = $_POST['login_role'] ?? 'student';

    $errors = [];

    // Block traditional login for students
    if ($selected_role === 'student') {
        $errors[] = "Please use Google to sign in to your student account.";
    }

    if (empty($email))
        $errors[] = "Email is required";
    if (empty($password))
        $errors[] = "Password is required";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->execute([$email, $selected_role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            
            // Allow admins and trainers to login via password
            if ($user['role'] === 'student') {
                set_flash('danger', 'Please use Google to sign in to your student account.');
                redirect('/login');
            }

            // Check if user account is active
            if (empty($user['is_active']) || $user['is_active'] == 0) {
                set_flash('danger', 'Your account has been deactivated. Please contact support.');
                redirect('/login');
            }

            // Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_profile_image'] = $user['profile_image'];
            $_SESSION['is_approved'] = $user['is_approved'] ?? 1;

            session_regenerate_id(true);

            set_flash('success', 'Welcome back, ' . $user['name'] . '!');

            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('/admin/dashboard');
            } else {
                redirect('/dashboard');
            }
        } else {
            set_flash('danger', 'Invalid email or password');
        }
    } else {
        foreach ($errors as $error) {
            set_flash('danger', $error);
        }
    }
}

require_once 'includes/header.php';
?>

<section class="login-page">
    <!-- Animated Background -->
    <div class="login-bg">
        <div class="login-bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
            <div class="shape shape-5"></div>
        </div>
    </div>

    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center align-items-center min-vh-100 py-5" style="margin-top: 40px;">
            <div class="col-lg-10">
                <div class="login-wrapper">
                    <div class="row g-0">
                        <!-- Left Panel: Branding -->
                        <div class="col-lg-5 d-none d-lg-flex">
                            <div class="login-branding">
                                <div class="branding-content">
                                    <div class="brand-icon-wrap">
                                        <i class="fas fa-code"></i>
                                    </div>
                                    <h2><?php echo get_setting('site_name', SITE_NAME); ?></h2>
                                    <p class="brand-tagline">Your gateway to mastering AI, Data Science, and emerging technologies.</p>
                                    
                                    <div class="brand-features">
                                        <div class="brand-feature">
                                            <div class="feature-dot"></div>
                                            <span>Industry-led live training</span>
                                        </div>
                                        <div class="brand-feature">
                                            <div class="feature-dot"></div>
                                            <span>Real-world project experience</span>
                                        </div>
                                        <div class="brand-feature">
                                            <div class="feature-dot"></div>
                                            <span>Placement assistance</span>
                                        </div>
                                        <div class="brand-feature">
                                            <div class="feature-dot"></div>
                                            <span>5000+ students enrolled</span>
                                        </div>
                                    </div>

                                    <div class="brand-stats">
                                        <div class="brand-stat">
                                            <span class="stat-num">5000+</span>
                                            <span class="stat-label">Students</span>
                                        </div>
                                        <div class="brand-stat-divider"></div>
                                        <div class="brand-stat">
                                            <span class="stat-num">500+</span>
                                            <span class="stat-label">Certifications</span>
                                        </div>
                                        <div class="brand-stat-divider"></div>
                                        <div class="brand-stat">
                                            <span class="stat-num">95%</span>
                                            <span class="stat-label">Satisfaction</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Login Form -->
                        <div class="col-lg-7">
                            <div class="login-form-panel">
                                <div class="login-form-inner">
                                    <!-- Header -->
                                    <div class="login-header">
                                        <div class="welcome-emoji">👋</div>
                                        <h3>Welcome Back</h3>
                                        <p>Sign in or create your account seamlessly</p>
                                    </div>

                                    <div class="login-options-wrapper">
                                        <?php
                                        $role_param = $_GET['role'] ?? 'student';
                                        if (!in_array($role_param, ['student', 'trainer', 'admin']))
                                            $role_param = 'student';
                                        ?>
                                        <!-- Role Selection -->
                                        <div class="role-selector">
                                            <label class="role-label">I am a</label>
                                            <div class="role-toggle">
                                                <input type="radio" class="btn-check" name="login_role" id="roleStudent"
                                                    value="student" <?php echo ($role_param !== 'trainer') ? 'checked' : ''; ?>
                                                    onchange="updateRoleUI()">
                                                <label class="role-option" for="roleStudent">
                                                    <div class="role-icon"><i class="fas fa-user-graduate"></i></div>
                                                    <div class="role-text">
                                                        <span class="role-name">Student</span>
                                                        <span class="role-desc">Learn & grow</span>
                                                    </div>
                                                </label>

                                                <input type="radio" class="btn-check" name="login_role" id="roleTrainer"
                                                    value="trainer" <?php echo ($role_param === 'trainer') ? 'checked' : ''; ?>
                                                    onchange="updateRoleUI()">
                                                <label class="role-option" for="roleTrainer">
                                                    <div class="role-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                                    <div class="role-text">
                                                        <span class="role-name">Trainer</span>
                                                        <span class="role-desc">Teach & inspire</span>
                                                    </div>
                                                </label>

                                                <input type="radio" class="btn-check" name="login_role" id="roleAdmin"
                                                    value="admin" <?php echo ($role_param === 'admin') ? 'checked' : ''; ?>
                                                    onchange="updateRoleUI()">
                                                <label class="role-option" for="roleAdmin">
                                                    <div class="role-icon"><i class="fas fa-user-shield"></i></div>
                                                    <div class="role-text">
                                                        <span class="role-name">Admin</span>
                                                        <span class="role-desc">Manage platform</span>
                                                    </div>
                                                </label>
                                            </div>
                                            <p class="text-center small mt-3 mb-0">
                                                Need administrator access?
                                                <a href="/login?role=admin" class="text-primary fw-semibold text-decoration-none">Open admin sign in</a>
                                            </p>
                                        </div>

                                        <!-- Student Auth Silo -->
                                        <div id="studentAuth" class="auth-section">
                                            <div class="security-badge">
                                                <i class="fas fa-shield-alt"></i>
                                                <span>Secured by Google — fast, safe, and private</span>
                                            </div>
                                            <button type="button" onclick="socialLogin('google')" class="google-btn">
                                                <div class="google-btn-inner">
                                                    <svg class="google-icon" viewBox="0 0 24 24" width="22" height="22">
                                                        <path fill="#4285F4"
                                                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" />
                                                        <path fill="#34A853"
                                                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                                        <path fill="#FBBC05"
                                                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                                        <path fill="#EA4335"
                                                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                                    </svg>
                                                    <span>Continue with Google</span>
                                                </div>
                                            </button>
                                        </div>

                                        <!-- Trainer Auth Silo -->
                                        <div id="trainerAuth" class="auth-section d-none">
                                            <div id="trainerCredentialsArea">
                                                <div class="trainer-login-form p-3 border rounded-4 bg-light mb-3">
                                                    <form id="trainerLoginForm" onsubmit="handleTrainerSubmit(event)">
                                                        <input type="hidden" id="trainerCsrfToken" value="<?php echo generate_csrf_token(); ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Email Address</label>
                                                            <input type="email" class="form-control" id="trainerEmail"
                                                                placeholder="trainer@example.com" required>
                                                        </div>
                                                        <div class="mb-3 d-flex justify-content-between align-items-center">
                                                            <label class="form-label small fw-bold mb-0">Password</label>
                                                            <a href="/forgot-password" class="text-primary small fw-semibold text-decoration-none">Forgot Password?</a>
                                                        </div>
                                                        <div class="mb-3">
                                                            <input type="password" class="form-control" id="trainerPassword"
                                                                placeholder="••••••••" required>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2" id="trainerSubmitBtn"
                                                            style="border-radius: 10px;">
                                                            Trainer Sign In
                                                        </button>
                                                    </form>
                                                    <p class="text-muted small text-center mt-3 mb-0">
                                                        Trainer accounts are provisioned by the team.
                                                        <a href="/contact" class="text-primary fw-semibold text-decoration-none">Request access</a>
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- OTP Verification Area (Hidden) -->
                                            <div id="trainerOtpArea" class="d-none text-center p-3 animate-fade-in">
                                                <div class="mb-3">
                                                    <div class="otp-icon-wrap text-primary mb-2">
                                                        <i class="fas fa-envelope-open-text fa-2x"></i>
                                                    </div>
                                                    <h5 class="fw-bold">Verify Your Email</h5>
                                                    <p class="small text-muted">We've sent a 6-digit code to <br><strong id="displayEmail"></strong></p>
                                                </div>
                                                <div class="otp-input-wrap mb-3 d-flex justify-content-center gap-2">
                                                    <input type="text" maxlength="6" id="otpCode" class="form-control text-center fw-bold fs-4" style="letter-spacing: 5px; width: 200px;" placeholder="000000">
                                                </div>
                                                <button type="button" onclick="handleVerifyOTP()" class="btn btn-primary w-100 fw-bold py-2 mb-2" id="verifyOtpBtn">
                                                    Verify & Login
                                                </button>
                                                <button type="button" onclick="resetTrainerAuth()" class="btn btn-link btn-sm text-muted text-decoration-none">
                                                    <i class="fas fa-arrow-left me-1"></i>Back to login
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Admin Auth Silo -->
                                        <div id="adminAuth" class="auth-section d-none">
                                            <div class="trainer-login-form p-3 border rounded-4 bg-light mb-3">
                                                <form method="POST" action="/login">
                                                    <input type="hidden" name="login_role" value="admin">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Admin Email Address</label>
                                                        <input type="email" class="form-control" name="email"
                                                            placeholder="admin@example.com" required autocomplete="email">
                                                    </div>
                                                    <div class="mb-3 d-flex justify-content-between align-items-center">
                                                        <label class="form-label small fw-bold mb-0">Password</label>
                                                        <a href="/forgot-password" class="text-primary small fw-semibold text-decoration-none">Forgot Password?</a>
                                                    </div>
                                                    <div class="mb-3">
                                                        <input type="password" class="form-control" name="password"
                                                            placeholder="••••••••" required autocomplete="current-password">
                                                    </div>
                                                    <button type="submit" class="btn btn-dark w-100 fw-bold py-2"
                                                        style="border-radius: 10px;">
                                                        Admin Sign In
                                                    </button>
                                                </form>
                                                <p class="text-muted small text-center mt-3 mb-0">
                                                    Restricted to administrator accounts.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Terms -->
                                    <p class="login-terms">
                                        By continuing, you agree to our <a href="/terms">Terms</a> and <a href="/privacy">Privacy Policy</a>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function updateRoleUI() {
        const role = document.querySelector('input[name="login_role"]:checked').value;
        const studentAuth = document.getElementById('studentAuth');
        const trainerAuth = document.getElementById('trainerAuth');
        const adminAuth = document.getElementById('adminAuth');
        
        if (role === 'trainer') {
            studentAuth.classList.add('d-none');
            trainerAuth.classList.remove('d-none');
            adminAuth.classList.add('d-none');
        } else if (role === 'admin') {
            studentAuth.classList.add('d-none');
            trainerAuth.classList.add('d-none');
            adminAuth.classList.remove('d-none');
        } else {
            studentAuth.classList.remove('d-none');
            trainerAuth.classList.add('d-none');
            adminAuth.classList.add('d-none');
        }

        // Update URL without reloading for bookmarking/sharing
        const url = new URL(window.location);
        url.searchParams.set('role', role);
        window.history.pushState({}, '', url);
    }

    async function handleTrainerSubmit(e) {
        e.preventDefault();
        const email = document.getElementById('trainerEmail').value;
        const password = document.getElementById('trainerPassword').value;
        const submitBtn = document.getElementById('trainerSubmitBtn');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

        try {
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('csrf_token', document.getElementById('trainerCsrfToken').value);

            const response = await fetch('/api/trainer_auth.php?action=send_otp', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                document.getElementById('trainerCredentialsArea').classList.add('d-none');
                document.getElementById('displayEmail').innerText = email;
                document.getElementById('trainerOtpArea').classList.remove('d-none');
            } else {
                alert(result.message || 'Something went wrong.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Could not connect to the server.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Trainer Sign In';
        }
    }

    async function handleVerifyOTP() {
        const email = document.getElementById('trainerEmail').value;
        const otp = document.getElementById('otpCode').value;
        const verifyBtn = document.getElementById('verifyOtpBtn');

        if (!otp || otp.length < 6) {
            alert('Please enter a valid 6-digit OTP.');
            return;
        }

        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';

        try {
            const formData = new FormData();
            formData.append('email', email);
            formData.append('otp', otp);
            formData.append('csrf_token', document.getElementById('trainerCsrfToken').value);

            const response = await fetch('/api/trainer_auth.php?action=verify_otp', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                window.location.href = result.redirect || '/dashboard';
            } else {
                alert(result.message || 'Invalid OTP.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Could not connect to the server.');
        } finally {
            verifyBtn.disabled = false;
            verifyBtn.innerText = 'Verify & Login';
        }
    }

    function resetTrainerAuth() {
        document.getElementById('trainerOtpArea').classList.add('d-none');
        document.getElementById('trainerCredentialsArea').classList.remove('d-none');
        document.getElementById('otpCode').value = '';
    }

    function socialLogin(provider) {
        const role = document.querySelector('input[name="login_role"]:checked').value;
        window.location.href = '/social-login/' + provider + '?role=' + role;
    }

    // Role selection animation
    document.querySelectorAll('.role-option').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.role-option').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
        });
        // Initialize active state
        const radio = opt.previousElementSibling;
        if (radio && radio.checked) {
            opt.classList.add('active');
            updateRoleUI();
        }
    });
</script>

<style>
    /* ===== LOGIN PAGE ===== */
    .login-page {
        position: relative;
        min-height: 100vh;
        overflow: hidden;
    }

    .login-page .main-content { padding: 0; }
    .login-page section { padding: 0; }
    .login-page section h2::after { display: none; }

    /* Animated Background */
    .login-bg {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
        z-index: 0;
    }

    .login-bg-shapes .shape {
        position: absolute;
        border-radius: 50%;
        opacity: 0.08;
        animation: floatShape 20s infinite ease-in-out;
    }
    .shape-1 { width: 400px; height: 400px; background: #4f46e5; top: -100px; right: -100px; animation-delay: 0s; }
    .shape-2 { width: 300px; height: 300px; background: #06b6d4; bottom: -50px; left: -80px; animation-delay: 5s; }
    .shape-3 { width: 200px; height: 200px; background: #f59e0b; top: 50%; left: 20%; animation-delay: 10s; }
    .shape-4 { width: 150px; height: 150px; background: #10b981; top: 20%; right: 30%; animation-delay: 7s; }
    .shape-5 { width: 250px; height: 250px; background: #8b5cf6; bottom: 20%; right: 10%; animation-delay: 3s; }

    @keyframes floatShape {
        0%, 100% { transform: translate(0, 0) scale(1); }
        25% { transform: translate(30px, -30px) scale(1.05); }
        50% { transform: translate(-20px, 20px) scale(0.95); }
        75% { transform: translate(15px, 10px) scale(1.02); }
    }

    /* Login Wrapper (card container) */
    .login-wrapper {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
        animation: slideUp 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .login-wrapper:hover {
        transform: none;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Left Branding Panel */
    .login-branding {
        background: linear-gradient(160deg, #4f46e5 0%, #7c3aed 50%, #2563eb 100%);
        padding: 3rem 2.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .login-branding::before {
        content: '';
        position: absolute;
        top: -50%; right: -50%;
        width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 60%);
        animation: rotateBg 30s linear infinite;
    }

    @keyframes rotateBg {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .branding-content {
        position: relative;
        z-index: 2;
    }

    .brand-icon-wrap {
        width: 60px; height: 60px;
        background: rgba(255,255,255,0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
    }

    .login-branding h2 {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }

    .brand-tagline {
        color: rgba(255,255,255,0.8);
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .brand-features {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 2rem;
    }

    .brand-feature {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: rgba(255,255,255,0.9);
        font-size: 0.88rem;
        font-weight: 500;
    }

    .feature-dot {
        width: 8px; height: 8px;
        background: #34d399;
        border-radius: 50%;
        flex-shrink: 0;
        box-shadow: 0 0 8px rgba(52,211,153,0.5);
    }

    .brand-stats {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: rgba(255,255,255,0.1);
        border-radius: 14px;
        backdrop-filter: blur(10px);
    }

    .brand-stat {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
    }

    .stat-num {
        font-size: 1.25rem;
        font-weight: 800;
        color: #fff;
    }

    .stat-label {
        font-size: 0.7rem;
        color: rgba(255,255,255,0.65);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .brand-stat-divider {
        width: 1px;
        height: 30px;
        background: rgba(255,255,255,0.2);
    }

    /* Right Form Panel */
    .login-form-panel {
        background: #ffffff;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 100%;
    }

    .login-form-inner {
        max-width: 420px;
        margin: 0 auto;
        width: 100%;
    }

    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .welcome-emoji {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        animation: wave 2s ease-in-out infinite;
    }

    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(20deg); }
        75% { transform: rotate(-15deg); }
    }

    .login-header h3 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }

    .login-header p {
        color: #64748b;
        font-size: 0.95rem;
    }

    /* Role Selector */
    .role-selector {
        margin-bottom: 1.5rem;
    }

    .role-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        margin-bottom: 0.75rem;
        text-align: center;
    }

    .role-toggle {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .role-option {
        flex: 1 1 120px;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .role-option:hover {
        border-color: #c7d2fe;
        background: #f0f0ff;
        transform: translateY(-2px);
    }

    .role-option.active,
    .btn-check:checked + .role-option {
        border-color: #4f46e5;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.15);
    }

    .role-icon {
        width: 40px; height: 40px;
        background: #e2e8f0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #64748b;
        transition: all 0.3s ease;
    }

    .btn-check:checked + .role-option .role-icon {
        background: #4f46e5;
        color: white;
    }

    .role-text {
        display: flex;
        flex-direction: column;
    }

    .role-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
    }

    .role-desc {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* Security Badge */
    .security-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-size: 0.82rem;
        color: #166534;
        font-weight: 500;
    }

    .security-badge i {
        color: #16a34a;
        font-size: 0.9rem;
    }

    /* Google Button */
    .google-btn {
        position: relative;
        width: 100%;
        padding: 0;
        border: none;
        background: transparent;
        cursor: pointer;
        outline: none;
    }

    .google-btn-inner {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 2;
    }

    .google-btn:hover .google-btn-inner {
        border-color: #4f46e5;
        background: #fafafa;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.15);
    }

    .google-btn:active .google-btn-inner {
        transform: translateY(0);
    }

    .google-btn-glow {
        position: absolute;
        bottom: -4px; left: 10%; right: 10%;
        height: 20px;
        background: linear-gradient(90deg, #4285F4, #34A853, #FBBC05, #EA4335);
        border-radius: 0 0 14px 14px;
        filter: blur(12px);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1;
    }

    .google-btn:hover .google-btn-glow {
        opacity: 0.5;
    }

    .google-icon {
        flex-shrink: 0;
    }

    /* Terms */
    .login-terms {
        text-align: center;
        font-size: 0.78rem;
        color: #94a3b8;
        margin-top: 1.25rem;
        margin-bottom: 0;
    }

    .login-terms a {
        color: #4f46e5;
        text-decoration: none;
        font-weight: 600;
    }

    .login-terms a:hover {
        text-decoration: underline;
    }

    /* Terms */
    .login-terms {
        text-align: center;
        font-size: 0.78rem;
        color: #94a3b8;
        margin-top: 1.25rem;
        margin-bottom: 0;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 991px) {
        .login-form-panel {
            padding: 2.5rem 1.5rem;
        }

        .login-wrapper {
            margin: 1rem;
        }
    }

    @media (max-width: 576px) {
        .login-form-panel {
            padding: 2rem 1.25rem;
        }

        .role-toggle {
            flex-direction: column;
        }

        .brand-stats {
            gap: 0.5rem;
        }

        .login-header h3 {
            font-size: 1.5rem;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>
