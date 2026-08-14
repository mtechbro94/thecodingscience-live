import secrets
from datetime import timedelta

from flask import Flask, g, render_template, session

from config import Config


def create_app(config_class=Config):
    app = Flask(
        __name__,
        static_folder="../assets",
        static_url_path="/assets",
        template_folder="templates",
    )
    app.config.from_object(config_class)

    app.permanent_session_lifetime = timedelta(seconds=config_class.PERMANENT_SESSION_LIFETIME)

    from app.db import db, init_app as init_db
    from app.helpers import get_setting

    init_db(app)

    @app.before_request
    def set_script_nonce():
        session.permanent = True
        g.script_nonce = secrets.token_hex(16)

    @app.after_request
    def apply_security_headers(response):
        csp_nonce = getattr(g, "script_nonce", secrets.token_hex(16))
        csp = (
            "default-src 'self'; "
            "script-src 'self' 'nonce-{nonce}' "
            "https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com "
            "https://code.jquery.com https://www.googletagmanager.com "
            "https://www.google-analytics.com https://www.google.com "
            "https://accounts.google.com https://checkout.razorpay.com; "
            "style-src 'self' 'unsafe-inline' "
            "https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com "
            "https://fonts.googleapis.com; "
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; "
            "img-src 'self' data: blob: https:; "
            "frame-src https://accounts.google.com https://checkout.razorpay.com; "
            "connect-src 'self' https://*.googleapis.com https://*.razorpay.com "
            "https://api.qrserver.com https://www.google-analytics.com https://www.google.com; "
            "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'"
        ).format(nonce=csp_nonce)

        if Config.ENVIRONMENT == "production":
            response.headers["Strict-Transport-Security"] = "max-age=31536000; includeSubDomains"
        response.headers["Content-Security-Policy"] = csp
        response.headers["X-Content-Type-Options"] = "nosniff"
        response.headers["X-Frame-Options"] = "DENY"
        response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
        response.headers["Permissions-Policy"] = (
            "camera=(), microphone=(), geolocation=(), browsing-topics=()"
        )
        return response

    @app.context_processor
    def inject_globals():
        from flask import request as flask_request

        from app.helpers import (
            current_user, generate_csrf_token, get_avatar, get_flash, get_image_url,
            get_internship, get_internships_by_category, is_admin,
            is_logged_in, is_student, is_trainer, validate_coupon,
        )

        return {
            "script_nonce": getattr(g, "script_nonce", ""),
            "get_setting": get_setting,
            "get_avatar": get_avatar,
            "get_flash": get_flash,
            "get_image_url": get_image_url,
            "get_internships_by_category": get_internships_by_category,
            "get_internship": get_internship,
            "is_logged_in": is_logged_in,
            "current_user": current_user,
            "is_admin": is_admin,
            "is_trainer": is_trainer,
            "is_student": is_student,
            "generate_csrf_token": generate_csrf_token,
            "validate_coupon": validate_coupon,
            "request_path": flask_request.path,
            "SITE_NAME": Config.SITE_NAME,
            "SITE_URL": Config.site_url(),
            "CONTACT_EMAIL": Config.CONTACT_EMAIL,
            "CONTACT_PHONE": Config.CONTACT_PHONE,
            "SECONDARY_PHONE": Config.SECONDARY_PHONE,
            "RAZORPAY_KEY_ID": Config.RAZORPAY_KEY_ID,
        }

    from app.routes.auth import bp as auth_bp
    from app.routes.public import bp as public_bp
    from app.routes.student import bp as student_bp
    from app.routes.payments import bp as payments_bp
    from app.routes.trainer import bp as trainer_bp
    from app.routes.admin import bp as admin_bp
    from app.routes.admin_core import bp as admin_core_bp
    from app.routes.admin_content import bp as admin_content_bp

    app.register_blueprint(public_bp)
    app.register_blueprint(auth_bp)
    app.register_blueprint(student_bp)
    app.register_blueprint(payments_bp)
    app.register_blueprint(trainer_bp)
    app.register_blueprint(admin_bp, url_prefix="/admin")
    app.register_blueprint(admin_core_bp, url_prefix="/admin")
    app.register_blueprint(admin_content_bp, url_prefix="/admin")

    app.jinja_env.filters["get_setting"] = get_setting

    import re
    from datetime import datetime

    app.jinja_env.filters["digits_only"] = lambda s: re.sub(r"[^0-9]", "", str(s or ""))
    app.jinja_env.filters["clean_text"] = lambda value: " ".join(
        str(value or "").replace("\r", " ").replace("\n", " ").split()
    )
    app.jinja_env.filters["markdown_to_html"] = helpers.markdown_to_html
    app.jinja_env.filters["number_format"] = lambda n, decimals=0: (
        f"{{:,.{decimals}f}}".format(float(n or 0))
    )

    def pretty_date(value):
        if not value:
            return ""
        try:
            return value.strftime("%B %d, %Y").replace(" 0", " ")
        except (AttributeError, ValueError):
            return ""

    app.jinja_env.filters["pretty_date"] = pretty_date
    app.jinja_env.globals["now"] = datetime.now()

    @app.errorhandler(404)
    def not_found(e):
        return render_template("404.html"), 404

    return app
