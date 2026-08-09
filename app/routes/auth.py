"""Auth routes: login, student/trainer login, social, password reset, logout, auth APIs."""

import json
import re
import secrets

from flask import Blueprint, jsonify, redirect, render_template, request, session, url_for

from config import Config
from app.db import db
from app.helpers import (
    generate_csrf_token, get_setting, hash_password, rate_limit_check, sanitize,
    set_flash, table_has_column, validate_csrf_token, verify_password,
)
from app.mail import send_email, send_otp_email

bp = Blueprint("auth", __name__)


def _build_session(user, email, role, profile_image=None):
    session.clear()
    session["user_id"] = user["id"]
    session["user_name"] = user["name"]
    session["user_role"] = role
    session["user_email"] = email or user.get("email")
    session["user_profile_image"] = profile_image if profile_image is not None else user.get("profile_image")
    session["is_approved"] = user.get("is_approved", 1)
    session.permanent = True


@bp.route("/login", methods=["GET", "POST"])
def login():
    if session.get("user_id"):
        return redirect("/dashboard")

    page_title = "Login"
    role_param = request.args.get("role", "student")
    if role_param not in ("student", "trainer", "admin"):
        role_param = "student"

    if request.method == "POST":
        email = sanitize(request.form.get("email", ""))
        password = request.form.get("password", "")
        selected_role = request.form.get("login_role", "student")

        errors = []
        if selected_role == "student":
            errors.append("Please use Google to sign in to your student account.")
        if not email:
            errors.append("Email is required")
        if not password:
            errors.append("Password is required")

        if not errors:
            user = db().fetch_one(
                "SELECT * FROM users WHERE email = %s AND role = %s", (email, selected_role)
            )

            if user and verify_password(password, user.get("password_hash") or ""):
                if user["role"] == "student":
                    set_flash("danger", "Please use Google to sign in to your student account.")
                    return redirect("/login")

                if not user.get("is_active"):
                    set_flash("danger", "Your account has been deactivated. Please contact support.")
                    return redirect("/login")

                _build_session(user, email, user["role"])
                set_flash("success", "Welcome back, {}!".format(user["name"]))

                if user["role"] == "admin":
                    return redirect("/admin/dashboard")
                return redirect("/dashboard")
            else:
                set_flash("danger", "Invalid email or password")
        else:
            for error in errors:
                set_flash("danger", error)

    return render_template(
        "auth/login.html",
        page_title=page_title,
        role_param=role_param,
    )


@bp.route("/student_login")
@bp.route("/student-login")
def student_login():
    if session.get("user_id"):
        if session.get("user_role") == "trainer":
            return redirect("/trainer-dashboard")
        return redirect("/dashboard")
    return render_template("auth/student_login.html", page_title="Student Login")


@bp.route("/trainer_login")
@bp.route("/trainer-login")
def trainer_login():
    if session.get("user_id"):
        if session.get("user_role") == "trainer":
            return redirect("/trainer-dashboard")
        return redirect("/dashboard")
    return render_template("auth/trainer_login.html", page_title="Trainer Login")


@bp.route("/logout")
def logout():
    session.clear()
    return redirect("/")


@bp.route("/forgot-password", methods=["GET", "POST"])
def forgot_password():
    import time as _time

    if session.get("user_id"):
        return redirect("/dashboard")

    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request. Please try again.")
            return redirect("/forgot-password")

        if not rate_limit_check("forgot_password", 5, 900):
            set_flash("danger", "Too many reset attempts. Please wait a few minutes and try again.")
            return redirect("/forgot-password")

        email = sanitize(request.form.get("email", ""))

        if not email:
            set_flash("danger", "Email is required")
        else:
            token = secrets.token_hex(32)
            token_time = int(_time.time()) + 3600
            should_send_email = False
            email_to_send = ""
            reset_link = ""
            user = None

            user = db().fetch_one("SELECT * FROM users WHERE email = %s", (email,))

            if user:
                if user["role"] == "student":
                    set_flash(
                        "info",
                        'Your account is linked to your Google account. Please use the "Continue with Google" '
                        'button on the <a href="/login" class="alert-link">Login page</a>.',
                    )
                    return redirect("/forgot-password")

                db().execute(
                    "UPDATE users SET reset_token = %s, reset_token_time = %s WHERE id = %s",
                    (token, token_time, user["id"]),
                )
                reset_link = (
                    Config.site_url()
                    + "/reset-password?token="
                    + token
                    + "&email="
                    + _urlencode(email)
                )
                should_send_email = True
                email_to_send = email

            if should_send_email and reset_link:
                subject = "Password Reset - " + Config.SITE_NAME
                user_name = user["name"] if user else "User"
                message = f"""
                <html>
                <head>
                    <title>Password Reset</title>
                </head>
                <body>
                    <h2>Password Reset Request</h2>
                    <p>Hello {user_name},</p>
                    <p>We received a request to reset your password. Click the link below to reset your password:</p>
                    <p><a href='{reset_link}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
                    <p>Or copy and paste this link in your browser:</p>
                    <p>{reset_link}</p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request a password reset, please ignore this email.</p>
                    <br>
                    <p>Best regards,<br>{get_setting('site_name', Config.SITE_NAME)}</p>
                </body>
                </html>
                """
                if send_email(email_to_send, subject, message, True):
                    set_flash(
                        "success",
                        "If the account exists and is eligible, a password reset link has been sent.",
                    )
                else:
                    set_flash(
                        "info",
                        "If the account exists and is eligible, a password reset link has been sent.",
                    )
            else:
                _time.sleep(0.2)
                set_flash("info", "If the account exists and is eligible, a password reset link has been sent.")

    return render_template("auth/forgot-password.html", page_title="Forgot Password")


@bp.route("/reset-password", methods=["GET", "POST"])
def reset_password():
    import time as _time

    if session.get("user_id"):
        return redirect("/dashboard")

    token = request.args.get("token", "")
    email = request.args.get("email", "")

    error = ""
    user = None

    if not token or not email:
        error = "Invalid reset link. Please request a new password reset."
    else:
        user = db().fetch_one("SELECT * FROM users WHERE email = %s AND reset_token = %s", (email, token))
        if not user:
            error = "Invalid reset link. Please request a new password reset."
        elif not user.get("reset_token_time") or int(user["reset_token_time"]) < int(_time.time()):
            error = "Reset link has expired. Please request a new password reset."

    if request.method == "POST" and not error:
        password = request.form.get("password", "")
        confirm_password = request.form.get("confirm_password", "")

        if not password:
            set_flash("danger", "Password is required")
        elif len(password) < 6:
            set_flash("danger", "Password must be at least 6 characters")
        elif password != confirm_password:
            set_flash("danger", "Passwords do not match")
        else:
            db().execute(
                "UPDATE users SET password_hash = %s, reset_token = NULL, reset_token_time = NULL WHERE id = %s",
                (hash_password(password), user["id"]),
            )
            set_flash("success", "Password has been reset successfully! You can now login with your new password.")
            return redirect("/login")

    return render_template(
        "auth/reset-password.html",
        page_title="Reset Password",
        error=error,
        token=token,
        email=email,
    )


# ---------------------------------------------------------------------------
# Social login (Google only, students)
# ---------------------------------------------------------------------------

@bp.route("/social-login/<provider>")
def social_login(provider):
    from app import social

    if provider != "google":
        set_flash("danger", "Invalid social provider. Only Google is supported for student login.")
        return redirect("/login")

    redirect_uri = Config.site_url() + "/social-callback/google"
    state = secrets.token_hex(16)
    session["oauth_state"] = state

    auth_url = social.get_auth_url(redirect_uri, state)
    if auth_url:
        return redirect(auth_url)

    set_flash("danger", "Failed to initialize social login.")
    return redirect("/login")


@bp.route("/social-callback/<provider>")
def social_callback(provider):
    from app import social

    code = request.args.get("code", "")
    state = request.args.get("state", "")

    if provider != "google":
        set_flash("danger", "Invalid OAuth provider.")
        return redirect("/student_login")

    if not code or state != session.get("oauth_state", ""):
        set_flash("danger", "Invalid authentication request.")
        return redirect("/student_login")

    session.pop("oauth_state", None)

    redirect_uri = Config.site_url() + "/social-callback/google"

    try:
        token_data = social.exchange_code(code, redirect_uri)
        access_token = token_data.get("access_token")
        if not access_token:
            set_flash("danger", "Failed to obtain access token from Google.")
            return redirect("/student_login")

        profile = social.get_user_info(access_token)
        if not profile or not profile.get("email"):
            set_flash("danger", "Failed to retrieve your Google profile.")
            return redirect("/student_login")

        gmail_id = profile["id"]
        email = profile["email"]

        has_gmail_id = table_has_column("users", "gmail_id")
        has_updated_at = table_has_column("users", "updated_at")

        student = None
        if has_gmail_id:
            student = db().fetch_one(
                "SELECT id, name, profile_image, is_active FROM users WHERE gmail_id = %s AND role = 'student'",
                (gmail_id,),
            )

        if not student:
            student = db().fetch_one(
                "SELECT id, name, profile_image, is_active FROM users WHERE email = %s AND role = 'student'",
                (email,),
            )
            if student and has_gmail_id:
                sql = "UPDATE users SET gmail_id = %s"
                if has_updated_at:
                    sql += ", updated_at = NOW()"
                sql += " WHERE id = %s"
                db().execute(sql, (gmail_id, student["id"]))

        if not student:
            name = profile["name"]
            random_password = secrets.token_hex(16)
            password_hash = hash_password(random_password)

            columns = ["email", "password_hash", "name", "role", "is_active", "created_at"]
            placeholders = ["%s", "%s", "%s", "'student'", "1", "NOW()"]
            values = [email, password_hash, name]

            if has_gmail_id:
                columns.insert(1, "gmail_id")
                placeholders.insert(1, "%s")
                values.insert(1, gmail_id)

            student_id = db().insert(
                "INSERT INTO users ({}) VALUES ({})".format(", ".join(columns), ", ".join(placeholders)),
                values,
            )
            student = db().fetch_one(
                "SELECT id, name, profile_image, is_active FROM users WHERE id = %s", (student_id,)
            )

        if student:
            if not student["is_active"]:
                set_flash("danger", "Your account has been deactivated. Please contact support.")
                return redirect("/student_login")

            _build_session(student, email, "student", student.get("profile_image"))
            set_flash("success", "Welcome back, {}!".format(student["name"]))
            return redirect("/dashboard")

        set_flash("danger", "Failed to create account. Please try again.")
        return redirect("/student_login")

    except Exception as exc:
        import logging
        logging.getLogger(__name__).exception("Google OAuth callback error")
        set_flash("danger", "An error occurred during authentication. Please try again.")
        return redirect("/student_login")


def _urlencode(value):
    from urllib.parse import quote
    return quote(value)


# ---------------------------------------------------------------------------
# Auth APIs
# ---------------------------------------------------------------------------

@bp.route("/api/trainer_auth")
def trainer_auth():
    return jsonify({"success": False, "message": "Invalid request method."}), 405


@bp.route("/api/trainer_auth", methods=["POST"])
def trainer_auth_post():
    from urllib.parse import quote

    action = request.args.get("action", "")

    if not validate_csrf_token(request.form.get("csrf_token", "")):
        return jsonify({"success": False, "message": "Invalid request. Please refresh and try again."}), 403

    email = request.form.get("email", "").strip()
    password = request.form.get("password", "")

    if "@" not in email:
        return jsonify({"success": False, "message": "Invalid email format."}), 400

    if action == "send_otp":
        if not rate_limit_check("trainer_send_otp", 5, 600):
            return (
                jsonify({"success": False, "message": "Too many OTP requests. Please wait a few minutes and try again."}),
                429,
            )

        if not password:
            return jsonify({"success": False, "message": "Password is required."}), 400

        trainer = db().fetch_one(
            "SELECT id, password_hash, is_active, otp_verified FROM users WHERE email = %s AND role = 'trainer'",
            (email,),
        )
        if not trainer:
            return (
                jsonify({
                    "success": False,
                    "message": "Trainer account not found. Please contact the admin team if you should have access.",
                }),
                404,
            )

        if not trainer.get("is_active"):
            return jsonify({"success": False, "message": "Your account has been deactivated."}), 403

        if not verify_password(password, trainer.get("password_hash") or ""):
            return jsonify({"success": False, "message": "Incorrect password."}), 401

        otp_code = f"{secrets.randbelow(1000000):06d}"

        db().execute(
            "UPDATE users SET otp_code = %s, otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), "
            "otp_verified = 0 WHERE id = %s",
            (otp_code, trainer["id"]),
        )

        if send_otp_email(email, otp_code, "login"):
            return jsonify({
                "success": True,
                "message": "OTP sent to your email. Valid for 10 minutes.",
                "action_type": "login",
            })
        return jsonify({"success": False, "message": "Failed to send OTP. Please try again later."}), 500

    elif action == "verify_otp":
        if not rate_limit_check("trainer_verify_otp", 10, 600):
            return (
                jsonify({"success": False, "message": "Too many verification attempts. Please wait a few minutes and try again."}),
                429,
            )

        otp_code = sanitize(request.form.get("otp", ""))
        if len(otp_code) != 6 or not otp_code.isdigit():
            return jsonify({"success": False, "message": "Invalid OTP format."}), 400

        trainer = db().fetch_one(
            "SELECT id, name, profile_image, is_approved, password_hash "
            "FROM users WHERE email = %s AND role = 'trainer' "
            "AND otp_code = %s AND otp_expires_at > NOW() AND is_active = 1 AND is_approved = 1",
            (email, otp_code),
        )

        if trainer:
            db().execute(
                "UPDATE users SET otp_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = %s",
                (trainer["id"],),
            )
            _build_session(trainer, email, "trainer", trainer.get("profile_image"))
            return jsonify({
                "success": True,
                "message": "Authentication successful!",
                "redirect": "/trainer-dashboard",
            })

        return jsonify({"success": False, "message": "Invalid or expired OTP."}), 401

    return jsonify({"success": False, "message": "Invalid action."}), 400


@bp.route("/api/student_auth", methods=["POST"])
def student_auth_post():
    action = request.args.get("action", "")

    if action != "verify_gmail":
        return jsonify({"success": False, "message": "Invalid action."}), 400

    if not rate_limit_check("student_gmail_auth", 20, 600):
        return (
            jsonify({"success": False, "message": "Too many login attempts. Please wait a few minutes and try again."}),
            429,
        )

    access_token = request.form.get("access_token", "")
    gmail_id = request.form.get("gmail_id", "")
    name = sanitize(request.form.get("name", ""))
    email = request.form.get("email", "").strip()

    if not access_token or not gmail_id or not email:
        return jsonify({"success": False, "message": "Missing required OAuth data."}), 400

    if "@" not in email:
        return jsonify({"success": False, "message": "Invalid email format."}), 400

    has_gmail_id = table_has_column("users", "gmail_id")
    has_username = table_has_column("users", "username")
    has_updated_at = table_has_column("users", "updated_at")

    if has_gmail_id:
        student = db().fetch_one(
            "SELECT id, name, profile_image, is_active, gmail_id "
            "FROM users WHERE role = 'student' AND (gmail_id = %s OR email = %s)",
            (gmail_id, email),
        )
    else:
        student = db().fetch_one(
            "SELECT id, name, profile_image, is_active "
            "FROM users WHERE email = %s AND role = 'student'",
            (email,),
        )

    if student:
        if not student["is_active"]:
            return jsonify({"success": False, "message": "Your account has been deactivated."}), 403

        if has_gmail_id and not student.get("gmail_id"):
            sql = "UPDATE users SET gmail_id = %s"
            if has_updated_at:
                sql += ", updated_at = NOW()"
            sql += " WHERE id = %s"
            db().execute(sql, (gmail_id, student["id"]))

        _build_session(student, email, "student", student.get("profile_image"))
        return jsonify({
            "success": True,
            "message": "Login successful!",
            "redirect": "/dashboard",
            "is_new": False,
        })

    username = None
    if name:
        base_name = name.lower().replace(" ", "_")
        username = re.sub(r"[^a-z0-9_]", "", base_name)

    random_password = secrets.token_hex(16)
    password_hash = hash_password(random_password)

    columns = ["email", "password_hash", "name", "role", "is_active", "created_at"]
    placeholders = ["%s", "%s", "%s", "'student'", "1", "NOW()"]
    values = [email, password_hash, name]

    if has_gmail_id:
        columns.insert(1, "gmail_id")
        placeholders.insert(1, "%s")
        values.insert(1, gmail_id)

    if has_username:
        columns.insert(2, "username")
        placeholders.insert(2, "%s")
        values.insert(2, username)

    student_id = db().insert(
        "INSERT INTO users ({}) VALUES ({})".format(", ".join(columns), ", ".join(placeholders)),
        values,
    )

    _build_session(
        {"id": student_id, "name": name, "profile_image": None, "is_approved": 1},
        email,
        "student",
        None,
    )
    return jsonify({
        "success": True,
        "message": "Account created and logged in successfully!",
        "redirect": "/dashboard",
        "is_new": True,
    }), 201
