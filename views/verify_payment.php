<?php
// views/verify_payment.php - Verify Razorpay Payment

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

$user = current_user();

$enrollment_id = $_POST['enrollment_id'] ?? 0;
$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
$razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
$razorpay_signature = $_POST['razorpay_signature'] ?? '';

if (empty($enrollment_id) || empty($razorpay_payment_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing payment details']);
    exit;
}

// Verify enrollment belongs to user
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE id = ? AND user_id = ?");
$stmt->execute([$enrollment_id, $user['id']]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    echo json_encode(['status' => 'error', 'message' => 'Enrollment not found']);
    exit;
}

// Verify the Razorpay signature (critical security check)
if (!empty($razorpay_order_id) && !empty($razorpay_signature)) {
    $signature_data = $razorpay_order_id . '|' . $razorpay_payment_id;
    $expected_signature = hash_hmac('sha256', $signature_data, RAZORPAY_KEY_SECRET);

    if ($razorpay_signature !== $expected_signature) {
        error_log("Razorpay signature mismatch for enrollment $enrollment_id. Expected: $expected_signature, Got: $razorpay_signature");
        echo json_encode(['status' => 'error', 'message' => 'Payment verification failed. Signature mismatch. Please contact support.']);
        exit;
    }
}

// Payment successful - update enrollment
$stmt = $pdo->prepare("UPDATE enrollments SET 
    status = 'completed', 
    razorpay_payment_id = ?, 
    razorpay_signature = ?,
    payment_method = 'razorpay',
    verified_at = NOW() 
    WHERE id = ?");
$stmt->execute([$razorpay_payment_id, $razorpay_signature, $enrollment_id]);

echo json_encode(['status' => 'success', 'message' => 'Payment verified successfully']);
exit;
