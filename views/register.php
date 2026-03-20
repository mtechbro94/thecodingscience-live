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
            <div class="col-md-6">
                <div class="card shadow border-0" style="border-radius: 20px;">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Create Your Account</h2>
                            <p class="text-muted">Join our global learning community</p>
                        </div>

                        <form id="registerForm">
                            <?php
                            $role_param = $_GET['role'] ?? 'student';
                            if (!in_array($role_param, ['student', 'trainer'])) $role_param = 'student';
                            ?>

                            <!-- Role Selection -->
                            <div class="mb-4 text-center">
                                <label class="form-label text-muted small text-uppercase fw-bold mb-3">Register As</label>
                                <div class="d-flex justify-content-center gap-3">
                                    <input type="radio" class="btn-check" name="register_role" id="roleStudent" value="student" <?php echo ($role_param !== 'trainer') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary px-4 py-3" for="roleStudent">
                                        <i class="fas fa-user-graduate fa-lg d-block mb-2"></i>
                                        Student
                                    </label>

                                    <input type="radio" class="btn-check" name="register_role" id="roleTrainer" value="trainer" <?php echo ($role_param === 'trainer') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-success px-4 py-3" for="roleTrainer">
                                        <i class="fas fa-chalkboard-teacher fa-lg d-block mb-2"></i>
                                        Trainer
                                    </label>
                                </div>
                            </div>

                            <div class="alert alert-light border text-center mb-4" style="font-size: 0.9rem; border-radius: 12px;">
                                <i class="fas fa-user-shield text-success me-2"></i>
                                Secure registration powered by Google.
                            </div>

                            <button type="button" onclick="socialLogin('google')" class="btn btn-danger btn-lg w-100 py-3 shadow-sm" style="border-radius: 12px; font-weight: 600;">
                                <i class="fab fa-google me-2"></i> Register with Google
                            </button>

                            <p class="text-muted small text-center mt-4">
                                By registering, you agree to our <a href="/terms" class="text-decoration-none">Terms & Conditions</a>.
                            </p>
                        </form>

                        <hr class="my-5">
                        <p class="text-center">Already have an account? <a href="/login" class="text-primary fw-bold text-decoration-none">Sign In Here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
        border-radius: 15px;
        transition: all 0.2s;
        min-width: 120px;
    }
    .btn-outline-primary:hover, .btn-outline-success:hover {
        transform: translateY(-2px);
    }
</style>

<script>
    function socialLogin(provider) {
        const role = document.querySelector('input[name="register_role"]:checked').value;
        window.location.href = '/social-login/' + provider + '?role=' + role;
    }
</script>

<?php require_once 'includes/footer.php'; ?>