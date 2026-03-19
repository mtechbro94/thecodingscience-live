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
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title mb-4 text-center">Welcome Back</h2>
                        <p class="text-muted text-center mb-4">Sign in to access your dashboard</p>

                        <form method="POST" action="" id="loginForm">
                            <?php
                            $role_param = $_GET['role'] ?? '';
                            if (!in_array($role_param, ['student', 'trainer']))
                                $role_param = '';
                            ?>

                            <!-- Role Selection -->
                            <div class="mb-4" <?php echo $role_param ? 'style="display:none;"' : ''; ?>>
                                <label class="form-label text-center d-block mb-3"><i class="fas fa-user-tag"></i> Login
                                    As</label>
                                <div class="d-flex justify-content-center gap-2">
                                    <input type="radio" class="btn-check" name="login_role" id="roleStudent"
                                        value="student" <?php echo ($role_param === 'trainer') ? '' : 'checked'; ?> onchange="updateLoginForm()">
                                    <label class="btn btn-outline-primary" for="roleStudent">
                                        <i class="fas fa-user-graduate"></i><br>
                                        <small>Student</small>
                                    </label>

                                    <input type="radio" class="btn-check" name="login_role" id="roleTrainer"
                                        value="trainer" <?php echo ($role_param === 'trainer') ? 'checked' : ''; ?> onchange="updateLoginForm()">
                                    <label class="btn btn-outline-success" for="roleTrainer">
                                        <i class="fas fa-chalkboard-teacher"></i><br>
                                        <small>Trainer</small>
                                    </label>
                                </div>
                            </div>

                            <?php if ($role_param): ?>
                                <div class="text-center mb-4">
                                    <h4
                                        class="fw-bold text-<?php echo $role_param === 'trainer' ? 'success' : 'primary'; ?>">
                                        <?php echo ucfirst($role_param); ?> Login
                                    </h4>
                                    <a href="/login" class="small text-muted">Not a <?php echo $role_param; ?>? Change
                                        role</a>
                                </div>
                                <input type="hidden" name="login_role" value="<?php echo $role_param; ?>">
                            <?php endif; ?>

                            <!-- Traditional Login Fields (Only for Admin - Hidden via JS) -->
                            <div id="traditionalFields" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                                    <input type="email" class="form-control" name="email"
                                        placeholder="your@email.com">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                                    <input type="password" class="form-control" name="password"
                                        placeholder="Enter your password">
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="fas fa-sign-in-alt"></i> Sign In
                                </button>
                            </div>

                            <!-- Social Login Message -->
                            <div id="socialOnlyMessage" class="text-center mb-4">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> For security, all <strong>Students</strong> and <strong>Trainers</strong> must sign in using their Google account.
                                </div>
                                <button type="button" onclick="socialLogin('google')" class="btn btn-danger btn-lg w-100 py-3 shadow-sm">
                                    <i class="fab fa-google me-2"></i> Continue with Google
                                </button>
                            </div>
                        </form>

                        <div id="adminLink" class="text-center mt-3">
                            <a href="javascript:void(0)" onclick="showAdminLogin()" class="small text-muted">Admin Login? Click here</a>
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
    function updateLoginForm() {
        const roleElement = document.querySelector('input[name="login_role"]:checked');
        const role = roleElement ? roleElement.value : 'student';
        
        const traditionalFields = document.getElementById('traditionalFields');
        const socialOnlyMessage = document.getElementById('socialOnlyMessage');
        const adminLink = document.getElementById('adminLink');

        if (role === 'admin') {
            traditionalFields.style.display = 'block';
            socialOnlyMessage.style.display = 'none';
            adminLink.style.display = 'none';
        } else {
            traditionalFields.style.display = 'none';
            socialOnlyMessage.style.display = 'block';
            adminLink.style.display = 'block';
        }
    }

    function showAdminLogin() {
        // Create an admin radio button dynamically or just show fields
        const traditionalFields = document.getElementById('traditionalFields');
        const socialOnlyMessage = document.getElementById('socialOnlyMessage');
        const adminLink = document.getElementById('adminLink');
        
        traditionalFields.style.display = 'block';
        socialOnlyMessage.style.display = 'none';
        adminLink.style.display = 'none';
        
        // Add a hidden role input for admin
        let roleInput = document.querySelector('input[name="login_role"][value="admin"]');
        if (!roleInput) {
            const form = document.getElementById('loginForm');
            const hiddenAdmin = document.createElement('input');
            hiddenAdmin.type = 'hidden';
            hiddenAdmin.name = 'login_role';
            hiddenAdmin.value = 'admin';
            form.appendChild(hiddenAdmin);
            
            // Uncheck other radios
            document.querySelectorAll('input[name="login_role"]').forEach(r => {
                if(r.type === 'radio') r.checked = false;
            });
        }
    }

    function socialLogin(provider) {
        const roleElement = document.querySelector('input[name="login_role"]:checked') || document.querySelector('input[name="login_role"][type="hidden"]');
        const role = roleElement ? roleElement.value : 'student';
        window.location.href = '/social-login/' + provider + '?role=' + role;
    }

    // Initial state
    document.addEventListener('DOMContentLoaded', updateLoginForm);
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