<?php
// views/social_login.php
require_once 'includes/SocialAuth.php';

$provider = $match[1] ?? ''; // From router regex
$allowed_providers = ['google', 'linkedin', 'github'];

if (!in_array($provider, $allowed_providers)) {
    set_flash('danger', 'Invalid social provider.');
    redirect('/login');
}

$socialAuth = new SocialAuth();
$redirect_uri = SITE_URL . '/social-callback/' . $provider;
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// We also store the intended role if they came from register page
// In a real app, we'd pass this via state or another session var
$_SESSION['oauth_role'] = $_GET['role'] ?? 'student';

$authUrl = $socialAuth->getAuthUrl($provider, $redirect_uri, $state);

if ($authUrl) {
    redirect($authUrl);
} else {
    set_flash('danger', 'Failed to initialize social login.');
    redirect('/login');
}
?>