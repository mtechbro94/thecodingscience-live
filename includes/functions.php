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
            'email' => $_SESSION['user_email'] ?? null,
            'profile_image' => $_SESSION['user_profile_image'] ?? null
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
 * Generate a 6-digit OTP
 */
function generate_otp()
{
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
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
        if ($type === 'profile') {
            return SITE_URL . '/assets/images/default-avatar.png';
        }
        return SITE_URL . '/assets/images/placeholder.webp';
    }

    // If it's already a full URL
    if (strpos($path, 'http') === 0) {
        return $path;
    }

    if ($type === 'profile') {
        return SITE_URL . '/assets/images/profiles/' . $path;
    }

    return SITE_URL . '/assets/images/' . $path;
}

/**
 * Get user avatar URL
 */
function get_avatar($user_data)
{
    $img = $user_data['profile_image'] ?? null;
    if (!empty($img)) {
        return get_image_url($img, 'profile');
    }
    return get_image_url('', 'profile');
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
        if (substr($line, 3, 1) == " ")
            break;
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
    if (empty($text))
        return '';

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

/**
 * Validate coupon code and calculate discount
 */
function validate_coupon($code, $total_amount, $pdo)
{
    $code = strtoupper(trim($code));

    try {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid coupon code'];
        }

        // Check expiry
        $now = new DateTime();
        if ($coupon['valid_from'] && new DateTime($coupon['valid_from']) > $now) {
            return ['success' => false, 'message' => 'Coupon is not yet valid'];
        }
        if ($coupon['valid_until'] && new DateTime($coupon['valid_until']) < $now) {
            return ['success' => false, 'message' => 'Coupon has expired'];
        }

        // Check minimum purchase
        if ($coupon['min_purchase'] > 0 && $total_amount < $coupon['min_purchase']) {
            return ['success' => false, 'message' => 'Minimum purchase of ₹' . number_format($coupon['min_purchase']) . ' required'];
        }

        // Check max uses
        if ($coupon['max_uses'] !== null && $coupon['used_count'] >= $coupon['max_uses']) {
            return ['success' => false, 'message' => 'Coupon usage limit reached'];
        }

        // Calculate discount
        if ($coupon['discount_type'] === 'percentage') {
            $discount = ($total_amount * $coupon['discount_value']) / 100;
        } else {
            $discount = min($coupon['discount_value'], $total_amount);
        }

        $final_amount = $total_amount - $discount;

        return [
            'success' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'original_amount' => $total_amount,
            'final_amount' => $final_amount
        ];

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error validating coupon'];
    }
}

/**
 * Increment coupon usage count
 */
function use_coupon($code, $pdo)
{
    $code = strtoupper(trim($code));
    try {
        $stmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
        $stmt->execute([$code]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get active internships, optionally filtered by category
 * @param string|null $category 'teaching', 'industrial', or null for all
 * @return array
 */
function get_internships_by_category($category = null)
{
    global $pdo; // Assuming $pdo is available globally from db.php

    $sql = "SELECT * FROM internships WHERE is_active = 1";
    $params = [];

    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY created_at DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log the error or handle it appropriately
        error_log("Error fetching internships: " . $e->getMessage());
        return [];
    }
}

/**
 * Get single internship by ID
 * @param int $id Internship ID
 * @return array|null
 */
function get_internship($id)
{
    global $pdo;

    $sql = "SELECT * FROM internships WHERE id = ? AND is_active = 1";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching internship: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all active trainer positions
 * @return array
 */
function get_trainer_positions()
{
    global $pdo;

    $sql = "SELECT * FROM trainer_positions WHERE is_active = 1 ORDER BY created_at DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching trainer positions: " . $e->getMessage());
        return [];
    }
}

/**
 * Get single trainer position by ID
 * @param int $id Position ID
 * @return array|null
 */
function get_trainer_position($id)
{
    global $pdo;

    $sql = "SELECT * FROM trainer_positions WHERE id = ? AND is_active = 1";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching trainer position: " . $e->getMessage());
        return null;
    }
}

/**
 * Convert Markdown to HTML
 * Simple markdown parser for basic formatting
 * @param string $markdown Markdown text
 * @return string HTML content
 */
function markdown_to_html($markdown)
{
    if (empty($markdown)) {
        return '';
    }

    // Convert markdown to HTML
    $html = htmlspecialchars($markdown);

    // Headers
    $html = preg_replace('/^### (.*?)$/m', '<h4>$1</h4>', $html);
    $html = preg_replace('/^## (.*?)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^# (.*?)$/m', '<h2>$1</h2>', $html);

    // Bold
    $html = preg_replace('/\*\*(.*?)\*\*/m', '<strong>$1</strong>', $html);
    $html = preg_replace('/__(.*?)__/m', '<strong>$1</strong>', $html);

    // Italic
    $html = preg_replace('/\*(.*?)\*/m', '<em>$1</em>', $html);
    $html = preg_replace('/_(.*?)_/m', '<em>$1</em>', $html);

    // Lists
    $html = preg_replace('/^\- (.*?)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*?<\/li>)/s', '<ul>$1</ul>', $html);
    $html = str_replace('</li><li>', '</li><li>', $html);

    // Ordered lists
    $html = preg_replace('/^\d+\. (.*?)$/m', '<li>$1</li>', $html);

    // Line breaks
    $html = nl2br($html);

    // Blockquotes
    $html = preg_replace('/^&gt; (.*?)$/m', '<blockquote><p>$1</p></blockquote>', $html);

    return $html;
}
