<?php
// views/social_callback.php - OAuth Callback Handler
// Now only supports Google for student login

require_once 'includes/SocialAuth.php';

$provider = $match[1] ?? '';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

// Only Google is allowed
if ($provider !== 'google') {
    set_flash('danger', 'Invalid OAuth provider.');
    redirect('/student_login');
}

if (empty($code) || $state !== ($_SESSION['oauth_state'] ?? '')) {
    set_flash('danger', 'Invalid authentication request.');
    redirect('/student_login');
}

unset($_SESSION['oauth_state']);

$socialAuth = new SocialAuth();
$redirect_uri = SITE_URL . '/social-callback/' . $provider;

$tokenData = $socialAuth->exchangeCode($provider, $code, $redirect_uri);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    set_flash('danger', 'Failed to obtain access token from Google.');
    redirect('/student_login');
}

$profile = $socialAuth->getUserInfo($provider, $accessToken);

if (!$profile || empty($profile['email'])) {
    set_flash('danger', 'Failed to retrieve your Google profile.');
    redirect('/student_login');
}

try {
    // Check if student exists by Gmail ID
    $stmt = $pdo->prepare("SELECT id, name, profile_image, is_active FROM users WHERE gmail_id = ? AND role = 'student'");
    $stmt->execute([$profile['sub'] ?? $profile['id']]);
    $student = $stmt->fetch();

    // If not found by Gmail ID, check by email
    if (!$student) {
        $stmt = $pdo->prepare("SELECT id, name, profile_image, is_active FROM users WHERE email = ? AND role = 'student'");
        $stmt->execute([$profile['email']]);
        $student = $stmt->fetch();

        if ($student) {
            // Link Gmail ID to existing student email account
            $stmt = $pdo->prepare("UPDATE users SET gmail_id = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$profile['sub'] ?? $profile['id'], $student['id']]);
        }
    }

    // If still not found, create new student account
    if (!$student) {
        $name = $profile['name'];
        $email = $profile['email'];
        $gmail_id = $profile['sub'] ?? $profile['id'];
        
        // Generate random password for Gmail-only students
        $random_password = bin2hex(random_bytes(16));
        $password_hash = password_hash($random_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (email, gmail_id, password_hash, name, role, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'student', 1, NOW(), NOW())
        ");
        $stmt->execute([$email, $gmail_id, $password_hash, $name]);

        $student_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("SELECT id, name, profile_image, is_active FROM users WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();
    }

    if ($student) {
        // Verify student is active
        if ($student['is_active'] == 0) {
            set_flash('danger', 'Your account has been deactivated. Please contact support.');
            redirect('/student_login');
        }

        // Login student
        $_SESSION['user_id'] = $student['id'];
        $_SESSION['user_name'] = $student['name'];
        $_SESSION['user_role'] = 'student';
        $_SESSION['user_email'] = $profile['email'];
        $_SESSION['user_profile_image'] = $student['profile_image'];

        session_regenerate_id(true);

        set_flash('success', 'Welcome back, ' . $student['name'] . '!');
        redirect('/dashboard');

    } else {
        set_flash('danger', 'Failed to create account. Please try again.');
        redirect('/student_login');
    }

} catch (Exception $e) {
    error_log("Google OAuth Callback Error: " . $e->getMessage());
    set_flash('danger', 'An error occurred during authentication. Please try again.');
    redirect('/student_login');
}
?>

}
?>