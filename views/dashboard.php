<?php
// views/dashboard.php

if (!is_logged_in()) {
    set_flash('danger', 'Please login to access your dashboard.');
    redirect('/login');
}

$user = current_user();

// Redirect Admin
if ($user['role'] === 'admin') {
    redirect('/admin/dashboard');
}
// Trainers can access both, no forced redirect here anymore

$page_title = "Dashboard";

// Fetch Enrollments
$stmt = $pdo->prepare("
    SELECT e.*, c.name as course_name, c.image as course_image, c.price as course_price
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    WHERE e.user_id = ? 
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$user['id']]);
$enrollments = $stmt->fetchAll();

// Count completed enrollments
$completed_count = 0;
foreach ($enrollments as $e) {
    if ($e['status'] === 'completed') {
        $completed_count++;
    }
}

require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <?php if ($user['profile_image']): ?>
                            <img src="<?php echo get_avatar($user); ?>" alt="Profile" class="rounded-circle mb-3 shadow-sm"
                                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #f8f9fa;">
                        <?php else: ?>
                            <div class="avatar-circle avatar-circle-md bg-primary mx-auto mb-3 shadow-sm">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <h5 class="card-title"><?php echo $user['name']; ?></h5>
                        <p class="text-muted small"><?php echo $user['email']; ?></p>
                        <span class="badge bg-secondary"><?php echo ucfirst($user['role']); ?></span>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="/dashboard" class="list-group-item list-group-item-action active">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <?php if ($user['role'] === 'trainer' || $user['role'] === 'admin'): ?>
                            <a href="/trainer-dashboard" class="list-group-item list-group-item-action text-success fw-bold">
                                <i class="fas fa-chalkboard-teacher me-2"></i> Trainer Dashboard
                            </a>
                        <?php endif; ?>
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
                <h2 class="mb-4">My Dashboard</h2>

                <?php if ($user['role'] === 'trainer' && empty($_SESSION['is_approved'])): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Your trainer account is currently pending approval. You
                        will have full access once an administrator approves your account.
                    </div>
                <?php endif; ?>

                <!-- Stats Overview -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Enrolled Courses</h6>
                                        <h2 class="mt-2 mb-0"><?php echo count($enrollments); ?></h2>
                                    </div>
                                    <i class="fas fa-book fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Completed</h6>
                                        <h2 class="mt-2 mb-0"><?php echo $completed_count; ?></h2>
                                    </div>
                                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Certificates</h6>
                                        <h2 class="mt-2 mb-0">0</h2>
                                    </div>
                                    <i class="fas fa-certificate fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Courses -->
                <h4 class="mb-3">My Courses</h4>
                <?php if (empty($enrollments)): ?>
                    <div class="card shadow-sm text-center p-5">
                        <div class="card-body">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <h5>No courses yet</h5>
                            <p class="text-muted">You haven't enrolled in any courses yet.</p>
                            <a href="/courses" class="btn btn-primary">Browse Courses</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($enrollments as $enrollment): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <?php if ($enrollment['course_image']): ?>
                                                <img src="<?php echo get_image_url($enrollment['course_image']); ?>" alt="Course"
                                                    class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                    style="width: 60px; height: 60px;">
                                                    <i class="fas fa-code fa-2x text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h5 class="card-title mb-1"><?php echo $enrollment['course_name']; ?></h5>
                                                <div class="mb-1">
                                                    <?php if ($enrollment['status'] === 'completed'): ?>
                                                        <span class="badge bg-success">Active</span>
                                                        <span class="badge bg-light text-dark border ms-1 small">
                                                            <i class="fas fa-clock text-primary me-1"></i>
                                                            <?php
                                                            // Fetch course timing if not already in result
                                                            $stmt_time = $pdo->prepare("SELECT batch_timing FROM courses WHERE id = ?");
                                                            $stmt_time->execute([$enrollment['course_id']]);
                                                            $course_data = $stmt_time->fetch();
                                                            echo htmlspecialchars($course_data['batch_timing'] ?: 'Full Access');
                                                            ?>
                                                        </span>
                                                    <?php elseif ($enrollment['status'] === 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">Payment Pending</span>
                                                    <?php else: ?>
                                                        <span
                                                            class="badge bg-secondary"><?php echo ucfirst($enrollment['status']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($enrollment['status'] === 'completed'): ?>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                            </div>
                                            <a href="/course-content/<?php echo $enrollment['course_id']; ?>"
                                                class="btn btn-primary btn-sm w-100 shadow-sm fw-bold mb-2">
                                                <i class="fas fa-graduation-cap me-1"></i> Go to Course Dashboard
                                            </a>
                                            <?php if ($course_data['live_link']): ?>
                                                <a href="<?php echo $course_data['live_link']; ?>" target="_blank"
                                                    class="btn btn-success btn-sm w-100 shadow-sm fw-bold">
                                                    <i class="fas fa-video me-1"></i> Join Live Class Now
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif ($enrollment['status'] === 'pending'): ?>
                                            <div class="alert alert-light border border-warning small py-2 mb-3">
                                                <i class="fas fa-clock text-warning"></i>
                                                <?php if ($enrollment['razorpay_payment_id']): ?>
                                                    Payment initiated. Please complete payment.
                                                <?php else: ?>
                                                    Action Required: Please complete payment to activate.
                                                <?php endif; ?>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <a href="/razorpay-payment/<?php echo $enrollment['id']; ?>"
                                                        class="btn btn-warning btn-sm w-100">
                                                        <i class="fas fa-credit-card me-1"></i> Pay Now
                                                    </a>
                                                </div>
                                                <div class="col-6">
                                                    <a href="/course/<?php echo $enrollment['course_id']; ?>"
                                                        class="btn btn-outline-primary btn-sm w-100">Details</a>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <p class="small text-danger mb-2">Status: <?php echo ucfirst($enrollment['status']); ?>
                                            </p>
                                            <a href="/course/<?php echo $enrollment['course_id']; ?>"
                                                class="btn btn-outline-primary btn-sm w-100">View Details</a>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>