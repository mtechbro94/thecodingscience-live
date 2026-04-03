<?php
// includes/mail.php - Email sending utilities

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('send_email_via_phpmailer')) {
    /**
     * Send email via PHPMailer when the dependency is installed.
     */
    function send_email_via_phpmailer($to, $subject, $body, $attachments = [])
    {
        $autoload_path = __DIR__ . '/../vendor/autoload.php';
        if (!file_exists($autoload_path)) {
            return false;
        }

        try {
            require_once $autoload_path;

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST') ?? 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER') ?? getenv('MAIL_USERNAME');
            $mail->Password = getenv('SMTP_PASS') ?? getenv('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = getenv('SMTP_PORT') ?? 587;

            $mail->setFrom(getenv('MAIL_FROM') ?? 'noreply@thecodingscience.com', SITE_NAME);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            foreach ($attachments as $file) {
                if (file_exists($file)) {
                    $mail->addAttachment($file);
                }
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Send OTP via Email (simplified version if PHPMailer not available)
 */
if (!function_exists('send_otp_email')) {
function send_otp_email($email, $otp_code, $purpose = 'login')
{
    $subject = "Your Verification Code - " . SITE_NAME;

    $purpose_text = match ($purpose) {
        'password_reset' => 'Reset your password. Your verification code is:',
        default => 'Your verification code for ' . SITE_NAME . ' is:'
    };

    $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h2 style='color: #4f46e5; margin: 0; font-size: 24px;'>Verification Code</h2>
            </div>
            
            <p style='color: #334155; font-size: 16px; margin-bottom: 20px;'>Hello,</p>
            
            <p style='color: #334155; font-size: 16px; margin-bottom: 25px;'>{$purpose_text}</p>
            
            <div style='background: white; padding: 25px; text-align: center; border-radius: 8px; margin-bottom: 30px; border: 2px solid #4f46e5;'>
                <div style='font-size: 42px; font-weight: 800; letter-spacing: 8px; color: #4f46e5; font-family: monospace; line-height: 1.2;'>
                    {$otp_code}
                </div>
            </div>
            
            <p style='color: #64748b; font-size: 14px; text-align: center; margin-bottom: 20px;'>
                <strong style='color: #ef4444;'>This code will expire in 10 minutes.</strong><br>
                If you didn't request this code, please ignore this email.
            </p>
            
            <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;'>
            
            <p style='text-align: center; color: #94a3b8; font-size: 12px; margin: 0;'>
                &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.<br>
                <a href='" . SITE_URL . "' style='color: #4f46e5; text-decoration: none;'>" . SITE_NAME . "</a>
            </p>
        </div>
    ";
    if (function_exists('send_email_via_phpmailer') && send_email_via_phpmailer($email, $subject, $message)) {
        return true;
    }

    return function_exists('send_email') ? send_email($email, $subject, $message) : false;
}
}

/**
 * Clean up expired OTP tokens
 */
if (!function_exists('cleanup_expired_otps')) {
function cleanup_expired_otps()
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM otp_tokens WHERE expires_at < NOW()");
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        error_log("OTP Cleanup Error: " . $e->getMessage());
        return false;
    }
}
}
?>
