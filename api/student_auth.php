<?php
/**
 * Student Authentication API
 * Handles Gmail OAuth login for students
 * 
 * Endpoints:
 * POST /api/student_auth.php?action=verify_gmail
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/SocialAuth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if ($action === 'verify_gmail') {
    
    $access_token = $_POST['access_token'] ?? '';
    $gmail_id = $_POST['gmail_id'] ?? '';
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (!$access_token || !$gmail_id || !$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required OAuth data.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    try {
        $has_gmail_id = table_has_column('users', 'gmail_id');
        $has_username = table_has_column('users', 'username');
        $has_updated_at = table_has_column('users', 'updated_at');

        // Check if student exists
        if ($has_gmail_id) {
            $stmt = $pdo->prepare("
                SELECT id, name, profile_image, is_active, gmail_id
                FROM users
                WHERE role = 'student' AND (gmail_id = ? OR email = ?)
            ");
            $stmt->execute([$gmail_id, $email]);
        } else {
            $stmt = $pdo->prepare("
                SELECT id, name, profile_image, is_active
                FROM users
                WHERE email = ? AND role = 'student'
            ");
            $stmt->execute([$email]);
        }
        $student = $stmt->fetch();

        if ($student) {
            // Existing student - verify they're active and login
            if ($student['is_active'] == 0) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Your account has been deactivated.']);
                exit;
            }

            // Update gmail_id if not set (migration from old system)
            if ($has_gmail_id && empty($student['gmail_id'])) {
                $update_sql = "UPDATE users SET gmail_id = ?";
                if ($has_updated_at) {
                    $update_sql .= ", updated_at = NOW()";
                }
                $update_sql .= " WHERE id = ?";

                $stmt = $pdo->prepare($update_sql);
                $stmt->execute([$gmail_id, $student['id']]);
            }

            $_SESSION['user_id'] = $student['id'];
            $_SESSION['user_name'] = $student['name'];
            $_SESSION['user_role'] = 'student';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_profile_image'] = $student['profile_image'];

            session_regenerate_id(true);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => '/dashboard',
                'is_new' => false
            ]);

        } else {
            // Create new student account
            $username = null;
            if (!empty($name)) {
                $base_name = strtolower(str_replace(' ', '_', $name));
                $username = preg_replace('/[^a-z0-9_]/', '', $base_name);
            }

            // Generate a random password (not used for students, they login via Gmail)
            $random_password = bin2hex(random_bytes(16));
            $password_hash = password_hash($random_password, PASSWORD_DEFAULT);

            $columns = ['email'];
            $placeholders = ['?'];
            $values = [$email];

            if ($has_gmail_id) {
                $columns[] = 'gmail_id';
                $placeholders[] = '?';
                $values[] = $gmail_id;
            }

            if ($has_username) {
                $columns[] = 'username';
                $placeholders[] = '?';
                $values[] = $username;
            }

            $columns = array_merge($columns, ['password_hash', 'name', 'role', 'is_active', 'created_at']);
            $placeholders = array_merge($placeholders, ['?', '?', "'student'", '1', 'NOW()']);
            $values[] = $password_hash;
            $values[] = $name;

            if ($has_updated_at) {
                $columns[] = 'updated_at';
                $placeholders[] = 'NOW()';
            }

            $stmt = $pdo->prepare("
                INSERT INTO users (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")
            ");
            $stmt->execute($values);

            $student_id = $pdo->lastInsertId();

            $_SESSION['user_id'] = $student_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'student';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_profile_image'] = null;

            session_regenerate_id(true);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Account created and logged in successfully!',
                'redirect' => '/dashboard',
                'is_new' => true
            ]);
        }

    } catch (Exception $e) {
        error_log("Student Gmail Auth Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication failed. Please try again later.'
        ]);
    }

} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
