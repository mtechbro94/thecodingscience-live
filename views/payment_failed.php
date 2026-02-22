<?php
// views/payment_failed.php

if (!is_logged_in()) {
    redirect('/login');
}

$user = current_user();
$enrollment_id = $_GET['enrollment_id'] ?? 0;
$error = $_GET['error'] ?? 'Payment was not completed';

// Update enrollment status to failed if exists
if ($enrollment_id > 0) {
    $stmt = $pdo->prepare("UPDATE enrollments SET status = 'failed' WHERE id = ? AND user_id = ?");
    $stmt->execute([$enrollment_id, $user['id']]);
}

$page_title = "Payment Failed";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-3">Payment Failed</h2>
                        <p class="text-muted mb-4">Sorry, your payment could not be processed.</p>
                        
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                        
                        <p class="text-muted small">Please try again or contact support if the problem persists.</p>
                        
                        <div class="d-grid gap-2">
                            <a href="/dashboard" class="btn btn-primary btn-lg">Go to Dashboard</a>
                            <a href="/contact" class="btn btn-outline-secondary">Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
