import os

from dotenv import load_dotenv

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

load_dotenv(os.path.join(BASE_DIR, ".env"))


def _env(key, default=None):
    return os.environ.get(key, default)


def _env_bool(key, default=False):
    value = _env(key)
    if value is None:
        return default
    return str(value).strip().lower() in ("1", "true", "yes", "on")


def _resolve_site_url():
    """Mirror config.php: use SITE_URL, but fall back to request host when on localhost."""
    env_url = (_env("SITE_URL") or "https://thecodingscience.com").rstrip("/")
    # During config load we don't have a request; dynamic resolution happens per-request.
    return env_url


class Config:
    """Application configuration, mirroring config.php constants."""

    ENVIRONMENT = _env("ENVIRONMENT", "production")
    SECRET_KEY = _env("SECRET_KEY", _env("SESSION_SECRET", "change-me-in-production"))
    SESSION_COOKIE_HTTPONLY = True
    SESSION_COOKIE_SECURE = ENVIRONMENT == "production"
    SESSION_COOKIE_SAMESITE = "Lax"
    PERMANENT_SESSION_LIFETIME = 7 * 24 * 60 * 60

    BASE_PATH = BASE_DIR

    DB_HOST = _env("DB_HOST", "localhost")
    DB_NAME = _env("DB_NAME", "")
    DB_USER = _env("DB_USER", "root")
    DB_PASS = _env("DB_PASS", "")
    DB_PORT = int(_env("DB_PORT", "3306"))

    SITE_NAME = _env("SITE_NAME", "The Coding Science")
    SITE_URL = _resolve_site_url()

    CONTACT_EMAIL = "csdsofficial249@gmail.com"
    ACADEMY_EMAIL = "academy@thecodingscience.com"
    SUPPORT_EMAIL = "support@thecodingscience.com"
    CONTACT_PHONE = "+916006516909"
    SECONDARY_PHONE = "+917889899153"

    RAZORPAY_KEY_ID = _env("RAZORPAY_KEY_ID", "")
    RAZORPAY_KEY_SECRET = _env("RAZORPAY_KEY_SECRET", "")

    SMTP_HOST = _env("SMTP_HOST", "smtp.gmail.com")
    SMTP_PORT = int(_env("SMTP_PORT", "465"))
    SMTP_USER = _env("SMTP_USER", "")
    SMTP_PASS = _env("SMTP_PASS", "")

    GOOGLE_CLIENT_ID = _env("GOOGLE_CLIENT_ID", "")
    GOOGLE_CLIENT_SECRET = _env("GOOGLE_CLIENT_SECRET", "")

    @staticmethod
    def site_url():
        """Resolve SITE_URL dynamically, falling back to local host in development."""
        base = Config.SITE_URL
        from flask import request
        try:
            host = request.host
        except Exception:
            return base
        if (host.startswith("localhost") or host.startswith("127.0.0.1")) and "thecodingscience.com" in base:
            return request.scheme + "://" + host
        return base
