<?php
// views/razorpay_payment.php - Razorpay Inline Checkout

if (!is_logged_in()) {
    redirect('/login');
}

$user = current_user();
$enrollment_id = isset($id) ? (int) $id : 0;

if ($enrollment_id === 0) {
    redirect('/dashboard');
}

// Fetch Enrollment with Course Details
$stmt = $pdo->prepare("
    SELECT e.*, c.name as course_name, c.price as course_price 
    FROM enrollments e 
    LEFT JOIN courses c ON e.course_id = c.id 
    WHERE e.id = ? AND e.user_id = ?
");
$stmt->execute([$enrollment_id, $user['id']]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    set_flash('danger', 'Enrollment not found.');
    redirect('/dashboard');
}

// For career tracks/combos with NULL course_id, get amount from amount_paid
if (empty($enrollment['course_name'])) {
    $enrollment['course_name'] = 'Career Track Bundle';
    $enrollment['course_price'] = $enrollment['amount_paid'];
}

if ($enrollment['status'] === 'completed') {
    set_flash('info', 'You are already enrolled in this course.');
    redirect('/dashboard');
}

// Create a unique receipt ID for this order
$receipt_id = 'TCS_' . $enrollment_id . '_' . time();
$amount_in_paise = (int)($enrollment['course_price'] * 100);

// Store receipt ID for verification later
$stmt = $pdo->prepare("UPDATE enrollments SET razorpay_order_id = ? WHERE id = ?");
$stmt->execute([$receipt_id, $enrollment_id]);

$page_title = "Complete Payment";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white p-4 text-center">
                        <h2 class="mb-0"><i class="fas fa-credit-card me-2"></i> Complete Payment</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h5 class="text-muted">Course Enrollment</h5>
                            <h4 class="mb-3"><?php echo htmlspecialchars($enrollment['course_name']); ?></h4>
                            <h2 class="text-primary">₹<?php echo number_format($enrollment['course_price'], 2); ?></h2>
                        </div>

                        <hr>

                        <!-- Razorpay Checkout Button -->
                        <div class="mb-3">
                            <h6 class="mb-3">Pay using:</h6>
                            <button id="razorpay-btn" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-mobile-alt me-2"></i> Pay Now with UPI / Card / Net Banking
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <p class="text-muted small mb-0">
                                <i class="fas fa-lock me-1"></i> Secured by Razorpay
                            </p>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded">
                            <p class="small mb-2"><strong>Payment will be processed via:</strong></p>
                            <div class="d-flex justify-content-between small text-muted flex-wrap">
                                <span><i class="fas fa-university"></i> Net Banking</span>
                                <span><i class="fas fa-credit-card"></i> Debit/Credit Card</span>
                                <span><i class="fas fa-wallet"></i> UPI (GPay/PhonePe)</span>
                                <span><i class="fas fa-wallet"></i> Wallet</span>
                            </div>
                        </div>

                        <hr>

                        <!-- Alternative: Manual Payment -->
                        <div class="text-center">
                            <p class="text-muted small">Having trouble with online payment?</p>
                            <a href="/submit-payment/<?php echo $enrollment_id; ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-qrcode me-1"></i> Pay via UPI / Bank Transfer
                            </a>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="/dashboard" class="text-muted">Cancel and go back</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('razorpay-btn').addEventListener('click', function() {
    var options = {
        key: "<?php echo RAZORPAY_KEY_ID; ?>",
        amount: "<?php echo $amount_in_paise; ?>",
        currency: "INR",
        name: "<?php echo addslashes(SITE_NAME); ?>",
        description: "Course: <?php echo addslashes($enrollment['course_name']); ?>",
        image: "<?php 
            $logo = get_setting('site_logo', '');
            echo !empty($logo) ? '/assets/images/' . $logo : '/assets/images/logo.jpeg'; 
        ?>",
        receipt: "<?php echo $receipt_id; ?>",
        handler: function(response) {
            // Payment successful
            // Submit payment details to server for verification
            var formData = new FormData();
            formData.append('enrollment_id', '<?php echo $enrollment_id; ?>');
            formData.append('razorpay_payment_id', response.razorpay_payment_id);
            formData.append('razorpay_order_id', response.razorpay_order_id);
            formData.append('razorpay_signature', response.razorpay_signature);
            
            fetch('/verify-payment', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = '/payment-success?enrollment_id=<?php echo $enrollment_id; ?>';
                } else {
                    alert('Payment verification failed: ' + data.message);
                    window.location.href = '/dashboard';
                }
            })
            .catch(err => {
                alert('Payment processed but verification failed. Contact support.');
                window.location.href = '/dashboard';
            });
        },
        prefill: {
            name: "<?php echo addslashes($user['name']); ?>",
            email: "<?php echo addslashes($user['email']); ?>"
        },
        theme: {
            color: "#007bff"
        },
        modal: {
            ondismiss: function() {
                // User closed the payment modal
                console.log('Payment modal closed');
            }
        }
    };
    
    var rzp1 = new Razorpay(options);
    rzp1.open();
});

rzp1.on('payment.failed', function(response) {
    // Payment failed
    alert('Payment failed: ' + response.error.description);
    // Redirect to manual payment
    window.location.href = '/submit-payment/<?php echo $enrollment_id; ?>';
});
</script>

<?php require_once 'includes/footer.php'; ?>
