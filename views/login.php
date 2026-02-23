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

    $errors = [];

    if (empty($email))
        $errors[] = "Email is required";
    if (empty($password))
        $errors[] = "Password is required";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Check if user account is active
            if (empty($user['is_active']) || $user['is_active'] == 0) {
                set_flash('danger', 'Your account has been deactivated. Please contact support.');
                redirect('/login');
            }

            // Check if trainer is approved
            if ($user['role'] === 'trainer' && empty($user['is_approved'])) {
                set_flash('warning', 'Your trainer account is pending approval. Please wait for admin to approve your account.');
                redirect('/login');
            }

            // Role Verification Logic
            $selected_role = $_POST['login_role'] ?? 'student';

            // Admin can login through any role selection but will be redirected to admin dashboard
            if ($user['role'] === 'admin') {
                // Admin is allowed
            } elseif ($user['role'] !== $selected_role) {
                set_flash('danger', 'Unauthorized access. Please select the correct role for your account.');
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
            } elseif ($user['role'] === 'trainer') {
                redirect('/trainer-dashboard');
            } else {
                // Student
                $redirect_url = $_GET['redirect'] ?? '/dashboard';
                redirect($redirect_url);
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
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title mb-4 text-center">Welcome Back</h2>
                        <p class="text-muted text-center mb-4">Sign in to access your dashboard</p>

                        <form method="POST" action="">
                            <!-- Role Selection -->
                            <div class="mb-4">
                                <label class="form-label text-center d-block mb-3"><i class="fas fa-user-tag"></i> Login
                                    As</label>
                                <div class="d-flex justify-content-center gap-2">
                                    <input type="radio" class="btn-check" name="login_role" id="roleStudent"
                                        value="student" checked>
                                    <label class="btn btn-outline-primary" for="roleStudent">
                                        <i class="fas fa-user-graduate"></i><br>
                                        <small>Student</small>
                                    </label>

                                    <input type="radio" class="btn-check" name="login_role" id="roleTrainer"
                                        value="trainer">
                                    <label class="btn btn-outline-success" for="roleTrainer">
                                        <i class="fas fa-chalkboard-teacher"></i><br>
                                        <small>Trainer</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" class="form-control" name="email" required
                                    placeholder="your@email.com" autofocus>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" class="form-control" name="password" required
                                    placeholder="Enter your password">
                                <a href="/forgot-password" class="small text-primary mt-2 d-inline-block">
                                    <i class="fas fa-redo"></i> Forgot Password?
                                </a>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Remember me</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </button>
                        </form>

                        <div class="text-center my-4">
                            <div class="d-flex align-items-center">
                                <hr class="flex-grow-1">
                                <span class="mx-3 text-muted small text-uppercase">Or continue with</span>
                                <hr class="flex-grow-1">
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <button onclick="socialLogin('google')" class="btn btn-outline-danger px-3 py-2 flex-grow-1"
                                title="Continue with Google">
                                <i class="fab fa-google"></i> Google
                            </button>
                        </div>


                        <hr>

                        <p class="text-center">
                            Don't have an account?
                            <a href="/register" class="text-primary fw-bold">Register Now</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function socialLogin(provider) {
        const roleElement = document.querySelector('input[name="login_role"]:checked');
        const role = roleElement ? roleElement.value : 'student';
        window.location.href = '/social-login/' + provider + '?role=' + role;
    }
</script>

<style>
    .btn-check:checked+.btn-outline-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border-color: #007bff;
    }

    .btn-check:checked+.btn-outline-success {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        color: white;
        border-color: #28a745;
    }

    .btn-outline-primary,
    .btn-outline-success {
        min-width: 90px;
        padding: 15px 10px;
    }
</style>

<?php require_once 'includes/footer.php'; ?>