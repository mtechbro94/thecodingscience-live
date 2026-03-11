<?php
// views/auth_selection.php
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-8">
                <h2 class="display-4 fw-bold mb-3">Join The Coding Science</h2>
                <p class="lead text-muted">Select your path to continue with our platform.</p>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Student Path -->
            <div class="col-md-5">
                <div class="card h-100 border-0 shadow-lg path-card student-path overflow-hidden">
                    <div class="path-icon-bg">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="card-body p-5 position-relative">
                        <div class="mb-4">
                            <span class="badge bg-primary px-3 py-2 mb-3">For Learners</span>
                            <h3 class="fw-bold">Student Portal</h3>
                            <p class="text-muted">Master industry-standard technologies, work on live projects, and
                                launch your career in tech.</p>
                        </div>

                        <div class="d-grid gap-3">
                            <a href="/login?role=student" class="btn btn-primary btn-lg py-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Student Login
                            </a>
                            <a href="/register?role=student" class="btn btn-outline-primary btn-lg py-3">
                                <i class="fas fa-user-plus me-2"></i> Create Student Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trainer Path -->
            <div class="col-md-5">
                <div class="card h-100 border-0 shadow-lg path-card trainer-path overflow-hidden">
                    <div class="path-icon-bg">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="card-body p-5 position-relative">
                        <div class="mb-4">
                            <span class="badge bg-success px-3 py-2 mb-3">For Professionals</span>
                            <h3 class="fw-bold">Trainer Portal</h3>
                            <p class="text-muted">Share your expertise, mentor global talent, and contribute to the next
                                generation of developers.</p>
                        </div>

                        <div class="d-grid gap-3">
                            <a href="/login?role=trainer" class="btn btn-success btn-lg py-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Trainer Login
                            </a>
                            <a href="/register?role=trainer" class="btn btn-outline-success btn-lg py-3">
                                <i class="fas fa-paper-plane me-2"></i> Apply as Trainer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .path-card {
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border-radius: 20px;
    }

    .path-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15) !important;
    }

    .path-icon-bg {
        position: absolute;
        top: -20px;
        right: -20px;
        font-size: 15rem;
        opacity: 0.03;
        transform: rotate(-15deg);
        pointer-events: none;
    }

    .student-path {
        background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);
        border-bottom: 5px solid #0d6efd !important;
    }

    .trainer-path {
        background: linear-gradient(135deg, #ffffff 0%, #f1faf1 100%);
        border-bottom: 5px solid #198754 !important;
    }

    .btn-lg {
        font-weight: 600;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.95rem;
    }
</style>

<?php require_once 'includes/footer.php'; ?>