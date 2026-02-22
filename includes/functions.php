<?php
// includes/functions.php - Helper Functions

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged in user data
 */
function current_user()
{
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? 'student',
            'email' => $_SESSION['user_email'] ?? null
        ];
    }
    return null;
}


/**
 * Check if current user is admin
 */
function is_admin()
{
    $user = current_user();
    return $user && ($user['role'] === 'admin');
}

/**
 * Check if current user is trainer
 */
function is_trainer()
{
    $user = current_user();
    return $user && ($user['role'] === 'trainer');
}

/**
 * Redirect to a specific URL
 */
function redirect($url)
{
    header("Location: $url");
    exit();
}

/**
 * Set flash message
 */
function set_flash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function get_flash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sanitize input data
 */
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Generate Slug from Title
 */
function generate_slug($title)
{
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Get a site setting by key
 */
function get_setting($key, $default = '')
{
    global $site_settings;
    return $site_settings[$key] ?? $default;
}

/**
 * Get the full URL for an image
 */
function get_image_url($path, $type = 'common')
{
    if (empty($path)) {
        return '/assets/images/placeholder.webp';
    }

    // If it's already a full URL
    if (strpos($path, 'http') === 0) {
        return $path;
    }

    return '/assets/images/' . $path;
}

/**
 * Send Email using SMTP
 */
function send_email($to, $subject, $message, $is_html = true)
{
    $from_email = defined('SMTP_USER') ? SMTP_USER : 'noreply@thecodingscience.com';
    $from_name = defined('SITE_NAME') ? SITE_NAME : 'The Coding Science';
    
    // Try SMTP first
    if (defined('SMTP_HOST') && defined('SMTP_USER') && defined('SMTP_PASS')) {
        $result = smtp_mail($to, $subject, $message, $from_email, $from_name, $is_html);
        if ($result) {
            return true;
        }
    }
    
    // Fallback to PHP mail()
    $headers = "MIME-Version: 1.0\r\n";
    if ($is_html) {
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    }
    $headers .= "From: $from_name <$from_email>\r\n";
    $headers .= "Reply-To: $from_email\r\n";
    
    return @mail($to, $subject, $message, $headers);
}

/**
 * Send Email via SMTP (Gmail)
 */
function smtp_mail($to, $subject, $message, $from_email, $from_name, $is_html = true)
{
    $smtp_host = 'ssl://smtp.gmail.com';
    $smtp_port = 465;
    $smtp_user = SMTP_USER;
    $smtp_pass = SMTP_PASS;
    
    $from = $from_name . " <" . $from_email . ">";
    
    $fp = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);
    
    if (!$fp) {
        // Try port 587 with TLS
        $fp = @fsockopen('tls://smtp.gmail.com', 587, $errno, $errstr, 30);
        if (!$fp) {
            return false;
        }
    }
    
    $response = fgets($fp, 515);
    
    // EHLO
    fputs($fp, "EHLO localhost\r\n");
    while ($line = fgets($fp, 515)) {
        if (substr($line, 3, 1) == " ") break;
    }
    
    // AUTH LOGIN
    fputs($fp, "AUTH LOGIN\r\n");
    $response = fgets($fp, 515);
    
    // Username
    fputs($fp, base64_encode($smtp_user) . "\r\n");
    $response = fgets($fp, 515);
    
    // Password
    fputs($fp, base64_encode($smtp_pass) . "\r\n");
    $response = fgets($fp, 515);
    
    if (substr($response, 0, 3) != '235') {
        fclose($fp);
        return false;
    }
    
    // MAIL FROM
    fputs($fp, "MAIL FROM: <$smtp_user>\r\n");
    fgets($fp, 515);
    
    // RCPT TO
    fputs($fp, "RCPT TO: <$to>\r\n");
    fgets($fp, 515);
    
    // DATA
    fputs($fp, "DATA\r\n");
    $response = fgets($fp, 515);
    
    // Message headers and body
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from_email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: " . ($is_html ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
    
    fputs($fp, $headers . "\r\n" . $subject . "\r\n\r\n" . $message . "\r\n.\r\n");
    $response = fgets($fp, 515);
    
    // QUIT
    fputs($fp, "QUIT\r\n");
    fclose($fp);
    
    return true;
}

/**
 * Render Markdown as HTML
 * Simple implementation for basic markdown formatting
 */
function render_markdown($text)
{
    if (empty($text)) return '';
    
    $text = htmlspecialchars($text);
    
    // Convert line breaks to <br>
    $text = nl2br($text);
    
    // Bold: **text** or __text__
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);
    
    // Italic: *text* or _text_
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/_(.+?)_/', '<em>$1</em>', $text);
    
    // Links: [text](url)
    $text = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $text);
    
    // Unordered lists: - item or * item
    $text = preg_replace('/^[\*\-]\s+(.+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    
    // Clean up multiple <br> in a row
    $text = preg_replace('/(<br\s*\/?>\s*){3,}/', '<br>', $text);
    
    return $text;
}
?>