<?php
// views/social_callback.php
require_once 'includes/SocialAuth.php';

$provider = $match[1] ?? '';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

if (empty($code) || $state !== ($_SESSION['oauth_state'] ?? '')) {
    set_flash('danger', 'Invalid authentication request.');
    redirect('/login');
}

unset($_SESSION['oauth_state']);

$socialAuth = new SocialAuth();
$redirect_uri = SITE_URL . '/social-callback/' . $provider;

$tokenData = $socialAuth->exchangeCode($provider, $code, $redirect_uri);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    set_flash('danger', 'Failed to obtain access token.');
    redirect('/login');
}

$profile = $socialAuth->getUserInfo($provider, $accessToken);

if (!$profile || empty($profile['email'])) {
    set_flash('danger', 'Failed to retrieve user profile.');
    redirect('/login');
}

// 1. Check if user exists by OAuth ID
$stmt = $pdo->prepare("SELECT * FROM users WHERE oauth_provider = ? AND oauth_id = ?");
$stmt->execute([$provider, $profile['id']]);
$user = $stmt->fetch();

// 2. If not found by OAuth, check by Email
if (!$user) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$profile['email']]);
    $user = $stmt->fetch();

    if ($user) {
        // Link social account to existing email
        $stmt = $pdo->prepare("UPDATE users SET oauth_provider = ?, oauth_id = ?, profile_image = COALESCE(profile_image, ?) WHERE id = ?");
        $stmt->execute([$provider, $profile['id'], $profile['avatar'] ?? null, $user['id']]);
    }
}

// 3. If still not found, create new user
if (!$user) {
    $role = $_SESSION['oauth_role'] ?? 'student';
    unset($_SESSION['oauth_role']);

    $name = $profile['name'];
    $email = $profile['email'];
    $oauth_id = $profile['id'];
    $avatar = $profile['avatar'] ?? null;

    // Students are approved immediately, trainers need approval
    $is_approved = ($role === 'student') ? 1 : 0;

    $sql = "INSERT INTO users (name, email, role, oauth_provider, oauth_id, profile_image, is_approved, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email, $role, $provider, $oauth_id, $avatar, $is_approved]);

    $user_id = $pdo->lastInsertId();

    // Fetch the new user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

// 4. Perform Login
if ($user) {
    if (empty($user['is_active']) && isset($user['is_active'])) {
        set_flash('danger', 'Your account is deactivated.');
        redirect('/login');
    }

    if ($user['role'] === 'trainer' && empty($user['is_approved'])) {
        set_flash('warning', 'Your trainer account is pending approval.');
        redirect('/login');
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_profile_image'] = $user['profile_image'];
    $_SESSION['is_approved'] = $user['is_approved'] ?? 1;

    set_flash('success', 'Welcome back, ' . $user['name'] . '!');

    if ($user['role'] === 'admin') {
        redirect('/admin/dashboard');
    } elseif ($user['role'] === 'trainer') {
        redirect('/trainer-dashboard');
    } else {
        redirect('/dashboard');
    }
} else {
    set_flash('danger', 'Authentication failed.');
    redirect('/login');
}
?>