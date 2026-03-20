<?php
// views/login.php

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/dashboard');
}

$page_title = "Login";

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $selected_role = $_POST['login_role'] ?? 'student';

    $errors = [];

    // Block traditional login for students and trainers
    if (in_array($selected_role, ['student', 'trainer'])) {
        $errors[] = "Please use Google to sign in to your " . $selected_role . " account.";
    }

    if (empty($email))
        $errors[] = "Email is required";
    if (empty($password))
        $errors[] = "Password is required";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            
            // Only admins can login via password now
            if ($user['role'] !== 'admin') {
                set_flash('danger', 'Please use Google to sign in to your account.');
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

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0" style="border-radius: 20px;">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Welcome Back</h2>
                            <p class="text-muted">Access your dashboard seamlessly</p>
                        </div>

                        <form id="loginForm">
                            <?php
                            $role_param = $_GET['role'] ?? 'student';
                            if (!in_array($role_param, ['student', 'trainer'])) $role_param = 'student';
                            ?>

                            <!-- Role Selection -->
                            <div class="mb-4 text-center">
                                <label class="form-label text-muted small text-uppercase fw-bold mb-3">Login As</label>
                                <div class="d-flex justify-content-center gap-2">
                                    <input type="radio" class="btn-check" name="login_role" id="roleStudent" value="student" <?php echo ($role_param !== 'trainer') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary px-4" for="roleStudent">
                                        <i class="fas fa-user-graduate me-2"></i>Student
                                    </label>

                                    <input type="radio" class="btn-check" name="login_role" id="roleTrainer" value="trainer" <?php echo ($role_param === 'trainer') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-success px-4" for="roleTrainer">
                                        <i class="fas fa-chalkboard-teacher me-2"></i>Trainer
                                    </label>
                                </div>
                            </div>

                            <div class="alert alert-light border text-center mb-4" style="font-size: 0.9rem; border-radius: 12px;">
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                We use Google for secure and fast authentication.
                            </div>

                            <button type="button" onclick="socialLogin('google')" class="btn btn-danger btn-lg w-100 py-3 shadow-sm" style="border-radius: 12px; font-weight: 600;">
                                <i class="fab fa-google me-2"></i> Continue with Google
                            </button>
                        </form>

                        <div class="text-center mt-5">
                            <p class="mb-0">Don't have an account? <a href="/register" class="text-primary fw-bold text-decoration-none">Register Now</a></p>
                        </div>

                        <!-- Subtle Admin Access -->
                        <div class="text-center mt-4">
                            <a href="javascript:void(0)" onclick="toggleAdmin()" class="text-muted small text-decoration-none opacity-50">Admin Access</a>
                        </div>

                        <div id="adminArea" style="display: none;" class="mt-4 pt-4 border-top">
                            <form method="POST" action="">
                                <input type="hidden" name="login_role" value="admin">
                                <div class="mb-3">
                                    <input type="email" class="form-control" name="email" placeholder="Admin Email" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                                </div>
                                <button type="submit" class="btn btn-dark w-100">Admin Sign In</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function toggleAdmin() {
        const area = document.getElementById('adminArea');
        area.style.display = area.style.display === 'none' ? 'block' : 'none';
        if(area.style.display === 'block') {
            area.scrollIntoView({ behavior: 'smooth' });
        }
    }

    function socialLogin(provider) {
        const role = document.querySelector('input[name="login_role"]:checked').value;
        window.location.href = '/social-login/' + provider + '?role=' + role;
    }
</script>

<style>
    .btn-check:checked+.btn-outline-primary {
        background: #0d6efd;
        color: white;
    }
    .btn-check:checked+.btn-outline-success {
        background: #198754;
        color: white;
    }
    .btn-outline-primary, .btn-outline-success {
        border-radius: 10px;
        transition: all 0.2s;
    }
    .btn-outline-primary:hover, .btn-outline-success:hover {
        transform: translateY(-2px);
    }
</style>

<?php require_once 'includes/footer.php'; ?>