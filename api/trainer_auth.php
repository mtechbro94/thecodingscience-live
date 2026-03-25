<?php
// api/trainer_auth.php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = 'trainer';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

if ($action === 'send_otp') {
    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Password is required.']);
        exit;
    }

    // Check if trainer already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    $type = 'signup';
    if ($user) {
        $type = 'login';
        // Check password for existing user
        if (!password_verify($password, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Incorrect password for trainer account.']);
            exit;
        }
    }

    // Generate OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $password_hash = ($type === 'signup') ? password_hash($password, PASSWORD_DEFAULT) : null;

    // Store OTP
    $stmt = $pdo->prepare("DELETE FROM trainer_otps WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);

    $stmt = $pdo->prepare("INSERT INTO trainer_otps (email, role, otp_code, password_hash, type, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$email, $role, $otp, $password_hash, $type, $expires_at]);

    // Send Email
    $subject = "Your Verification Code - " . SITE_NAME;
    $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
            <h2 style='color: #4f46e5; text-align: center;'>Verification Code</h2>
            <p>Hello,</p>
            <p>Your verification code for " . SITE_NAME . " is:</p>
            <div style='background: #f8fafc; padding: 20px; text-align: center; font-size: 32px; font-weight: 800; letter-spacing: 5px; color: #0f172a; border-radius: 8px; margin: 20px 0;'>
                {$otp}
            </div>
            <p style='color: #64748b; font-size: 14px;'>This code will expire in 10 minutes. If you didn't request this code, please ignore this email.</p>
            <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
            <p style='text-align: center; color: #94a3b8; font-size: 12px;'>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
        </div>
    ";

    if (send_email($email, $subject, $message)) {
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully to your email.', 'type' => $type]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
    }

} elseif ($action === 'verify_otp') {
    $otp = sanitize($_POST['otp'] ?? '');

    if (empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'OTP is required.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM trainer_otps WHERE email = ? AND role = ? AND otp_code = ? AND expires_at > NOW()");
    $stmt->execute([$email, $role, $otp]);
    $otp_record = $stmt->fetch();

    if (!$otp_record) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP.']);
        exit;
    }

    // Success! Process login or signup
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if ($otp_record['type'] === 'signup' && !$user) {
        // Create user
        $name = explode('@', $email)[0]; // Default name from email
        $sql = "INSERT INTO users (name, email, role, password_hash, is_approved, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $role, $otp_record['password_hash'], 0, 1]);
        
        $user_id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    }

    if ($user) {
        // Log in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_profile_image'] = $user['profile_image'];
        $_SESSION['is_approved'] = $user['is_approved'] ?? 1;

        session_regenerate_id(true);

        // Clean up OTPs
        $stmt = $pdo->prepare("DELETE FROM trainer_otps WHERE email = ? AND role = ?");
        $stmt->execute([$email, $role]);

        echo json_encode(['success' => true, 'message' => 'Authentication successful!', 'redirect' => '/dashboard']);
    } else {
        echo json_encode(['success' => false, 'message' => 'User account could not be found or created.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
