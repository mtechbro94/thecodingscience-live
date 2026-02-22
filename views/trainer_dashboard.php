<?php
// views/trainer_dashboard.php

if (!is_logged_in()) {
    set_flash('danger', 'Please login to access your dashboard.');
    redirect('/login');
}

$user = current_user();

// Redirect Non-Trainers
if ($user['role'] !== 'trainer' && $user['role'] !== 'admin') {
    redirect('/dashboard');
}

// If Admin, they can view this but usually they go to admin/dashboard
if ($user['role'] === 'admin' && !isset($_GET['view_as_trainer'])) {
    redirect('/admin/dashboard');
}

$page_title = "Trainer Dashboard";

// Fetch Trainer specific stats (e.g., courses they are teaching or pending approval)
// For now, let's keep it simple and show their profile and status

require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="avatar-circle bg-success text-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                            style="width: 80px; height: 80px; font-size: 2rem; border-radius: 50%;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <h5 class="card-title">
                            <?php echo $user['name']; ?>
                        </h5>
                        <p class="text-muted small">
                            <?php echo $user['email']; ?>
                        </p>
                        <span class="badge bg-success">Trainer</span>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="/trainer-dashboard"
                            class="list-group-item list-group-item-action active bg-success border-success">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="/profile" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i> My Profile
                        </a>
                        <a href="/logout" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Trainer Dashboard</h2>
                </div>

                <?php if (empty($_SESSION['is_approved'])): ?>
                    <div class="alert alert-warning shadow-sm border-0">
                        <div class="d-flex">
                            <i class="fas fa-clock fa-2x me-3"></i>
                            <div>
                                <h5 class="alert-heading">Pending Approval</h5>
                                <p class="mb-0">Your trainer account is currently pending approval from the administration.
                                    You will be able to manage courses and access all trainer features once your account is
                                    verified.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success shadow-sm border-0">
                        <div class="d-flex">
                            <i class="fas fa-check-circle fa-2x me-3"></i>
                            <div>
                                <h5 class="alert-heading">Account Approved</h5>
                                <p class="mb-0">Welcome! Your trainer account is active. You can now start contributing to
                                    the platform.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stats Overview -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success text-white shadow-sm border-0 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">My Courses</h6>
                                        <h2 class="mt-2 mb-0">0</h2>
                                    </div>
                                    <i class="fas fa-chalkboard fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-primary text-white shadow-sm border-0 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Active Students</h6>
                                        <h2 class="mt-2 mb-0">0</h2>
                                    </div>
                                    <i class="fas fa-users fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-info text-white shadow-sm border-0 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Average Rating</h6>
                                        <h2 class="mt-2 mb-0">N/A</h2>
                                    </div>
                                    <i class="fas fa-star fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Messages or Tasks -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Upcoming Tasks</h5>
                    </div>
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5>No tasks for now</h5>
                        <p class="text-muted">When you are assigned to courses, your management panel will appear here.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>