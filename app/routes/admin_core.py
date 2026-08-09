"""Admin core routes: dashboard, users, courses, course form, course content,
enrollments and messages. Mirrors the legacy PHP pages under /admin."""

import json
import math
import os
import uuid
from datetime import date, datetime

from flask import Blueprint, redirect, render_template, request
from werkzeug.utils import secure_filename

from config import Config
from app.auth import admin_required
from app.db import db
from app.helpers import (
    current_user, sanitize, set_flash, table_has_column, validate_csrf_token,
)

bp = Blueprint("admin_core", __name__)

_DEFAULT_CURRICULUM = (
    '[{"title": "Module 1: Introduction", "topics": ["Overview", "Setup"]}, '
    '{"title": "Module 2: Advanced Topics", "topics": ["Performance", "Security"]}]'
)


def _fmt_date(value, fmt="%b %d, %Y"):
    """Format a DB datetime/date value, mirroring PHP date('...', strtotime(...))."""
    if value is None:
        return ""
    if isinstance(value, datetime):
        return value.strftime(fmt)
    if isinstance(value, date):
        return value.strftime(fmt)
    return str(value)


def _count(sql, params=None):
    try:
        return int(db().fetch_val(sql, params) or 0)
    except Exception:
        return 0


def _save_upload(file, upload_dir, prefix, allowed):
    """Validate and save an uploaded file. Returns (filename, ext, error)."""
    if file is None or not file.filename:
        return None, None, "Please select a file to upload."

    filename = secure_filename(file.filename or "")
    ext = os.path.splitext(filename)[1].lstrip(".").lower()

    if not ext or ext not in allowed:
        return None, None, "Invalid file type. Allowed: " + ", ".join(allowed)

    new_filename = prefix + uuid.uuid4().hex + "." + ext
    os.makedirs(upload_dir, exist_ok=True)
    file.save(os.path.join(upload_dir, new_filename))
    return new_filename, ext, None


@bp.route("/dashboard")
@admin_required
def dashboard():
    stats = {
        "users": _count("SELECT COUNT(*) FROM users"),
        "courses": _count("SELECT COUNT(*) FROM courses"),
        "enrollments": _count(
            "SELECT COUNT(*) FROM enrollments WHERE status = 'completed'"
        ),
        "pending_trainers": _count(
            "SELECT COUNT(*) FROM users WHERE role = 'trainer' AND is_approved = 0"
        ),
        "revenue": float(
            db().fetch_val(
                "SELECT SUM(amount_paid) FROM enrollments WHERE status = 'completed'"
            )
            or 0
        ),
        "success_stories": _count("SELECT COUNT(*) FROM success_stories"),
    }

    recent_enrollments = []
    try:
        recent_enrollments = db().fetch_all(
            "SELECT e.*, u.name AS user_name, c.name AS course_name "
            "FROM enrollments e "
            "JOIN users u ON e.user_id = u.id "
            "JOIN courses c ON e.course_id = c.id "
            "ORDER BY e.enrolled_at DESC LIMIT 5"
        )
    except Exception:
        recent_enrollments = []

    for row in recent_enrollments:
        row["enrolled_at_display"] = _fmt_date(row.get("enrolled_at"))

    return render_template(
        "admin/dashboard.html",
        page_title="Dashboard",
        stats=stats,
        recent_enrollments=recent_enrollments,
    )


@bp.route("/users", methods=["GET", "POST"])
@admin_required
def users():
    edit_id = request.args.get("edit", 0, type=int)

    if edit_id > 0:
        user = db().fetch_one("SELECT * FROM users WHERE id = %s", (edit_id,))
        if not user:
            set_flash("danger", "User not found.")
            return redirect("/admin/users")

        if request.method == "POST":
            if not validate_csrf_token(request.form.get("csrf_token", "")):
                set_flash("danger", "Invalid request.")
                return redirect("/admin/users")

            name = sanitize(request.form.get("name", ""))
            email = sanitize(request.form.get("email", "")).strip() or (
                user.get("email") or ""
            )
            role = sanitize(request.form.get("role", "student"))
            is_approved = 1 if request.form.get("is_approved") else 0
            is_active = 1 if request.form.get("is_active") else 0

            if not name or not email:
                set_flash("danger", "Name and email are required.")
            else:
                db().execute(
                    "UPDATE users SET name = %s, email = %s, role = %s, "
                    "is_approved = %s, is_active = %s WHERE id = %s",
                    (name, email, role, is_approved, is_active, edit_id),
                )
                set_flash("success", "User updated successfully.")
                return redirect("/admin/users")

        return render_template(
            "admin/user_edit.html", page_title="Edit User", user=user
        )

    if request.method == "POST" and request.form.get("action") and request.form.get("id"):
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid or expired action request.")
            return redirect("/admin/users")

        action = request.form.get("action")
        try:
            user_id = int(request.form.get("id"))
        except (TypeError, ValueError):
            user_id = 0

        if action == "approve_trainer":
            db().execute("UPDATE users SET is_approved = 1 WHERE id = %s", (user_id,))
            set_flash("success", "Trainer approved successfully.")
        elif action == "delete":
            admin = current_user()
            if admin and user_id != admin["id"]:
                db().execute("DELETE FROM users WHERE id = %s", (user_id,))
                set_flash("success", "User deleted successfully.")
            else:
                set_flash("danger", "You cannot delete your own account.")
        return redirect("/admin/users")

    page = max(request.args.get("page", 1, type=int), 1)
    per_page = 20
    offset = (page - 1) * per_page

    users_list = db().fetch_all(
        "SELECT * FROM users ORDER BY created_at DESC LIMIT %s OFFSET %s",
        (per_page, offset),
    )
    total_users = _count("SELECT COUNT(*) FROM users")
    total_pages = math.ceil(total_users / per_page)

    for row in users_list:
        row["created_at_display"] = _fmt_date(row.get("created_at"))

    return render_template(
        "admin/users.html",
        page_title="Users",
        users=users_list,
        page=page,
        total_pages=total_pages,
    )


@bp.route("/courses", methods=["GET", "POST"])
@admin_required
def courses():
    if request.method == "POST" and request.form.get("action") and request.form.get("id"):
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request.")
            return redirect("/admin/courses")

        action = request.form.get("action")
        try:
            course_id = int(request.form.get("id"))
        except (TypeError, ValueError):
            course_id = 0

        if action == "delete":
            enrollment_count = _count(
                "SELECT COUNT(*) FROM enrollments WHERE course_id = %s", (course_id,)
            )
            if enrollment_count > 0:
                set_flash("danger", "Cannot delete course with existing enrollments.")
            else:
                db().execute("DELETE FROM courses WHERE id = %s", (course_id,))
                set_flash("success", "Course deleted successfully.")
        return redirect("/admin/courses")

    courses_list = db().fetch_all(
        "SELECT c.*, u.name AS trainer_name FROM courses c "
        "LEFT JOIN users u ON c.trainer_id = u.id "
        "ORDER BY c.created_at DESC"
    )

    return render_template(
        "admin/courses.html", page_title="Courses", courses=courses_list
    )


@bp.route("/course_form", methods=["GET", "POST"])
@admin_required
def course_form():
    course = {}
    errors = []
    form_data = request.form if request.method == "POST" else {}

    course_id = request.args.get("id", 0, type=int)
    is_edit = course_id > 0

    if is_edit:
        course = db().fetch_one("SELECT * FROM courses WHERE id = %s", (course_id,))
        if not course:
            set_flash("danger", "Course not found.")
            return redirect("/admin/courses")
        page_title = "Edit Course: " + (course.get("name") or "")
    else:
        page_title = "Add New Course"

    trainers = db().fetch_all(
        "SELECT id, name FROM users WHERE role = 'trainer' AND is_approved = 1 "
        "ORDER BY name"
    )

    if request.method == "POST":
        name = sanitize(request.form.get("name", ""))
        description = request.form.get("description", "")
        summary = sanitize(request.form.get("summary", ""))
        try:
            price = float(request.form.get("price", 0) or 0)
        except (TypeError, ValueError):
            price = 0.0
        duration = sanitize(request.form.get("duration", ""))
        batch_timing = sanitize(request.form.get("batch_timing", ""))
        live_link = sanitize(request.form.get("live_link", ""))
        level = sanitize(request.form.get("level", ""))
        raw_trainer = request.form.get("trainer_id", "")
        trainer_id = int(raw_trainer) if raw_trainer else None
        curriculum = request.form.get("curriculum", "[]")
        is_featured = 1 if request.form.get("is_featured") else 0

        if not name:
            errors.append("Course Name is required")
        if price < 0:
            errors.append("Price cannot be negative")

        try:
            json.loads(curriculum)
        except (ValueError, TypeError):
            errors.append("Invalid Curriculum JSON format")

        image = course.get("image") if course else None
        image_file = request.files.get("image")
        if image_file is not None and image_file.filename:
            allowed = ["jpg", "jpeg", "png", "webp"]
            new_filename, _, upload_error = _save_upload(
                image_file,
                os.path.join(Config.BASE_PATH, "assets", "images"),
                "course_",
                allowed,
            )
            if upload_error:
                errors.append(upload_error)
            elif new_filename:
                image = new_filename

        if not errors:
            try:
                if is_edit:
                    db().execute(
                        "UPDATE courses SET name = %s, description = %s, summary = %s, "
                        "price = %s, duration = %s, batch_timing = %s, live_link = %s, "
                        "level = %s, trainer_id = %s, image = %s, curriculum = %s, "
                        "is_featured = %s WHERE id = %s",
                        (
                            name, description, summary, price, duration, batch_timing,
                            live_link, level, trainer_id, image, curriculum,
                            is_featured, course_id,
                        ),
                    )
                    set_flash("success", "Course updated successfully.")
                else:
                    db().insert(
                        "INSERT INTO courses (name, description, summary, price, "
                        "duration, batch_timing, live_link, level, trainer_id, image, "
                        "curriculum, is_featured, created_at) "
                        "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())",
                        (
                            name, description, summary, price, duration, batch_timing,
                            live_link, level, trainer_id, image, curriculum,
                            is_featured,
                        ),
                    )
                    set_flash("success", "Course created successfully.")
                return redirect("/admin/courses")
            except Exception:
                errors.append("Database Error: could not save course.")

    return render_template(
        "admin/course_form.html",
        page_title=page_title,
        course=course,
        errors=errors,
        form_data=form_data,
        trainers=trainers,
        is_edit=is_edit,
        default_curriculum=_DEFAULT_CURRICULUM,
    )


@bp.route("/course-content", methods=["GET", "POST"])
@admin_required
def course_content():
    course_id = request.args.get("course_id", type=int) or request.args.get("id", type=int) or 0
    if course_id <= 0:
        set_flash("danger", "Invalid Course ID.")
        return redirect("/admin/courses")

    course = db().fetch_one("SELECT * FROM courses WHERE id = %s", (course_id,))
    if not course:
        set_flash("danger", "Course not found.")
        return redirect("/admin/courses")

    page_title = "Manage Content: " + (course.get("name") or "")

    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request.")
            return redirect("/admin/course-content?course_id=%s" % course_id)

        if request.form.get("add_resource"):
            title = sanitize(request.form.get("resource_title", ""))
            if not title:
                set_flash("danger", "Resource title is required.")
            else:
                allowed = [
                    "pdf", "ppt", "pptx", "zip", "rar", "doc", "docx",
                    "txt", "csv", "xlsx", "png", "jpg",
                ]
                new_filename, ext, upload_error = _save_upload(
                    request.files.get("resource_file"),
                    os.path.join(Config.BASE_PATH, "assets", "uploads", "resources"),
                    "res_",
                    allowed,
                )
                if upload_error:
                    set_flash("danger", upload_error)
                elif new_filename:
                    db().insert(
                        "INSERT INTO course_resources (course_id, title, file_path, file_type) "
                        "VALUES (%s, %s, %s, %s)",
                        (course_id, title, new_filename, ext),
                    )
                    set_flash("success", "Resource uploaded successfully.")
            return redirect("/admin/course-content?course_id=%s" % course_id)

        if request.form.get("add_recording"):
            title = sanitize(request.form.get("recording_title", ""))
            url = sanitize(request.form.get("recording_url", ""))
            if not title or not url:
                set_flash("danger", "All recording fields are required.")
            else:
                db().insert(
                    "INSERT INTO course_recordings (course_id, title, recording_url) "
                    "VALUES (%s, %s, %s)",
                    (course_id, title, url),
                )
                set_flash("success", "Recording added successfully.")
            return redirect("/admin/course-content?course_id=%s" % course_id)

        if request.form.get("delete_resource"):
            try:
                res_id = int(request.form.get("delete_resource"))
            except (TypeError, ValueError):
                res_id = 0
            res = db().fetch_one(
                "SELECT file_path FROM course_resources WHERE id = %s AND course_id = %s",
                (res_id, course_id),
            )
            if res:
                file_path = os.path.join(
                    Config.BASE_PATH, "assets", "uploads", "resources", res["file_path"]
                )
                try:
                    if os.path.exists(file_path):
                        os.remove(file_path)
                except OSError:
                    pass
                db().execute(
                    "DELETE FROM course_resources WHERE id = %s", (res_id,)
                )
                set_flash("success", "Resource deleted.")
            return redirect("/admin/course-content?course_id=%s" % course_id)

        if request.form.get("delete_recording"):
            try:
                rec_id = int(request.form.get("delete_recording"))
            except (TypeError, ValueError):
                rec_id = 0
            db().execute(
                "DELETE FROM course_recordings WHERE id = %s AND course_id = %s",
                (rec_id, course_id),
            )
            set_flash("success", "Recording deleted.")
            return redirect("/admin/course-content?course_id=%s" % course_id)

    recordings = db().fetch_all(
        "SELECT * FROM course_recordings WHERE course_id = %s ORDER BY created_at DESC",
        (course_id,),
    )
    resources = db().fetch_all(
        "SELECT * FROM course_resources WHERE course_id = %s ORDER BY created_at DESC",
        (course_id,),
    )

    for rec in recordings:
        rec["created_at_display"] = _fmt_date(rec.get("created_at"), "%d %b %Y")
    for res in resources:
        res["created_at_display"] = _fmt_date(res.get("created_at"), "%d %b %Y")

    return render_template(
        "admin/course_content.html",
        page_title=page_title,
        course=course,
        course_id=course_id,
        recordings=recordings,
        resources=resources,
    )


@bp.route("/enrollments", methods=["GET", "POST"])
@admin_required
def enrollments():
    if request.method == "POST" and request.form.get("action") and request.form.get("id"):
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request.")
            return redirect("/admin/enrollments")

        action = request.form.get("action")
        try:
            enrollment_id = int(request.form.get("id"))
        except (TypeError, ValueError):
            enrollment_id = 0

        if action == "verify_payment":
            db().execute(
                "UPDATE enrollments SET status = 'completed', verified_at = NOW() "
                "WHERE id = %s",
                (enrollment_id,),
            )
            set_flash("success", "Payment verified and enrollment completed.")
        elif action == "delete":
            db().execute("DELETE FROM enrollments WHERE id = %s", (enrollment_id,))
            set_flash("success", "Enrollment deleted successfully.")
        return redirect("/admin/enrollments")

    page = max(request.args.get("page", 1, type=int), 1)
    per_page = 20
    offset = (page - 1) * per_page

    enrollments_list = db().fetch_all(
        "SELECT e.*, u.name AS user_name, u.email AS user_email, c.name AS course_name "
        "FROM enrollments e "
        "JOIN users u ON e.user_id = u.id "
        "JOIN courses c ON e.course_id = c.id "
        "ORDER BY e.enrolled_at DESC LIMIT %s OFFSET %s",
        (per_page, offset),
    )
    total_enrollments = _count("SELECT COUNT(*) FROM enrollments")
    total_pages = math.ceil(total_enrollments / per_page)

    for row in enrollments_list:
        row["enrolled_at_display"] = _fmt_date(row.get("enrolled_at"))

    return render_template(
        "admin/enrollments.html",
        page_title="Enrollments",
        enrollments=enrollments_list,
        page=page,
        total_pages=total_pages,
    )


@bp.route("/messages", methods=["GET", "POST"])
@admin_required
def messages():
    has_is_read = table_has_column("contact_messages", "is_read")

    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request.")
            return redirect("/admin/messages")

        action = request.form.get("action")
        try:
            message_id = int(request.form.get("id"))
        except (TypeError, ValueError):
            message_id = 0

        if action == "delete":
            db().execute("DELETE FROM contact_messages WHERE id = %s", (message_id,))
            set_flash("success", "Message deleted successfully.")
        elif action == "mark_read" and has_is_read:
            db().execute(
                "UPDATE contact_messages SET is_read = 1 WHERE id = %s", (message_id,)
            )
            set_flash("success", "Message marked as read.")
        return redirect("/admin/messages")

    messages_list = db().fetch_all(
        "SELECT * FROM contact_messages ORDER BY created_at DESC"
    )
    for row in messages_list:
        row["created_at_display"] = _fmt_date(row.get("created_at"), "%b %d, %Y %I:%M %p")
        row["created_at_full_display"] = _fmt_date(
            row.get("created_at"), "%B %d, %Y %I:%M %p"
        )

    return render_template(
        "admin/messages.html",
        page_title="Messages",
        messages=messages_list,
        has_is_read=has_is_read,
    )
