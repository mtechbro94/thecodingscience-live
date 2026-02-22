<?php
// admin/dashboard.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';




// Check Admin Access
if (!is_admin()) {
    redirect('/');
}

$page_title = "Dashboard";
require_once __DIR__ . '/includes/header.php';


// Fetch Stats
$stats = [];

// Total Users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['users'] = $stmt->fetchColumn();

// Total Courses
$stmt = $pdo->query("SELECT COUNT(*) FROM courses");
$stats['courses'] = $stmt->fetchColumn();

// Total Enrollments
$stmt = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'completed'");
$stats['enrollments'] = $stmt->fetchColumn();

// Pending Approvals (Trainers)
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'trainer' AND is_approved = 0");
$stats['pending_trainers'] = $stmt->fetchColumn();

// Total Revenue
$stmt = $pdo->query("SELECT SUM(amount_paid) FROM enrollments WHERE status = 'completed'");
$stats['revenue'] = $stmt->fetchColumn() ?: 0;

// Recent Enrollments
$stmt = $pdo->query("
    SELECT e.*, u.name as user_name, c.name as course_name 
    FROM enrollments e 
    JOIN users u ON e.user_id = u.id 
    JOIN courses c ON e.course_id = c.id 
    ORDER BY e.enrolled_at DESC 
    LIMIT 5
");
$recent_enrollments = $stmt->fetchAll();

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
            <span data-feather="calendar"></span>
            This week
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Users</h6>
                        <h2 class="mb-0">
                            <?php echo $stats['users']; ?>
                        </h2>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Revenue</h6>
                        <h2 class="mb-0">₹
                            <?php echo number_format($stats['revenue']); ?>
                        </h2>
                    </div>
                    <i class="fas fa-rupee-sign fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Enrollments</h6>
                        <h2 class="mb-0">
                            <?php echo $stats['enrollments']; ?>
                        </h2>
                    </div>
                    <i class="fas fa-graduation-cap fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Pending Trainers</h6>
                        <h2 class="mb-0">
                            <?php echo $stats['pending_trainers']; ?>
                        </h2>
                    </div>
                    <i class="fas fa-user-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Enrollments</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_enrollments as $enrollment): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($enrollment['user_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($enrollment['course_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($enrollment['status'] === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>₹
                                        <?php echo number_format($enrollment['amount_paid'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_enrollments)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-3">No recent enrollments found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="/admin/enrollments" class="btn btn-sm btn-outline-primary w-100 mt-2">View All Enrollments</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/admin/courses/create" class="btn btn-outline-primary">
                        <i class="fas fa-plus-circle me-2"></i> Add New Course
                    </a>
                    <a href="/admin/users/create" class="btn btn-outline-secondary">
                        <i class="fas fa-user-plus me-2"></i> Add User
                    </a>
                    <a href="/admin/messages" class="btn btn-outline-info">
                        <i class="fas fa-envelope me-2"></i> View Messages
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>