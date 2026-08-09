"""Helper functions mirroring includes/functions.php."""

import hashlib
import hmac
import html
import re
import secrets
import time
from datetime import datetime

from flask import abort, g, redirect, request, session

import bcrypt

from config import Config
from app.db import db

# Schema inspection cache: (table, column) -> bool
_COLUMN_CACHE = {}


# ---------------------------------------------------------------------------
# Session / current user
# ---------------------------------------------------------------------------

def is_logged_in():
    return session.get("user_id") is not None


def current_user():
    if not is_logged_in():
        return None
    return {
        "id": session.get("user_id"),
        "name": session.get("user_name"),
        "role": session.get("user_role", "student"),
        "email": session.get("user_email"),
        "profile_image": session.get("user_profile_image"),
    }


def is_admin():
    user = current_user()
    return bool(user and user["role"] == "admin")


def is_trainer():
    user = current_user()
    return bool(user and user["role"] == "trainer")


def is_student():
    user = current_user()
    return bool(user and user["role"] == "student")


# ---------------------------------------------------------------------------
# Flash messages (Flask-native, stored in session['flash'])
# ---------------------------------------------------------------------------

def set_flash(message_type, message):
    session["flash"] = {"type": message_type, "message": message}


def get_flash():
    flash_data = session.pop("flash", None)
    return flash_data


# ---------------------------------------------------------------------------
# Input helpers
# ---------------------------------------------------------------------------

def sanitize(value):
    """htmlspecialchars(strip_tags(trim(value)))"""
    value = html.escape(html.unescape(str(value)).strip(), quote=True)
    return value


def generate_slug(title):
    slug = str(title).lower()
    slug = re.sub(r"[^a-z0-9\s-]", "", slug)
    slug = re.sub(r"[\s-]+", "-", slug)
    slug = slug.strip("-")
    return slug


# ---------------------------------------------------------------------------
# Site settings
# ---------------------------------------------------------------------------

def get_setting(key, default=""):
    if "site_settings" not in g:
        g.site_settings = {}
        try:
            for row in db().query("SELECT `key`, `value` FROM site_settings"):
                g.site_settings[row["key"]] = row["value"]
        except Exception:
            g.site_settings = {}
    return g.site_settings.get(key, default)


# ---------------------------------------------------------------------------
# Schema inspection
# ---------------------------------------------------------------------------

def table_has_column(table, column):
    cache_key = (table, column)
    if cache_key in _COLUMN_CACHE:
        return _COLUMN_CACHE[cache_key]

    try:
        row = db().fetch_val(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS "
            "WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            (Config.DB_NAME, table, column),
        )
        result = bool(row)
    except Exception:
        result = False

    _COLUMN_CACHE[cache_key] = result
    return result


# ---------------------------------------------------------------------------
# Image helpers
# ---------------------------------------------------------------------------

def get_image_url(path, image_type="common"):
    site_url = Config.site_url()
    if not path:
        if image_type == "profile":
            return site_url + "/assets/images/default-avatar.png"
        return site_url + "/assets/images/placeholder.webp"

    if str(path).startswith("http"):
        return path

    if image_type == "profile":
        import os
        full_path = os.path.join(Config.BASE_PATH, "assets", "images", "profiles", path)
        if os.path.exists(full_path):
            return site_url + "/assets/images/profiles/" + path
        return site_url + "/assets/images/" + path

    return site_url + "/assets/images/" + path


def get_avatar(user_data):
    img = (user_data or {}).get("profile_image")
    if img:
        return get_image_url(img, "profile")
    return get_image_url("", "profile")


# ---------------------------------------------------------------------------
# Password hashing (bcrypt, compatible with PHP password_hash/password_verify)
# ---------------------------------------------------------------------------

def hash_password(plain_password):
    return bcrypt.hashpw(plain_password.encode("utf-8"), bcrypt.gensalt(rounds=12)).decode("utf-8")


def verify_password(plain_password, hashed):
    if not hashed:
        return False
    try:
        candidate = hashed.encode("utf-8")
        if candidate.startswith(b"$2y$"):
            candidate = b"$2b$" + candidate[4:]
        return bcrypt.checkpw(plain_password.encode("utf-8"), candidate)
    except (ValueError, TypeError):
        return False


# ---------------------------------------------------------------------------
# Markdown rendering (mirrors markdown_to_html)
# ---------------------------------------------------------------------------

def markdown_to_html(markdown):
    if not markdown:
        return ""
    text = html.escape(markdown)

    text = re.sub(r"^### (.*?)$", r"<h4>\1</h4>", text, flags=re.M)
    text = re.sub(r"^## (.*?)$", r"<h3>\1</h3>", text, flags=re.M)
    text = re.sub(r"^# (.*?)$", r"<h2>\1</h2>", text, flags=re.M)
    text = re.sub(r"\*\*(.*?)\*\*", r"<strong>\1</strong>", text, flags=re.M)
    text = re.sub(r"__(.*?)__", r"<strong>\1</strong>", text, flags=re.M)
    text = re.sub(r"\*(.*?)\*", r"<em>\1</em>", text, flags=re.M)
    text = re.sub(r"_(.*?)_", r"<em>\1</em>", text, flags=re.M)
    text = re.sub(r"^\- (.*?)$", r"<li>\1</li>", text, flags=re.M)
    text = re.sub(r"(<li>.*?</li>)", r"<ul>\1</ul>", text, flags=re.S)
    text = re.sub(r"^\d+\. (.*?)$", r"<li>\1</li>", text, flags=re.M)
    text = re.sub(r"^&gt; (.*?)$", r"<blockquote><p>\1</p></blockquote>", text, flags=re.M)
    text = text.replace("\n", "<br>")
    return text


render_markdown = markdown_to_html


# ---------------------------------------------------------------------------
# CSRF
# ---------------------------------------------------------------------------

def generate_csrf_token():
    if not session.get("csrf_token"):
        session["csrf_token"] = secrets.token_hex(32)
    return session["csrf_token"]


def validate_csrf_token(token):
    if not token:
        return False
    return session.get("csrf_token") is not None and hmac.compare_digest(
        str(session["csrf_token"]), str(token)
    )


# ---------------------------------------------------------------------------
# Rate limiting (session-backed, mirrors PHP)
# ---------------------------------------------------------------------------

def rate_limit_check(key, max_attempts, window_seconds):
    now = int(time.time())
    attempts = [t for t in session.get("rate_limits", {}).get(key, [])
                if (now - int(t)) < window_seconds]

    if len(attempts) >= max_attempts:
        session.setdefault("rate_limits", {})[key] = attempts
        return False

    attempts.append(now)
    session.setdefault("rate_limits", {})[key] = attempts
    return True


# ---------------------------------------------------------------------------
# Coupons
# ---------------------------------------------------------------------------

def validate_coupon(code, total_amount):
    code = str(code or "").strip().upper()
    try:
        coupon = db().fetch_one(
            "SELECT * FROM coupons WHERE code = %s AND is_active = 1", (code,)
        )
    except Exception:
        return {"success": False, "message": "Error validating coupon"}

    if not coupon:
        return {"success": False, "message": "Invalid coupon code"}

    now = datetime.now()
    try:
        if coupon.get("valid_from") and coupon["valid_from"] and coupon["valid_from"] > now:
            return {"success": False, "message": "Coupon is not yet valid"}
        if coupon.get("valid_until") and coupon["valid_until"] and coupon["valid_until"] < now:
            return {"success": False, "message": "Coupon has expired"}
    except TypeError:
        pass

    if float(coupon.get("min_purchase") or 0) > 0 and total_amount < float(coupon["min_purchase"]):
        return {
            "success": False,
            "message": "Minimum purchase of ₹{:,} required".format(int(coupon["min_purchase"])),
        }

    max_uses = coupon.get("max_uses")
    if max_uses is not None and int(coupon.get("used_count") or 0) >= int(max_uses):
        return {"success": False, "message": "Coupon usage limit reached"}

    if coupon["discount_type"] == "percentage":
        discount = (total_amount * float(coupon["discount_value"])) / 100
    else:
        discount = min(float(coupon["discount_value"]), total_amount)

    final_amount = total_amount - discount

    return {
        "success": True,
        "coupon": coupon,
        "discount": discount,
        "original_amount": total_amount,
        "final_amount": final_amount,
    }


def use_coupon(code):
    code = str(code or "").strip().upper()
    try:
        db().execute("UPDATE coupons SET used_count = used_count + 1 WHERE code = %s", (code,))
        return True
    except Exception:
        return False


# ---------------------------------------------------------------------------
# Internships
# ---------------------------------------------------------------------------

def get_internships_by_category(category=None):
    sql = "SELECT * FROM internships WHERE is_active = 1"
    params = []
    if category:
        sql += " AND category = %s"
        params.append(category)
    sql += " ORDER BY created_at DESC"
    try:
        return db().fetch_all(sql, params)
    except Exception:
        return []


def get_internship(internship_id):
    try:
        return db().fetch_one(
            "SELECT * FROM internships WHERE id = %s AND is_active = 1", (internship_id,)
        )
    except Exception:
        return None


# ---------------------------------------------------------------------------
# Search
# ---------------------------------------------------------------------------

def search_content(query):
    results = []
    term = f"%{query}%"

    try:
        courses = db().fetch_all(
            "SELECT 'course' as type, id, name as title, summary as content, image "
            "FROM courses WHERE name LIKE %s OR summary LIKE %s OR description LIKE %s",
            (term, term, term),
        )
        results.extend(courses)
    except Exception:
        pass

    try:
        blogs = db().fetch_all(
            "SELECT 'blog' as type, id, title, excerpt as content, image FROM blogs "
            "WHERE (title LIKE %s OR content LIKE %s OR excerpt LIKE %s) AND is_published = 1",
            (term, term, term),
        )
        results.extend(blogs)
    except Exception:
        pass

    return results
