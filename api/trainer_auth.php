<?php
/**
 * Trainer Authentication API
 * Handles: Login with OTP verification
 * 
 * Endpoints:
 * POST /api/trainer_auth.php?action=send_otp
 * POST /api/trainer_auth.php?action=verify_otp
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/mail.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Please refresh and try again.']);
    exit;
}

$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

if ($action === 'send_otp') {
    if (!rate_limit_check('trainer_send_otp', 5, 600)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many OTP requests. Please wait a few minutes and try again.']);
        exit;
    }
    
    if (empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password is required.']);
        exit;
    }

    try {
        // Check if trainer exists
        $stmt = $pdo->prepare("SELECT id, password_hash, is_active, otp_verified FROM users WHERE email = ? AND role = 'trainer'");
        $stmt->execute([$email]);
        $trainer = $stmt->fetch();

        if (!$trainer) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Trainer account not found. Please contact the admin team if you should have access.'
            ]);
            exit;
        }

        if ($trainer['is_active'] == 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Your account has been deactivated.']);
            exit;
        }
        
        if (!password_verify($password, $trainer['password_hash'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
            exit;
        }

        // Generate OTP
        $otp_code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ?, otp_verified = 0 WHERE id = ?");
        $stmt->execute([$otp_code, $expires_at, $trainer['id']]);

        // Send OTP email
        if (send_otp_email($email, $otp_code, 'login')) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'OTP sent to your email. Valid for 10 minutes.',
                'action_type' => 'login'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ]);
        }

    } catch (Exception $e) {
        error_log("Trainer OTP Send Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ]);
    }

} elseif ($action === 'verify_otp') {
    if (!rate_limit_check('trainer_verify_otp', 10, 600)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many verification attempts. Please wait a few minutes and try again.']);
        exit;
    }
    
    $otp_code = sanitize($_POST['otp'] ?? '');

    if (empty($otp_code) || strlen($otp_code) !== 6 || !ctype_digit($otp_code)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid OTP format.']);
        exit;
    }

    try {
        // Check existing trainer
        $stmt = $pdo->prepare("
            SELECT id, name, profile_image, is_approved, password_hash 
            FROM users 
            WHERE email = ? AND role = 'trainer' 
            AND otp_code = ? 
            AND otp_expires_at > NOW()
            AND is_active = 1
        ");
        $stmt->execute([$email, $otp_code]);
        $trainer = $stmt->fetch();

        if ($trainer) {
            // Existing trainer - mark OTP as verified and login
            $stmt = $pdo->prepare("UPDATE users SET otp_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
            $stmt->execute([$trainer['id']]);

            $_SESSION['user_id'] = $trainer['id'];
            $_SESSION['user_name'] = $trainer['name'];
            $_SESSION['user_role'] = 'trainer';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_profile_image'] = $trainer['profile_image'];
            $_SESSION['is_approved'] = $trainer['is_approved'] ?? 1;

            session_regenerate_id(true);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Authentication successful!',
                'redirect' => '/trainer-dashboard'
            ]);

        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP.']);
        }

    } catch (Exception $e) {
        error_log("Trainer OTP Verify Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ]);
    }

} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
