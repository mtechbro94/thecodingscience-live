<?php
// views/payment_success.php

if (!is_logged_in()) {
    redirect('/login');
}

$user = current_user();
$enrollment_id = $_GET['enrollment_id'] ?? 0;

if ($enrollment_id === 0) {
    redirect('/dashboard');
}

// Verify enrollment
$stmt = $pdo->prepare("
    SELECT e.*, c.name as course_name 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    WHERE e.id = ? AND e.user_id = ?
");
$stmt->execute([$enrollment_id, $user['id']]);
$enrollment = $stmt->fetch();

$page_title = "Payment Successful";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-3">Payment Successful!</h2>
                        <p class="text-muted mb-4">Thank you for your payment. You are now enrolled in the course.</p>
                        
                        <?php if ($enrollment): ?>
                            <div class="bg-light p-3 rounded mb-4">
                                <h5><?php echo htmlspecialchars($enrollment['course_name']); ?></h5>
                                <p class="mb-0 text-muted">Transaction ID: <?php echo htmlspecialchars($enrollment['razorpay_payment_id']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2">
                            <a href="/dashboard" class="btn btn-primary btn-lg">Go to Dashboard</a>
                            <a href="/courses" class="btn btn-outline-secondary">Browse More Courses</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
