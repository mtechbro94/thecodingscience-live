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
                        <?php if ($user['profile_image']): ?>
                            <img src="<?php echo get_avatar($user); ?>" alt="Profile" class="rounded-circle mb-3 shadow-sm"
                                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #f8f9fa;">
                        <?php else: ?>
                            <div class="avatar-circle avatar-circle-md bg-success mx-auto mb-3 shadow-sm">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
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
                        <a href="/trainer-blogs" class="list-group-item list-group-item-action">
                            <i class="fas fa-blog me-2"></i> My Blogs
                        </a>
                        <a href="/dashboard" class="list-group-item list-group-item-action">
                            <i class="fas fa-graduation-cap me-2 text-primary"></i> Learning Dashboard
                        </a>
                        <a href="/trainer-blog/new" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-square me-2"></i> Write New Blog
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

                <?php
                // Fetch stats for the trainer
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE trainer_id = ?");
                $stmt->execute([$user['id']]);
                $course_count = $stmt->fetchColumn();

                $stmt = $pdo->prepare("SELECT COUNT(e.id) FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.trainer_id = ? AND e.status = 'completed'");
                $stmt->execute([$user['id']]);
                $student_count = $stmt->fetchColumn();
                ?>

                <!-- Stats Overview -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success text-white shadow-sm border-0 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">My Courses</h6>
                                        <h2 class="mt-2 mb-0"><?php echo $course_count; ?></h2>
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
                                        <h2 class="mt-2 mb-0"><?php echo $student_count; ?></h2>
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

                <!-- My Courses Section -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-chalkboard-teacher me-2 text-success"></i> My Assigned Courses</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Fetch courses assigned to this trainer
                        $stmt = $pdo->prepare("SELECT * FROM courses WHERE trainer_id = ? ORDER BY created_at DESC");
                        $stmt->execute([$user['id']]);
                        $trainer_courses = $stmt->fetchAll();

                        if (empty($trainer_courses)):
                        ?>
                            <div class="text-center py-5">
                                <i class="fas fa-book-open fa-3x text-muted opacity-25 mb-3"></i>
                                <p class="text-muted">You are not currently assigned to any courses.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Course Details</th>
                                            <th>Batch Timing</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($trainer_courses as $course): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($course['image']): ?>
                                                            <img src="<?php echo get_image_url($course['image']); ?>" alt="" class="rounded me-3" style="width: 45px; height: 45px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="bg-success-subtle text-success p-2 rounded me-3">
                                                                <i class="fas fa-code"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($course['name']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($course['level']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="fas fa-clock text-primary me-1"></i>
                                                        <?php echo htmlspecialchars($course['batch_timing'] ?: 'Not Set'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($course['live_link']): ?>
                                                        <span class="badge bg-success-subtle text-success">Link Set</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-subtle text-warning">No Link</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="/trainer-manage-course/<?php echo $course['id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                                        <i class="fas fa-cog me-1"></i> Manage Class
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>