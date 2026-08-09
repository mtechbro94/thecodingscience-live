"""Email utilities mirroring includes/mail.php + send_email from functions.php."""

import smtplib
from datetime import datetime
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

from config import Config
from app.db import db


def send_email(to, subject, message, is_html=True):
    """Send email via SMTP. Mirrors PHP send_email (SMTP path only)."""
    from_email = Config.SMTP_USER or "noreply@thecodingscience.com"
    from_name = Config.SITE_NAME

    if not Config.SMTP_HOST or not Config.SMTP_USER or not Config.SMTP_PASS:
        return False

    msg = MIMEMultipart("alternative")
    msg["Subject"] = subject
    msg["From"] = f"{from_name} <{from_email}>"
    msg["To"] = to
    msg["Reply-To"] = from_email

    msg.attach(MIMEText(message, "html" if is_html else "plain", "utf-8"))

    try:
        server = smtplib.SMTP(Config.SMTP_HOST, Config.SMTP_PORT, timeout=30)
        server.ehlo()
        if Config.SMTP_PORT == 587:
            server.starttls()
            server.ehlo()
        server.login(Config.SMTP_USER, Config.SMTP_PASS)
        server.sendmail(from_email, [to], msg.as_string())
        server.quit()
        return True
    except Exception as exc:
        import logging
        logging.getLogger(__name__).error("SMTP send failed: %s", exc)
        return False


def send_otp_email(email, otp_code, purpose="login"):
    """Send styled OTP email. Mirrors includes/mail.php send_otp_email()."""
    subject = f"Your Verification Code - {Config.SITE_NAME}"

    if purpose == "password_reset":
        purpose_text = "Reset your password. Your verification code is:"
    else:
        purpose_text = f"Your verification code for {Config.SITE_NAME} is:"

    message = f"""
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h2 style='color: #4f46e5; margin: 0; font-size: 24px;'>Verification Code</h2>
            </div>

            <p style='color: #334155; font-size: 16px; margin-bottom: 25px;'>{purpose_text}</p>

            <div style='background: white; padding: 25px; text-align: center; border-radius: 8px; margin-bottom: 30px; border: 2px solid #4f46e5;'>
                <div style='font-size: 42px; font-weight: 800; letter-spacing: 8px; color: #4f46e5; font-family: monospace; line-height: 1.2;'>
                    {otp_code}
                </div>
            </div>

            <p style='color: #64748b; font-size: 14px; text-align: center; margin-bottom: 20px;'>
                <strong style='color: #ef4444;'>This code will expire in 10 minutes.</strong><br>
                If you didn't request this code, please ignore this email.
            </p>

            <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;'>

            <p style='text-align: center; color: #94a3b8; font-size: 12px; margin: 0;'>
                &copy; {datetime.now().year} {Config.SITE_NAME}. All rights reserved.<br>
                <a href='{Config.site_url()}' style='color: #4f46e5; text-decoration: none;'>{Config.SITE_NAME}</a>
            </p>
        </div>
    """

    return send_email(email, subject, message)


def cleanup_expired_otps():
    """Delete expired OTP tokens from otp_tokens."""
    try:
        db().execute("DELETE FROM otp_tokens WHERE expires_at < NOW()")
        return True
    except Exception:
        return False
