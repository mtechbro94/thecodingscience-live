<?php
// views/register.php

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/dashboard');
}

$page_title = "Register";

// Handle Registration Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Block all traditional registrations
    set_flash('danger', 'Registration is currently only available via Google.');
    redirect('/register');
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

                        <div class="text-center mb-5">
                            <div class="alert alert-info py-4">
                                <h4 class="alert-heading"><i class="fas fa-user-shield"></i> Secure Registration</h4>
                                <p class="mb-0">To ensure the highest security for our students and trainers, we only support registration through <strong>Google</strong>.</p>
                            </div>
                        </div>

                        <form method="POST" action="" id="registerForm">
                            <?php
                            $role_param = $_GET['role'] ?? '';
                            if (!in_array($role_param, ['student', 'trainer']))
                                $role_param = 'student';
                            ?>

                            <!-- Role Selection -->
                            <div class="mb-4">
                                <label class="form-label text-center d-block mb-3"><i class="fas fa-user-tag"></i>
                                    Register As</label>
                                <div class="d-flex justify-content-center gap-3">
                                    <input type="radio" class="btn-check" name="register_role" id="roleStudent"
                                        value="student" <?php echo ($role_param === 'trainer') ? '' : 'checked'; ?>>
                                    <label class="btn btn-outline-primary px-4 py-3" for="roleStudent">
                                        <i class="fas fa-user-graduate fa-2x d-block mb-2"></i>
                                        Student
                                    </label>

                                    <input type="radio" class="btn-check" name="register_role" id="roleTrainer"
                                        value="trainer" <?php echo ($role_param === 'trainer') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-success px-4 py-3" for="roleTrainer">
                                        <i class="fas fa-chalkboard-teacher fa-2x d-block mb-2"></i>
                                        Trainer
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-3 mt-5">
                                <button type="button" onclick="socialLogin('google')" class="btn btn-danger btn-lg py-3 shadow-sm">
                                    <i class="fab fa-google me-2"></i> Register with Google
                                </button>
                            </div>

                            <p class="text-muted small text-center mt-3">
                                By registering, you agree to our <a href="/terms" class="text-decoration-none">Terms & Conditions</a>.
                            </p>
                        </form>

                        <hr class="my-5">
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
        const roleElement = document.querySelector('input[name="register_role"]:checked');
        const role = roleElement ? roleElement.value : 'student';
        window.location.href = '/social-login/' + provider + '?role=' + role;
    }
</script>

<?php require_once 'includes/footer.php'; ?>