<?php
// views/register.php

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/dashboard');
}

$page_title = "Register";

// Handle Registration Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['register_role'] ?? 'student';
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Trainer specific fields
    $education = sanitize($_POST['education'] ?? '');
    $experience = sanitize($_POST['experience'] ?? '');
    $expertise = sanitize($_POST['expertise'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');

    $errors = [];

    // Validation
    if (empty($name))
        $errors[] = "Full Name is required";
    if (empty($email))
        $errors[] = "Email is required";
    if (empty($password))
        $errors[] = "Password is required";
    if ($password !== $confirm_password)
        $errors[] = "Passwords do not match";
    if (strlen($password) < 6)
        $errors[] = "Password must be at least 6 characters";

    if ($role === 'trainer') {
        if (empty($education))
            $errors[] = "Education is required for trainers";
        if (empty($experience))
            $errors[] = "Experience is required for trainers";
        if (empty($expertise))
            $errors[] = "Expertise is required for trainers";
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email is already registered";
    }

    if (empty($errors)) {
        // Hash Password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            if ($role === 'trainer') {
                // Register Trainer (Needs approval)
                $sql = "INSERT INTO users (name, email, phone, password_hash, role, education, experience, expertise, bio, is_approved, created_at) 
                        VALUES (?, ?, ?, ?, 'trainer', ?, ?, ?, ?, 0, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $email, $phone, $password_hash, $education, $experience, $expertise, $bio]);

                set_flash('success', 'Trainer registration successful! Your account is pending approval.');
            } else {
                // Register Student
                $sql = "INSERT INTO users (name, email, phone, password_hash, role, created_at) VALUES (?, ?, ?, ?, 'student', NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $email, $phone, $password_hash]);

                set_flash('success', 'Registration successful! Please login.');
            }
            redirect('/login');
        } catch (PDOException $e) {
            set_flash('danger', 'Registration failed: ' . $e->getMessage());
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
            <div class="col-md-7">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title mb-4 text-center">Create Your Account</h2>
                        <p class="text-muted text-center mb-4">Join our learning community</p>

                        <form method="POST" action="">
                            <!-- Role Selection -->
                            <div class="mb-4">
                                <label class="form-label text-center d-block mb-3"><i class="fas fa-user-tag"></i>
                                    Register As</label>
                                <div class="d-flex justify-content-center gap-3">
                                    <input type="radio" class="btn-check" name="register_role" id="roleStudent"
                                        value="student" checked onchange="toggleTrainerFields()">
                                    <label class="btn btn-outline-primary px-4 py-3" for="roleStudent">
                                        <i class="fas fa-user-graduate fa-2x d-block mb-2"></i>
                                        Student
                                    </label>

                                    <input type="radio" class="btn-check" name="register_role" id="roleTrainer"
                                        value="trainer" onchange="toggleTrainerFields()">
                                    <label class="btn btn-outline-success px-4 py-3" for="roleTrainer">
                                        <i class="fas fa-chalkboard-teacher fa-2x d-block mb-2"></i>
                                        Trainer
                                    </label>
                                </div>
                            </div>

                            <hr>

                            <h5 class="mb-3"><i class="fas fa-user"></i> Basic Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="name" required placeholder="John Doe"
                                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="email" required
                                        placeholder="your@email.com"
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" name="phone" required
                                        placeholder="+91 98765 43210"
                                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" class="form-control" name="password" required
                                        placeholder="Minimum 6 characters">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" name="confirm_password" required
                                    placeholder="Re-enter password">
                            </div>

                            <!-- Trainer Specific Fields -->
                            <div id="trainerFields" style="display: none;">
                                <hr>
                                <h5 class="mb-3"><i class="fas fa-chalkboard-teacher"></i> Trainer Information</h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Trainer accounts require admin approval before
                                    you can access the full dashboard.
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Educational Qualification *</label>
                                        <input type="text" class="form-control" name="education"
                                            placeholder="e.g., B.Tech in Computer Science">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Years of Experience *</label>
                                        <input type="number" class="form-control" name="experience" min="0" max="50"
                                            placeholder="e.g., 5">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Areas of Expertise *</label>
                                    <input type="text" class="form-control" name="expertise"
                                        placeholder="e.g., Python, Data Science, Machine Learning">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Short Bio</label>
                                    <textarea class="form-control" name="bio" rows="3"
                                        placeholder="Tell us about yourself..."></textarea>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="agree_terms" id="agreeTerms"
                                    required>
                                <label class="form-check-label" for="agreeTerms">I agree to the Terms and
                                    Conditions</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3" id="submitBtn">
                                <i class="fas fa-user-plus"></i> <span id="submitText">Create Student Account</span>
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
                        <p class="text-center">Already have an account? <a href="/login"
                                class="text-primary fw-bold">Sign In Here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
</style>

<script>
    function socialLogin(provider) {
        const role = document.querySelector('input[name="register_role"]:checked').value;
        window.location.href = '/social-login/' + provider + '?role=' + role;
    }

    function toggleTrainerFields() {
        const isTrainer = document.getElementById('roleTrainer').checked;
        const trainerFields = document.getElementById('trainerFields');
        const submitText = document.getElementById('submitText');

        if (isTrainer) {
            trainerFields.style.display = 'block';
            submitText.textContent = 'Register as Trainer';
            document.getElementsByName('education')[0].required = true;
            document.getElementsByName('experience')[0].required = true;
            document.getElementsByName('expertise')[0].required = true;
        } else {
            trainerFields.style.display = 'none';
            submitText.textContent = 'Create Student Account';
            document.getElementsByName('education')[0].required = false;
            document.getElementsByName('experience')[0].required = false;
            document.getElementsByName('expertise')[0].required = false;
        }
    }

    // Run on load to set correct state (e.g. if page reloads after error)
    document.addEventListener('DOMContentLoaded', toggleTrainerFields);
</script>

<?php require_once 'includes/footer.php'; ?>