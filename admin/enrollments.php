<?php
// admin/enrollments.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';




if (!is_admin()) {
    redirect('/');
}

$page_title = "Enrollments";

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $enrollment_id = (int) $_GET['id'];

    if ($action === 'verify_payment') {
        $stmt = $pdo->prepare("UPDATE enrollments SET status = 'completed', verified_at = NOW() WHERE id = ?");
        $stmt->execute([$enrollment_id]);

        // You would typically send an email here

        set_flash('success', 'Payment verified and enrollment completed.');
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
        $stmt->execute([$enrollment_id]);
        set_flash('success', 'Enrollment deleted successfully.');
    }
    redirect('/admin/enrollments');
}

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Fetch Enrollments
$stmt = $pdo->prepare("
    SELECT e.*, u.name as user_name, u.email as user_email, c.name as course_name 
    FROM enrollments e 
    JOIN users u ON e.user_id = u.id 
    JOIN courses c ON e.course_id = c.id 
    ORDER BY e.enrolled_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $per_page, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$enrollments = $stmt->fetchAll();

// Total Enrollments
$total_enrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$total_pages = ceil($total_enrollments / $per_page);

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Enrollment Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary">Export CSV</button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td>
                                <?php echo $enrollment['id']; ?>
                            </td>
                            <td>
                                <div class="fw-bold">
                                    <?php echo htmlspecialchars($enrollment['user_name']); ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($enrollment['user_email']); ?>
                                </small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($enrollment['course_name']); ?>
                            </td>
                            <td>₹
                                <?php echo number_format($enrollment['amount_paid'], 2); ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo strtoupper($enrollment['payment_method']); ?>
                                </span>
                                <?php if ($enrollment['utr']): ?>
                                    <div class="mt-1">
                                        <span class="badge bg-info text-dark">UTR:
                                            <?php echo htmlspecialchars($enrollment['utr']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($enrollment['payment_id']): ?>
                                    <div class="small text-muted mt-1" title="Payment ID">
                                        <i class="fas fa-receipt"></i>
                                        <?php echo substr($enrollment['payment_id'], 0, 10) . '...'; ?>
                                    </div>
                                <?php endif; ?>

                            </td>
                            <td>
                                <?php if ($enrollment['status'] === 'completed'): ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php elseif ($enrollment['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending verification</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst($enrollment['status']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($enrollment['status'] === 'pending'): ?>
                                        <a href="/admin/enrollments?action=verify_payment&id=<?php echo $enrollment['id']; ?>"
                                            class="btn btn-success" title="Verify Payment">
                                            <i class="fas fa-check-double"></i> Verify
                                        </a>
                                    <?php endif; ?>
                                    <a href="/admin/enrollments?action=delete&id=<?php echo $enrollment['id']; ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this enrollment?');"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>