"""Admin content routes (port of admin/*.php content pages).

Registered under url_prefix "/admin" in app/__init__.py, so all route paths
here are relative (e.g. "/blogs" -> "/admin/blogs").
"""

import json
import os
import re
import time
import uuid
from datetime import datetime
from urllib.parse import urlparse

from flask import Blueprint, redirect, render_template, request, session
from werkzeug.utils import secure_filename

from config import Config
from app.auth import admin_required
from app.db import db
from app.helpers import (
    generate_slug,
    sanitize,
    set_flash,
    table_has_column,
    validate_csrf_token,
)

bp = Blueprint("admin_content", __name__)

ALLOWED_IMAGE_EXTENSIONS = {"jpg", "jpeg", "png", "webp"}


# ---------------------------------------------------------------------------
# Small helpers
# ---------------------------------------------------------------------------

def _int_param(name, default=0):
    try:
        return int(request.args.get(name, default))
    except (TypeError, ValueError):
        return default


def _form_int(name, default=0):
    raw = request.form.get(name, "")
    if raw is None or raw == "":
        return default
    try:
        return int(raw)
    except (TypeError, ValueError):
        return default


def _form_float(name, default=0.0):
    raw = request.form.get(name, "")
    if raw is None or raw == "":
        return default
    try:
        return float(raw)
    except (TypeError, ValueError):
        return default


def _valid_url(value):
    if not value:
        return ""
    parsed = urlparse(value)
    if parsed.scheme in ("http", "https") and parsed.netloc:
        return value
    return ""


def _success_story_image_full_path(relative_path):
    if not relative_path:
        return None
    normalized = str(relative_path).replace("\\", "/").lstrip("/")
    if not normalized.startswith("success-stories/"):
        return None
    return os.path.join(Config.BASE_PATH, "assets", "images", normalized)


def _save_uploaded_image(file, prefix, subdir="", allowed=None, max_size=None):
    """Save an uploaded image file like PHP move_uploaded_file().

    Returns (stored_filename, error_message); (None, None) when no file present.
    """
    if file is None or not file.filename:
        return None, None

    allowed = allowed or ALLOWED_IMAGE_EXTENSIONS
    safe_name = secure_filename(file.filename)
    ext = os.path.splitext(safe_name)[1].lstrip(".").lower()

    if ext not in allowed:
        return None, "Invalid file type. Allowed: {}".format(", ".join(sorted(allowed)))

    if max_size:
        file.seek(0, 2)
        size = file.tell()
        file.seek(0)
        if size > max_size:
            return None, "File is too large. Maximum allowed size is {}MB.".format(max_size // (1024 * 1024))

    upload_dir = os.path.join(Config.BASE_PATH, "assets", "images", subdir)
    os.makedirs(upload_dir, exist_ok=True)

    new_filename = "{}{}.{}".format(prefix, uuid.uuid4().hex, ext)
    file.save(os.path.join(upload_dir, new_filename))
    return new_filename, None


# ---------------------------------------------------------------------------
# Blogs
# ---------------------------------------------------------------------------

@bp.route("/blogs", methods=["GET", "POST"])
@admin_required
def blogs():
    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request.")
            return redirect("/admin/blogs")

        if request.form.get("action") == "delete":
            try:
                db().execute("DELETE FROM blogs WHERE id = %s", (_form_int("id"),))
                set_flash("success", "Blog post deleted successfully.")
            except Exception:
                set_flash("danger", "Error deleting blog post. Please try again.")
        return redirect("/admin/blogs")

    try:
        blogs = db().fetch_all(
            "SELECT b.*, u.name AS author_name, u.profile_image AS author_image "
            "FROM blogs b LEFT JOIN users u ON b.author_id = u.id "
            "ORDER BY b.created_at DESC"
        )
    except Exception:
        try:
            blogs = db().fetch_all("SELECT * FROM blogs ORDER BY created_at DESC")
            for blog in blogs:
                blog["author_name"] = blog.get("author") or "Admin"
                blog["author_image"] = None
        except Exception:
            blogs = []

    return render_template("admin/blogs.html", page_title="Blog Management", blogs=blogs)


@bp.route("/blog_form", methods=["GET", "POST"])
@admin_required
def blog_form():
    errors = []
    blog_id = _int_param("id")
    is_edit = blog_id > 0

    blog = {}
    if is_edit:
        blog = db().fetch_one("SELECT * FROM blogs WHERE id = %s", (blog_id,))
        if not blog:
            set_flash("danger", "Blog post not found.")
            return redirect("/admin/blogs")

    form_data = dict(blog) if blog else {}

    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            errors.append("Invalid request. Please try again.")
        else:
            title = sanitize(request.form.get("title", ""))
            slug = sanitize(request.form.get("slug", "") or generate_slug(title))
            excerpt = sanitize(request.form.get("excerpt", ""))
            content = request.form.get("content", "")
            author = sanitize(request.form.get("author", "") or session.get("user_name") or "Admin")
            author_id = session.get("user_id")
            is_published = 1 if request.form.get("is_published") else 0
            date = datetime.now().strftime("%Y-%m-%d")

            if not title:
                errors.append("Title is required")
            if not content:
                errors.append("Content is required")

            image = blog.get("image") if is_edit else None
            uploaded, upload_error = _save_uploaded_image(request.files.get("image"), "blog_")
            if upload_error:
                errors.append(upload_error)
            elif uploaded:
                image = uploaded

            if not errors:
                try:
                    if is_edit:
                        db().execute(
                            "UPDATE blogs SET title=%s, slug=%s, excerpt=%s, content=%s, image=%s, "
                            "author=%s, author_id=%s, is_published=%s WHERE id=%s",
                            (title, slug, excerpt, content, image, author, author_id, is_published, blog_id),
                        )
                        set_flash("success", "Blog post updated successfully.")
                    else:
                        db().insert(
                            "INSERT INTO blogs (title, slug, excerpt, content, image, author, author_id, "
                            "date, is_published, created_at) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())",
                            (title, slug, excerpt, content, image, author, author_id, date, is_published),
                        )
                        set_flash("success", "Blog post created successfully.")
                    return redirect("/admin/blogs")
                except Exception:
                    errors.append("Failed to save the blog post. Please try again.")

            for field in ("title", "slug", "excerpt", "content", "author"):
                if field in request.form:
                    form_data[field] = request.form.get(field)
            if "is_published" in request.form:
                form_data["is_published"] = 1

    return render_template(
        "admin/blog_form.html",
        page_title="Manage Blog Post",
        blog=blog,
        errors=errors,
        form_data=form_data,
        is_edit=is_edit,
    )


# ---------------------------------------------------------------------------
# Coupons
# ---------------------------------------------------------------------------

@bp.route("/coupons", methods=["GET", "POST"])
@admin_required
def coupons():
    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request.")
            return redirect("/admin/coupons")

        if request.form.get("add_coupon"):
            code = (request.form.get("code", "") or "").strip().upper()
            discount_type = request.form.get("discount_type", "")
            discount_value = _form_float("discount_value")
            min_purchase = _form_float("min_purchase")
            max_uses_raw = request.form.get("max_uses", "").strip()
            try:
                max_uses = int(max_uses_raw) if max_uses_raw else None
            except ValueError:
                max_uses = None
            valid_from = request.form.get("valid_from", "") or None
            valid_until = request.form.get("valid_until", "") or None

            if not code or not discount_value:
                set_flash("danger", "Please fill all required fields")
            else:
                try:
                    db().insert(
                        "INSERT INTO coupons (code, discount_type, discount_value, min_purchase, "
                        "max_uses, valid_from, valid_until) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                        (code, discount_type, discount_value, min_purchase, max_uses, valid_from, valid_until),
                    )
                    set_flash("success", "Coupon created successfully!")
                except Exception as exc:
                    if getattr(exc, "args", None) and exc.args and exc.args[0] == 1062:
                        set_flash("danger", "Coupon code already exists")
                    else:
                        set_flash("danger", "Error creating coupon. Please try again.")
        elif request.form.get("toggle_coupon"):
            db().execute(
                "UPDATE coupons SET is_active = %s WHERE id = %s",
                (1 if request.form.get("is_active") == "1" else 0, _form_int("coupon_id")),
            )
            set_flash("success", "Coupon status updated!")
        elif request.form.get("delete_coupon"):
            db().execute("DELETE FROM coupons WHERE id = %s", (_form_int("coupon_id"),))
            set_flash("success", "Coupon deleted!")
        return redirect("/admin/coupons")

    coupons = db().fetch_all("SELECT * FROM coupons ORDER BY created_at DESC")
    return render_template("admin/coupons.html", page_title="Manage Coupons", coupons=coupons)


# ---------------------------------------------------------------------------
# Career tracks
# ---------------------------------------------------------------------------

@bp.route("/career_tracks")
@admin_required
def career_tracks():
    action = request.args.get("action")
    track_id = _int_param("id")

    if action and track_id:
        if action == "delete":
            db().execute("DELETE FROM career_tracks WHERE id = %s", (track_id,))
            set_flash("success", "Career track deleted successfully.")
        elif action == "toggle_featured":
            db().execute("UPDATE career_tracks SET is_featured = NOT is_featured WHERE id = %s", (track_id,))
            set_flash("success", "Featured status updated.")
        elif action == "toggle_active":
            db().execute("UPDATE career_tracks SET is_active = NOT is_active WHERE id = %s", (track_id,))
            set_flash("success", "Status updated.")
        return redirect("/admin/career_tracks")

    tracks = db().fetch_all("SELECT * FROM career_tracks ORDER BY sort_order ASC, created_at DESC")
    for track in tracks:
        track["course_count"] = int(
            db().fetch_val(
                "SELECT COUNT(*) FROM career_track_courses WHERE track_id = %s", (track["id"],)
            )
            or 0
        )
    return render_template("admin/career_tracks.html", page_title="Career Track Programs", tracks=tracks)


@bp.route("/career_track_form", methods=["GET", "POST"])
@admin_required
def career_track_form():
    errors = []
    track_id = _int_param("id")
    is_edit = track_id > 0

    track = {}
    if is_edit:
        track = db().fetch_one("SELECT * FROM career_tracks WHERE id = %s", (track_id,))
        if not track:
            set_flash("danger", "Career track not found.")
            return redirect("/admin/career_tracks")

    courses = db().fetch_all("SELECT id, name, price FROM courses ORDER BY name")

    selected_course_ids = []
    if is_edit:
        rows = db().fetch_all(
            "SELECT course_id FROM career_track_courses WHERE track_id = %s ORDER BY sort_order",
            (track_id,),
        )
        selected_course_ids = [str(row["course_id"]) for row in rows]

    form_data = dict(track) if track else {}

    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            errors.append("Invalid request. Please refresh and try again.")

        name = sanitize(request.form.get("name", ""))
        slug = sanitize(request.form.get("slug", ""))
        description = request.form.get("description", "")
        summary = sanitize(request.form.get("summary", ""))
        duration = sanitize(request.form.get("duration", ""))
        original_price = _form_float("original_price")
        price = _form_float("price")
        badge = sanitize(request.form.get("badge", ""))
        badge_color = sanitize(request.form.get("badge_color", "primary"))
        outcomes = request.form.get("outcomes", "")
        requirements = request.form.get("requirements", "")
        curriculum = request.form.get("curriculum", "[]")
        is_active = 1 if request.form.get("is_active") else 0
        is_featured = 1 if request.form.get("is_featured") else 0
        sort_order = _form_int("sort_order")
        selected_course_ids = [cid for cid in request.form.getlist("courses")]

        if not name:
            errors.append("Track Name is required")

        if not slug:
            slug = generate_slug(name)
        else:
            slug = generate_slug(slug)

        if is_edit:
            duplicate = db().fetch_val(
                "SELECT id FROM career_tracks WHERE slug = %s AND id != %s", (slug, track_id)
            )
        else:
            duplicate = db().fetch_val("SELECT id FROM career_tracks WHERE slug = %s", (slug,))
        if duplicate:
            errors.append("Slug already exists. Please use a different slug.")

        if price < 0:
            errors.append("Price cannot be negative")

        try:
            json.loads(curriculum)
        except (TypeError, ValueError):
            errors.append("Invalid Curriculum JSON format")

        image = track.get("image") if is_edit else None
        uploaded, upload_error = _save_uploaded_image(request.files.get("image"), "track_")
        if upload_error:
            errors.append(upload_error)
        elif uploaded:
            image = uploaded

        if not errors:
            try:
                if is_edit:
                    db().execute(
                        "UPDATE career_tracks SET name=%s, slug=%s, description=%s, summary=%s, duration=%s, "
                        "original_price=%s, price=%s, badge=%s, badge_color=%s, image=%s, outcomes=%s, "
                        "requirements=%s, curriculum=%s, is_active=%s, is_featured=%s, sort_order=%s WHERE id=%s",
                        (name, slug, description, summary, duration, original_price, price, badge, badge_color,
                         image, outcomes, requirements, curriculum, is_active, is_featured, sort_order, track_id),
                    )
                    db().execute("DELETE FROM career_track_courses WHERE track_id = %s", (track_id,))
                else:
                    track_id = db().insert(
                        "INSERT INTO career_tracks (name, slug, description, summary, duration, original_price, "
                        "price, badge, badge_color, image, outcomes, requirements, curriculum, is_active, "
                        "is_featured, sort_order, created_at) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, "
                        "%s, %s, %s, %s, %s, %s, NOW())",
                        (name, slug, description, summary, duration, original_price, price, badge, badge_color,
                         image, outcomes, requirements, curriculum, is_active, is_featured, sort_order),
                    )

                for index, course_id in enumerate(selected_course_ids):
                    db().insert(
                        "INSERT INTO career_track_courses (track_id, course_id, sort_order, is_required) "
                        "VALUES (%s, %s, %s, 1)",
                        (track_id, course_id, index),
                    )

                set_flash("success", "Career track {} successfully.".format("updated" if is_edit else "created"))
                return redirect("/admin/career_tracks")
            except Exception:
                errors.append("Failed to save the career track. Please try again.")

        for field in ("name", "slug", "description", "summary", "duration", "badge", "badge_color",
                      "outcomes", "requirements", "curriculum", "sort_order", "original_price", "price"):
            if field in request.form:
                form_data[field] = request.form.get(field)
        if "is_active" in request.form:
            form_data["is_active"] = 1
        if "is_featured" in request.form:
            form_data["is_featured"] = 1

    return render_template(
        "admin/career_track_form.html",
        page_title="Manage Career Track",
        track=track,
        errors=errors,
        form_data=form_data,
        is_edit=is_edit,
        courses=courses,
        selected_course_ids=selected_course_ids,
        colors=["primary", "success", "warning", "danger", "info", "secondary"],
    )


# ---------------------------------------------------------------------------
# Success stories
# ---------------------------------------------------------------------------

@bp.route("/success_stories")
@admin_required
def success_stories():
    has_photo_path = table_has_column("success_stories", "photo_path")
    action = request.args.get("action")
    story_id = _int_param("id")

    if action == "delete" and story_id:
        try:
            if has_photo_path:
                story = db().fetch_one("SELECT photo_path FROM success_stories WHERE id = %s", (story_id,))
                old_file = _success_story_image_full_path((story or {}).get("photo_path"))
                if old_file and os.path.exists(old_file):
                    os.remove(old_file)
            db().execute("DELETE FROM success_stories WHERE id = %s", (story_id,))
            set_flash("success", "Success story deleted successfully.")
        except Exception:
            set_flash("danger", "Error deleting success story.")
        return redirect("/admin/success_stories")

    if action == "toggle" and story_id:
        try:
            db().execute("UPDATE success_stories SET is_active = NOT is_active WHERE id = %s", (story_id,))
            set_flash("success", "Status updated.")
        except Exception:
            set_flash("danger", "Error updating status.")
        return redirect("/admin/success_stories")

    try:
        stories = db().fetch_all("SELECT * FROM success_stories ORDER BY sort_order ASC, created_at DESC")
    except Exception:
        stories = []

    return render_template(
        "admin/success_stories.html",
        page_title="Success Stories Management",
        stories=stories,
        has_photo_path=has_photo_path,
    )


@bp.route("/success_story_form", methods=["GET", "POST"])
@admin_required
def success_story_form():
    errors = []
    has_photo_path = table_has_column("success_stories", "photo_path")
    story_id = _int_param("id")
    is_edit = story_id > 0

    story = {}
    if is_edit:
        story = db().fetch_one("SELECT * FROM success_stories WHERE id = %s", (story_id,))
        if not story:
            set_flash("danger", "Success story not found.")
            return redirect("/admin/success_stories")

    form_data = dict(story) if story else {}

    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            errors.append("Invalid request. Please refresh and try again.")

        name = sanitize(request.form.get("name", ""))
        title = sanitize(request.form.get("title", ""))
        content = sanitize(request.form.get("content", ""))
        rating = _form_int("rating", 5)
        avatar_bg = sanitize(request.form.get("avatar_bg", "bg-primary"))
        is_active = 1 if request.form.get("is_active") else 0
        sort_order = _form_int("sort_order")
        remove_photo = "remove_photo" in request.form
        photo_path = story.get("photo_path") if is_edit else None

        if not name:
            errors.append("Name is required")
        if not title:
            errors.append("Title is required")
        if not content:
            errors.append("Content is required")
        if rating < 1 or rating > 5:
            errors.append("Rating must be between 1 and 5")

        if has_photo_path:
            photo = request.files.get("photo")
            if photo and photo.filename:
                photo.seek(0, 2)
                size = photo.tell()
                photo.seek(0)
                if size > 2 * 1024 * 1024:
                    errors.append("Photo is too large. Maximum size is 2MB.")
                else:
                    ext = os.path.splitext(secure_filename(photo.filename))[1].lstrip(".").lower()
                    if ext not in ALLOWED_IMAGE_EXTENSIONS:
                        errors.append("Invalid photo type. Allowed: jpg, jpeg, png, webp.")
                    else:
                        upload_dir = os.path.join(Config.BASE_PATH, "assets", "images", "success-stories")
                        os.makedirs(upload_dir, exist_ok=True)
                        new_filename = "success_story_{}_{}.{}".format(story_id or "new", int(time.time()), ext)
                        photo.save(os.path.join(upload_dir, new_filename))
                        old_file = _success_story_image_full_path(photo_path)
                        if old_file and os.path.exists(old_file):
                            os.remove(old_file)
                        photo_path = "success-stories/" + new_filename
            elif remove_photo and photo_path:
                old_file = _success_story_image_full_path(photo_path)
                if old_file and os.path.exists(old_file):
                    os.remove(old_file)
                photo_path = None

        if not errors:
            try:
                if is_edit:
                    if has_photo_path:
                        db().execute(
                            "UPDATE success_stories SET name=%s, title=%s, content=%s, rating=%s, photo_path=%s, "
                            "avatar_bg=%s, is_active=%s, sort_order=%s WHERE id=%s",
                            (name, title, content, rating, photo_path, avatar_bg, is_active, sort_order, story_id),
                        )
                    else:
                        db().execute(
                            "UPDATE success_stories SET name=%s, title=%s, content=%s, rating=%s, avatar_bg=%s, "
                            "is_active=%s, sort_order=%s WHERE id=%s",
                            (name, title, content, rating, avatar_bg, is_active, sort_order, story_id),
                        )
                    set_flash("success", "Success story updated successfully.")
                else:
                    if has_photo_path:
                        db().insert(
                            "INSERT INTO success_stories (name, title, content, rating, photo_path, avatar_bg, "
                            "is_active, sort_order) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                            (name, title, content, rating, photo_path, avatar_bg, is_active, sort_order),
                        )
                    else:
                        db().insert(
                            "INSERT INTO success_stories (name, title, content, rating, avatar_bg, is_active, "
                            "sort_order) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                            (name, title, content, rating, avatar_bg, is_active, sort_order),
                        )
                    set_flash("success", "Success story created successfully.")
                return redirect("/admin/success_stories")
            except Exception:
                errors.append("Failed to save the success story. Please try again.")

        for field in ("name", "title", "content", "rating", "avatar_bg", "sort_order"):
            if field in request.form:
                form_data[field] = request.form.get(field)
        if "is_active" in request.form:
            form_data["is_active"] = 1
        form_data["remove_photo"] = remove_photo

    return render_template(
        "admin/success_story_form.html",
        page_title="Manage Success Story",
        story=story,
        errors=errors,
        form_data=form_data,
        is_edit=is_edit,
        has_photo_path=has_photo_path,
    )


# ---------------------------------------------------------------------------
# Internships
# ---------------------------------------------------------------------------

@bp.route("/internships")
@admin_required
def internships():
    action = request.args.get("action")
    internship_id = _int_param("id")

    if action == "delete" and internship_id:
        db().execute("DELETE FROM internships WHERE id = %s", (internship_id,))
        set_flash("success", "Internship deleted successfully.")
        return redirect("/admin/internships")

    internships = db().fetch_all(
        "SELECT * FROM internships WHERE category = 'industrial' ORDER BY created_at DESC"
    )
    return render_template("admin/internships.html", page_title="Student Internships", internships=internships)


@bp.route("/internship_form", methods=["GET", "POST"])
@admin_required
def internship_form():
    errors = []
    internship_id = _int_param("id")
    is_edit = internship_id > 0

    internship = {}
    if is_edit:
        internship = db().fetch_one("SELECT * FROM internships WHERE id = %s", (internship_id,))
        if not internship:
            set_flash("danger", "Internship not found.")
            return redirect("/admin/internships")

    form_data = dict(internship) if internship else {}

    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            errors.append("Invalid request. Please refresh and try again.")

        title = sanitize(request.form.get("title", ""))
        description = request.form.get("description", "")
        duration = sanitize(request.form.get("duration", ""))
        skills_covered = sanitize(request.form.get("skills_covered", ""))
        category = sanitize(request.form.get("category", "industrial"))
        google_form_link = _valid_url(request.form.get("google_form_link", ""))
        is_active = 1 if request.form.get("is_active") else 0

        if not title:
            errors.append("Internship Title is required")
        if not duration:
            errors.append("Duration is required")
        if category not in ("teaching", "industrial"):
            errors.append("Invalid category selected.")
        if request.form.get("google_form_link") and not google_form_link:
            errors.append("Invalid Google Form Link URL.")

        if not errors:
            try:
                if is_edit:
                    db().execute(
                        "UPDATE internships SET title=%s, description=%s, duration=%s, skills_covered=%s, "
                        "category=%s, google_form_link=%s, is_active=%s, updated_at=NOW() WHERE id=%s",
                        (title, description, duration, skills_covered, category, google_form_link, is_active,
                         internship_id),
                    )
                    set_flash("success", "Internship updated successfully.")
                else:
                    db().insert(
                        "INSERT INTO internships (title, description, duration, skills_covered, category, "
                        "google_form_link, is_active, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s, "
                        "%s, NOW(), NOW())",
                        (title, description, duration, skills_covered, category, google_form_link, is_active),
                    )
                    set_flash("success", "Internship created successfully.")
                return redirect("/admin/internships")
            except Exception:
                errors.append("Failed to save the internship. Please try again.")

        for field in ("title", "description", "duration", "skills_covered", "category", "google_form_link"):
            if field in request.form:
                form_data[field] = request.form.get(field)
        if "is_active" in request.form:
            form_data["is_active"] = 1

    return render_template(
        "admin/internship_form.html",
        page_title="Manage Internship",
        internship=internship,
        errors=errors,
        form_data=form_data,
        is_edit=is_edit,
    )


# ---------------------------------------------------------------------------
# Site settings
# ---------------------------------------------------------------------------

@bp.route("/settings", methods=["GET", "POST"])
@admin_required
def settings():
    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request.")
            return redirect("/admin/settings")

        errors = []
        for key, value in request.form.items():
            match = re.match(r"^settings\[(.+)\]$", key)
            if match:
                try:
                    db().execute(
                        "INSERT INTO site_settings (`key`, `value`) VALUES (%s, %s) "
                        "ON DUPLICATE KEY UPDATE `value` = %s",
                        (match.group(1), value, value),
                    )
                except Exception:
                    errors.append("Failed to update {}.".format(match.group(1)))

        upload_dir = os.path.join(Config.BASE_PATH, "assets", "images")
        os.makedirs(upload_dir, exist_ok=True)

        image_fields = ["site_logo", "site_favicon", "hero_background"]
        allowed = {"jpg", "jpeg", "png", "gif", "ico", "webp", "svg"}
        upload_errors = []
        upload_success = []

        for field in image_fields:
            file = request.files.get(field)
            if file and file.filename:
                ext = os.path.splitext(secure_filename(file.filename))[1].lstrip(".").lower()
                if ext in allowed:
                    if field == "site_favicon":
                        new_filename = "favicon." + ext
                    elif field == "site_logo":
                        new_filename = "logo." + ext
                    else:
                        new_filename = "hero-bg." + ext
                    try:
                        file.save(os.path.join(upload_dir, new_filename))
                        db().execute(
                            "INSERT INTO site_settings (`key`, `value`) VALUES (%s, %s) "
                            "ON DUPLICATE KEY UPDATE `value` = %s",
                            (field, new_filename, new_filename),
                        )
                        upload_success.append("{} uploaded: {}".format(field, new_filename))
                    except Exception:
                        upload_errors.append("Failed to move {}. Check folder permissions.".format(field))
                else:
                    upload_errors.append("Invalid file type for {}: {}".format(field, ext))

            if request.form.get("remove_" + field):
                db().execute(
                    "INSERT INTO site_settings (`key`, `value`) VALUES (%s, %s) "
                    "ON DUPLICATE KEY UPDATE `value` = %s",
                    (field, "", ""),
                )

        if upload_errors:
            set_flash("danger", " | ".join(upload_errors))
        elif upload_success:
            set_flash("success", "Settings updated! " + " | ".join(upload_success))
        elif errors:
            set_flash("danger", " | ".join(errors))
        else:
            set_flash("success", "Settings updated successfully.")
        return redirect("/admin/settings")

    rows = db().fetch_all("SELECT `key`, `value` FROM site_settings")
    current_settings = {row["key"]: row["value"] for row in rows}
    return render_template("admin/settings.html", page_title="Site Settings", current_settings=current_settings)

