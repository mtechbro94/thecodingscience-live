<?php
// views/payment_callback.php - Verify Razorpay Payment

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user = current_user();

header('Content-Type: application/json');

$enrollment_id = $_POST['enrollment_id'] ?? 0;
$razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
$razorpay_signature = $_POST['razorpay_signature'] ?? '';

if (empty($enrollment_id) || empty($razorpay_order_id) || empty($razorpay_payment_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
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

// Verify signature
$generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, RAZORPAY_KEY_SECRET);

if ($generated_signature !== $razorpay_signature) {
    // Mark as failed
    $stmt = $pdo->prepare("UPDATE enrollments SET status = 'failed', razorpay_payment_id = ?, razorpay_signature = ? WHERE id = ?");
    $stmt->execute([$razorpay_payment_id, $razorpay_signature, $enrollment_id]);
    
    echo json_encode(['status' => 'error', 'message' => 'Invalid payment signature']);
    exit;
}

// Payment successful - update enrollment
$stmt = $pdo->prepare("UPDATE enrollments SET 
    status = 'completed', 
    razorpay_payment_id = ?, 
    razorpay_signature = ?,
    verified_at = NOW() 
    WHERE id = ?");
$stmt->execute([$razorpay_payment_id, $razorpay_signature, $enrollment_id]);

echo json_encode(['status' => 'success', 'message' => 'Payment verified successfully']);
exit;
