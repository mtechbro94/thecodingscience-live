<?php
// views/submit_payment.php

if (!is_logged_in()) {
    redirect('/login');
}

$user = current_user();
$enrollment_id = isset($id) ? (int) $id : 0;

if ($enrollment_id === 0) {
    redirect('/dashboard');
}

// Fetch Enrollment
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

// For career tracks/combos with NULL course_id
if (empty($enrollment['course_name'])) {
    $enrollment['course_name'] = 'Career Track Bundle';
    $enrollment['course_price'] = $enrollment['amount_paid'];
}

if ($enrollment['status'] === 'completed') {
    set_flash('info', 'Your payment is already verified.');
    redirect('/dashboard');
}

// UPI Payment Details
$upi_id = 'u.e1@ybl';
$amount = $enrollment['course_price'];
$note = 'Course: ' . $enrollment['course_name'];

// Generate UPI payment link
$upi_link = 'upi://pay?pa=' . $upi_id . '&pn=' . urlencode('The Coding Science') . '&am=' . $amount . '&tn=' . urlencode($note);

// Generate QR code URL (using free QR API)
$qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($upi_link);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utr = sanitize($_POST['utr'] ?? '');

    if (empty($utr)) {
        $error = "Transaction ID (UTR) is required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE enrollments SET utr = ?, status = 'pending' WHERE id = ?");
            $stmt->execute([$utr, $enrollment_id]);

            set_flash('success', 'Payment details submitted successfully! Our team will verify it soon.');
            redirect('/dashboard');
        } catch (PDOException $e) {
            $error = "Failed to update enrollment: " . $e->getMessage();
        }
    }
}

$page_title = "Submit Payment Details";
require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white p-4 text-center">
                        <h2 class="mb-0"><i class="fas fa-qrcode me-2"></i> Pay via UPI</h2>
                        <p class="mb-0 mt-2">Scan QR Code or Pay Directly</p>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Amount Display -->
                        <div class="text-center mb-4">
                            <p class="text-muted mb-1">Amount to Pay</p>
                            <h1 class="text-success">₹<?php echo number_format($amount, 2); ?></h1>
                            <span class="badge bg-light text-success border"><?php echo $enrollment['course_name']; ?></span>
                        </div>

                        <hr>

                        <!-- QR Code and UPI Details -->
                        <div class="row align-items-center">
                            <div class="col-md-5 text-center mb-4 mb-md-0">
                                <div class="bg-white p-3 rounded shadow-sm d-inline-block">
                                    <img src="<?php echo $qr_code_url; ?>" alt="UPI QR Code" class="img-fluid" style="max-width: 200px;">
                                </div>
                                <p class="small text-muted mt-2">Scan with any UPI app</p>
                            </div>
                            
                            <div class="col-md-7">
                                <h5 class="mb-3"><i class="fas fa-mobile-alt text-success me-2"></i>Pay via UPI App</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">UPI ID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="<?php echo $upi_id; ?>" id="upiId" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyUPI()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="alert alert-light border">
                                    <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i> How to Pay:</h6>
                                    <ol class="mb-0 small">
                                        <li>Open any UPI app (GPay, PhonePe, Paytm, BHIM)</li>
                                        <li>Scan the QR code OR enter UPI ID: <strong><?php echo $upi_id; ?></strong></li>
                                        <li>Enter amount: <strong>₹<?php echo number_format($amount, 2); ?></strong></li>
                                        <li>Complete payment and copy the UTR/Transaction ID</li>
                                        <li>Submit UTR below for verification</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Bank Transfer Option -->
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="fas fa-university text-primary me-2"></i>Bank Transfer (Alternative)</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush small">
                                        <li class="list-group-item"><strong>Bank:</strong> J&K Bank</li>
                                        <li class="list-group-item"><strong>Account:</strong> The Coding Science</li>
                                        <li class="list-group-item"><strong>Account No:</strong> 0622040100004775</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush small">
                                        <li class="list-group-item"><strong>IFSC:</strong> JAKA0KLGUND</li>
                                        <li class="list-group-item"><strong>Branch:</strong> Kralgund</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <!-- UTR Submission Form -->
                        <form method="POST">
                            <div class="mb-4">
                                <label for="utr" class="form-label fw-bold">
                                    <i class="fas fa-hashtag text-primary me-1"></i>Enter UTR / Transaction ID
                                </label>
                                <input type="text" class="form-control form-control-lg" id="utr" name="utr" required
                                    placeholder="e.g. 123456789012" style="letter-spacing: 2px;">
                                <small class="text-muted">Enter the 12-digit UTR number from your payment confirmation.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg shadow">
                                    <i class="fas fa-check-circle me-2"></i> Submit & Verify Payment
                                </button>
                                <a href="/dashboard" class="btn btn-link text-muted">Back to Dashboard</a>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted small mb-2"><i class="fas fa-shield-alt text-success me-1"></i> Payments are secure and encrypted</p>
                            <div class="d-flex justify-content-center gap-3">
                                <span><i class="fab fa-google-pay fa-2x"></i></span>
                                <span><i class="fab fa-phonepe fa-2x"></i></span>
                                <span><i class="fab fa-paytm fa-2x"></i></span>
                                <span><i class="fas fa-university fa-2x"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function copyUPI() {
    var copyText = document.getElementById("upiId");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("UPI ID copied: " + copyText.value);
}
</script>

<?php require_once 'includes/footer.php'; ?>
