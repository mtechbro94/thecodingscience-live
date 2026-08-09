"""Student routes: dashboard, profile (with photo upload), course content."""

import os
import time

from flask import Blueprint, flash, redirect, render_template, request, session
from werkzeug.utils import secure_filename

from config import Config
from app.auth import login_required
from app.db import db
from app.helpers import current_user, is_trainer, sanitize, set_flash

bp = Blueprint("student", __name__)

ALLOWED_IMAGE_TYPES = {"image/jpeg", "image/png", "image/gif", "image/webp"}
MAX_IMAGE_SIZE = 5 * 1024 * 1024


@bp.route("/dashboard")
@login_required
def dashboard():
    user = current_user()

    if user["role"] == "admin":
        return redirect("/admin/dashboard")

    enrollments = db().fetch_all(
        "SELECT e.*, c.name as course_name, c.image as course_image, c.price as course_price "
        "FROM enrollments e JOIN courses c ON e.course_id = c.id "
        "WHERE e.user_id = %s ORDER BY e.enrolled_at DESC",
        (user["id"],),
    )

    completed_count = sum(1 for e in enrollments if e["status"] == "completed")

    course_timing = {}
    live_links = {}
    for e in enrollments:
        if e["status"] == "completed":
            course_id = e["course_id"]
            if course_id not in course_timing:
                course = db().fetch_one(
                    "SELECT batch_timing, live_link FROM courses WHERE id = %s", (course_id,)
                )
                course_timing[course_id] = (course or {}).get("batch_timing")
                live_links[course_id] = (course or {}).get("live_link")

    return render_template(
        "student/dashboard.html",
        page_title="Dashboard",
        user=user,
        enrollments=enrollments,
        completed_count=completed_count,
        course_timing=course_timing,
        live_links=live_links,
    )


@bp.route("/profile", methods=["GET", "POST"])
@login_required
def profile():
    user = current_user()
    user_data = db().fetch_one("SELECT * FROM users WHERE id = %s", (user["id"],))

    if request.method == "POST":
        name = sanitize(request.form.get("name", ""))
        phone = sanitize(request.form.get("phone", ""))
        education = sanitize(request.form.get("education", "")) or None
        expertise = sanitize(request.form.get("expertise", "")) or None
        bio = sanitize(request.form.get("bio", "")) or None

        error_msg = ""
        if not name:
            error_msg = "Name is required"

        profile_image = None
        file = request.files.get("profile_photo")
        if file and file.filename:
            if file.mimetype not in ALLOWED_IMAGE_TYPES:
                error_msg = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed."
            else:
                file.seek(0, os.SEEK_END)
                size = file.tell()
                file.seek(0)
                if size > MAX_IMAGE_SIZE:
                    error_msg = "File is too large. Maximum 5MB allowed."
                else:
                    upload_dir = os.path.join(Config.BASE_PATH, "assets", "images", "profiles")
                    os.makedirs(upload_dir, exist_ok=True)

                    role_prefix = "trainer" if is_trainer() else "student"
                    file_ext = (file.filename.rsplit(".", 1)[-1] if "." in file.filename else "").lower()
                    filename = f"{role_prefix}_{user['id']}_{int(time.time())}.{file_ext}"
                    filepath = os.path.join(upload_dir, filename)
                    file.save(filepath)

                    old_data = db().fetch_one(
                        "SELECT profile_image FROM users WHERE id = %s", (user["id"],)
                    )
                    if old_data and old_data.get("profile_image"):
                        old_file = os.path.join(upload_dir, os.path.basename(old_data["profile_image"]))
                        if os.path.exists(old_file):
                            try:
                                os.remove(old_file)
                            except OSError:
                                pass

                    profile_image = "assets/images/profiles/" + filename

        if not error_msg:
            try:
                if is_trainer():
                    if profile_image:
                        db().execute(
                            "UPDATE users SET name = %s, phone = %s, profile_image = %s, "
                            "education = %s, expertise = %s, bio = %s, updated_at = NOW() WHERE id = %s",
                            (name, phone, profile_image, education, expertise, bio, user["id"]),
                        )
                    else:
                        db().execute(
                            "UPDATE users SET name = %s, phone = %s, education = %s, "
                            "expertise = %s, bio = %s, updated_at = NOW() WHERE id = %s",
                            (name, phone, education, expertise, bio, user["id"]),
                        )
                else:
                    if profile_image:
                        db().execute(
                            "UPDATE users SET name = %s, phone = %s, profile_image = %s, "
                            "updated_at = NOW() WHERE id = %s",
                            (name, phone, profile_image, user["id"]),
                        )
                    else:
                        db().execute(
                            "UPDATE users SET name = %s, phone = %s, updated_at = NOW() WHERE id = %s",
                            (name, phone, user["id"]),
                        )

                session["user_name"] = name
                if profile_image:
                    session["user_profile_image"] = profile_image

                user_data = db().fetch_one("SELECT * FROM users WHERE id = %s", (user["id"],))
                set_flash("success", "Profile updated successfully!")
            except Exception:
                set_flash("danger", "Failed to update profile. Please try again.")
        else:
            set_flash("danger", error_msg)

    if request.method == "GET" and not user_data:
        user_data = {}

    return render_template(
        "student/profile.html",
        page_title="My Profile",
        user=user,
        user_data=user_data or {},
    )


@bp.route("/course-content/<int:course_id>")
@login_required
def course_content(course_id):
    user = current_user()

    if course_id <= 0:
        set_flash("danger", "Invalid course access.")
        return redirect("/dashboard")

    enrollment = db().fetch_one(
        "SELECT * FROM enrollments WHERE user_id = %s AND course_id = %s AND status = 'completed'",
        (user["id"], course_id),
    )
    if not enrollment:
        set_flash("warning", "You must be enrolled in this course to view its content.")
        return redirect("/dashboard")

    course = db().fetch_one(
        "SELECT c.*, u.name as trainer_name FROM courses c "
        "LEFT JOIN users u ON c.trainer_id = u.id WHERE c.id = %s",
        (course_id,),
    )
    if not course:
        set_flash("danger", "Course details not found.")
        return redirect("/dashboard")

    recordings = db().fetch_all(
        "SELECT * FROM course_recordings WHERE course_id = %s ORDER BY created_at DESC",
        (course_id,),
    )
    resources = db().fetch_all(
        "SELECT * FROM course_resources WHERE course_id = %s ORDER BY created_at DESC",
        (course_id,),
    )

    return render_template(
        "student/course_content.html",
        page_title=f"{course['name']} - Course Dashboard",
        course=course,
        recordings=recordings,
        resources=resources,
    )
