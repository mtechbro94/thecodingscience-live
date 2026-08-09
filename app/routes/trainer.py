"""Trainer routes: dashboard, class management, actions, and blogs."""

import os
import re
import uuid
from datetime import datetime

from flask import Blueprint, redirect, render_template, request, session

from config import Config
from app.auth import trainer_required
from app.db import db
from app.helpers import (
    current_user, generate_slug, sanitize, set_flash, table_has_column,
    validate_csrf_token,
)

bp = Blueprint("trainer", __name__)


def _fmt_date(value, fmt="%d %b %Y"):
    """Format a MySQL datetime like PHP date('d M Y', strtotime(...))."""
    if not value:
        return ""
    if isinstance(value, datetime):
        return value.strftime(fmt)
    for pattern in ("%Y-%m-%d %H:%M:%S", "%Y-%m-%d %H:%M:%S.%f", "%Y-%m-%d"):
        try:
            return datetime.strptime(str(value), pattern).strftime(fmt)
        except ValueError:
            continue
    return str(value)


def _strip_tags(value):
    return re.sub(r"<[^>]+>", "", value or "")


def _course_id_from_request():
    raw = request.args.get("course_id") or request.args.get("id") or ""
    try:
        return int(raw)
    except (TypeError, ValueError):
        return 0


def _require_own_course(course_id):
    """Verify the course exists and belongs to the current trainer."""
    course = db().fetch_one("SELECT * FROM courses WHERE id = %s", (course_id,))
    if not course:
        set_flash("danger", "Course not found.")
        return None
    if int(course.get("trainer_id") or 0) != int(current_user()["id"]):
        set_flash("danger", "You are not authorized to manage this course.")
        return None
    return course


# ---------------------------------------------------------------------------
# Dashboard
# ---------------------------------------------------------------------------

@bp.route("/trainer-dashboard")
@bp.route("/trainer_dashboard")
@trainer_required
def trainer_dashboard():
    user = current_user()
    user_id = user["id"]

    try:
        course_count = int(
            db().fetch_val(
                "SELECT COUNT(*) FROM courses WHERE trainer_id = %s", (user_id,)
            )
            or 0
        )
    except Exception:
        course_count = 0

    try:
        student_count = int(
            db().fetch_val(
                "SELECT COUNT(e.id) FROM enrollments e JOIN courses c ON e.course_id = c.id "
                "WHERE c.trainer_id = %s AND e.status = 'completed'",
                (user_id,),
            )
            or 0
        )
    except Exception:
        student_count = 0

    try:
        trainer_courses = db().fetch_all(
            "SELECT * FROM courses WHERE trainer_id = %s ORDER BY created_at DESC",
            (user_id,),
        )
    except Exception:
        trainer_courses = []

    return render_template(
        "trainer/trainer_dashboard.html",
        page_title="Trainer Dashboard",
        user=user,
        is_approved=session.get("is_approved", 1),
        course_count=course_count,
        student_count=student_count,
        trainer_courses=trainer_courses,
    )


# ---------------------------------------------------------------------------
# Manage class
# ---------------------------------------------------------------------------

@bp.route("/trainer-manage-course")
@bp.route("/trainer-manage-course/<int:course_id>")
@trainer_required
def trainer_course_manage(course_id=None):
    if course_id is None:
        course_id = _course_id_from_request()

    if course_id <= 0:
        set_flash("danger", "Invalid course ID.")
        return redirect("/trainer-dashboard")

    course = _require_own_course(course_id)
    if not course:
        return redirect("/trainer-dashboard")

    try:
        recordings = db().fetch_all(
            "SELECT * FROM course_recordings WHERE course_id = %s ORDER BY created_at DESC",
            (course_id,),
        )
    except Exception:
        recordings = []
    try:
        resources = db().fetch_all(
            "SELECT * FROM course_resources WHERE course_id = %s ORDER BY created_at DESC",
            (course_id,),
        )
    except Exception:
        resources = []

    for rec in recordings:
        rec["created_date"] = _fmt_date(rec.get("created_at"), "%d %b %Y")
    for res in resources:
        res["created_date"] = _fmt_date(res.get("created_at"), "%d %b %Y")

    return render_template(
        "trainer/trainer_course_manage.html",
        page_title="Manage Class: {}".format(course["name"]),
        course=course,
        course_id=course_id,
        recordings=recordings,
        resources=resources,
    )


# ---------------------------------------------------------------------------
# Actions (POST-only)
# ---------------------------------------------------------------------------

@bp.route("/trainer-actions", methods=["POST"])
@trainer_required
def trainer_actions():
    if not validate_csrf_token(request.form.get("csrf_token", "")):
        set_flash("danger", "Invalid request.")
        return redirect("/trainer-dashboard")

    action = request.form.get("action", "")
    try:
        course_id = int(request.form.get("course_id") or 0)
    except (TypeError, ValueError):
        course_id = 0

    if course_id <= 0:
        set_flash("danger", "Invalid request.")
        return redirect("/trainer-dashboard")

    course = db().fetch_one("SELECT trainer_id FROM courses WHERE id = %s", (course_id,))
    if not course or int(course.get("trainer_id") or 0) != int(current_user()["id"]):
        set_flash("danger", "Unauthorized action.")
        return redirect("/trainer-dashboard")

    if action == "update_live_settings":
        live_link = sanitize(request.form.get("live_link", ""))
        batch_timing = sanitize(request.form.get("batch_timing", ""))
        db().execute(
            "UPDATE courses SET live_link = %s, batch_timing = %s WHERE id = %s",
            (live_link, batch_timing, course_id),
        )
        set_flash("success", "Live session settings updated.")

    elif action == "add_recording":
        title = sanitize(request.form.get("title", ""))
        url = request.form.get("recording_url", "")
        if title and url:
            db().insert(
                "INSERT INTO course_recordings (course_id, title, recording_url) VALUES (%s, %s, %s)",
                (course_id, title, url),
            )
            set_flash("success", "Recording added successfully.")
        else:
            set_flash("danger", "Please provide both title and URL.")

    elif action == "delete_recording":
        try:
            rec_id = int(request.form.get("id") or 0)
        except (TypeError, ValueError):
            rec_id = 0
        db().execute(
            "DELETE FROM course_recordings WHERE id = %s AND course_id = %s",
            (rec_id, course_id),
        )
        set_flash("success", "Recording deleted.")

    elif action == "add_resource":
        title = sanitize(request.form.get("title", ""))
        file = request.files.get("resource_file")
        if file and file.filename:
            ext = os.path.splitext(file.filename)[1].lstrip(".").lower()
            allowed = ["pdf", "doc", "docx", "zip", "rar", "ppt", "pptx", "txt"]
            if ext in allowed:
                filename = "res_{}.{}".format(uuid.uuid4().hex, ext)
                upload_dir = os.path.join(Config.BASE_PATH, "assets", "uploads", "resources")
                os.makedirs(upload_dir, exist_ok=True)
                try:
                    file.save(os.path.join(upload_dir, filename))
                except Exception:
                    set_flash("danger", "Failed to save file.")
                else:
                    db().insert(
                        "INSERT INTO course_resources (course_id, title, file_path, file_type) "
                        "VALUES (%s, %s, %s, %s)",
                        (course_id, title, filename, ext),
                    )
                    set_flash("success", "Resource uploaded successfully.")
            else:
                set_flash("danger", "Invalid file type.")
        # No file: PHP silently does nothing

    elif action == "delete_resource":
        try:
            res_id = int(request.form.get("id") or 0)
        except (TypeError, ValueError):
            res_id = 0
        res = db().fetch_one(
            "SELECT file_path FROM course_resources WHERE id = %s AND course_id = %s",
            (res_id, course_id),
        )
        if res:
            full_path = os.path.join(
                Config.BASE_PATH, "assets", "uploads", "resources", res["file_path"]
            )
            if os.path.exists(full_path):
                try:
                    os.unlink(full_path)
                except OSError:
                    pass
            db().execute("DELETE FROM course_resources WHERE id = %s", (res_id,))
            set_flash("success", "Resource deleted.")

    return redirect("/trainer-manage-course/{}".format(course_id))


# ---------------------------------------------------------------------------
# Blogs
# ---------------------------------------------------------------------------

@bp.route("/trainer-blogs", methods=["GET", "POST"])
@trainer_required
def trainer_blogs():
    user = current_user()
    has_author_id = table_has_column("blogs", "author_id")

    if request.method == "POST":
        if request.form.get("action") == "delete":
            if not validate_csrf_token(request.form.get("csrf_token", "")):
                set_flash("danger", "Invalid request.")
                return redirect("/trainer-blogs")
            try:
                blog_id = int(request.form.get("id") or 0)
            except (TypeError, ValueError):
                blog_id = 0
            if has_author_id:
                rowcount = db().execute(
                    "DELETE FROM blogs WHERE id = %s AND author_id = %s",
                    (blog_id, user["id"]),
                )
            else:
                rowcount = db().execute("DELETE FROM blogs WHERE id = %s", (blog_id,))
            if rowcount:
                set_flash("success", "Blog post deleted successfully.")
            else:
                set_flash("danger", "You do not have permission to delete this post.")
            return redirect("/trainer-blogs")

    blogs = []
    try:
        if has_author_id:
            blogs = db().fetch_all(
                "SELECT * FROM blogs WHERE author_id = %s ORDER BY created_at DESC",
                (user["id"],),
            )
        else:
            blogs = db().fetch_all(
                "SELECT * FROM blogs WHERE author = %s ORDER BY created_at DESC",
                (user["name"],),
            )
    except Exception:
        set_flash("danger", "Unable to load your blogs right now.")

    for blog in blogs:
        blog["created_date"] = _fmt_date(blog.get("created_at"), "%b %d, %Y")
        excerpt = blog.get("excerpt") or ""
        if not excerpt:
            excerpt = _strip_tags(blog.get("content"))[:100] + "..."
        blog["display_excerpt"] = excerpt

    return render_template(
        "trainer/trainer_blogs.html",
        page_title="My Blogs",
        blogs=blogs,
    )


@bp.route("/trainer-blog", methods=["GET", "POST"])
@bp.route("/trainer-blog/new", methods=["GET", "POST"])
@bp.route("/trainer-blog/<int:blog_id>", methods=["GET", "POST"])
@trainer_required
def trainer_blog(blog_id=None):
    user = current_user()
    errors = []
    has_author_id = table_has_column("blogs", "author_id")

    if blog_id is None:
        raw_id = request.args.get("id", "")
        try:
            blog_id = int(raw_id) if raw_id else None
        except (TypeError, ValueError):
            blog_id = None

    is_edit = bool(blog_id)

    blog = None
    if is_edit:
        if has_author_id:
            blog = db().fetch_one(
                "SELECT * FROM blogs WHERE id = %s AND author_id = %s",
                (blog_id, user["id"]),
            )
        else:
            blog = db().fetch_one("SELECT * FROM blogs WHERE id = %s", (blog_id,))
        if not blog:
            set_flash("danger", "Blog post not found or you do not have permission to edit it.")
            return redirect("/trainer-blogs")

    if request.method == "POST":
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            errors.append("Invalid request. Please refresh and try again.")

        title = sanitize(request.form.get("title", ""))
        slug = sanitize(request.form.get("slug") or generate_slug(title))
        excerpt = sanitize(request.form.get("excerpt", ""))
        content = request.form.get("content", "")
        is_published = 1 if request.form.get("is_published") else 0
        today = datetime.now().strftime("%Y-%m-%d")

        if not title:
            errors.append("Title is required")
        if not content:
            errors.append("Content is required")

        image = blog.get("image") if blog else None

        image_file = request.files.get("image")
        if image_file and image_file.filename:
            image_file.stream.seek(0, os.SEEK_END)
            size = image_file.stream.tell()
            image_file.stream.seek(0)

            if size > 2 * 1024 * 1024:
                errors.append("Image is too large. Maximum size is 2MB.")
            else:
                allowed = ["jpg", "jpeg", "png", "webp"]
                ext = os.path.splitext(image_file.filename)[1].lstrip(".").lower()
                if ext in allowed:
                    new_filename = "blog_{}.{}".format(uuid.uuid4().hex, ext)
                    upload_dir = os.path.join(Config.BASE_PATH, "assets", "images")
                    os.makedirs(upload_dir, exist_ok=True)
                    try:
                        image_file.save(os.path.join(upload_dir, new_filename))
                        image = new_filename
                    except Exception:
                        errors.append("Failed to upload image. Check directory permissions.")
                else:
                    errors.append("Invalid file type. Allowed: jpg, jpeg, png, webp")

        if not errors:
            try:
                if is_edit:
                    if has_author_id:
                        db().execute(
                            "UPDATE blogs SET title = %s, slug = %s, excerpt = %s, content = %s, "
                            "image = %s, is_published = %s WHERE id = %s AND author_id = %s",
                            (title, slug, excerpt, content, image, is_published, blog_id, user["id"]),
                        )
                    else:
                        db().execute(
                            "UPDATE blogs SET title = %s, slug = %s, excerpt = %s, content = %s, "
                            "image = %s, is_published = %s WHERE id = %s",
                            (title, slug, excerpt, content, image, is_published, blog_id),
                        )
                    set_flash("success", "Blog post updated successfully.")
                else:
                    if has_author_id:
                        db().insert(
                            "INSERT INTO blogs (title, slug, excerpt, content, image, author, author_id, "
                            "date, is_published, created_at) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())",
                            (title, slug, excerpt, content, image, user["name"], user["id"], today, is_published),
                        )
                    else:
                        db().insert(
                            "INSERT INTO blogs (title, slug, excerpt, content, image, author, "
                            "date, is_published, created_at) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, NOW())",
                            (title, slug, excerpt, content, image, user["name"], today, is_published),
                        )
                    set_flash("success", "Blog post created successfully.")
                return redirect("/trainer-blogs")
            except Exception:
                errors.append("Failed to save the blog post. Please try again.")

        blog = {
            "id": blog_id or 0,
            "title": title,
            "slug": slug,
            "excerpt": excerpt,
            "content": content,
            "image": image,
            "is_published": is_published,
        }

    if blog is None:
        blog = {}

    is_edit = bool(blog.get("id"))

    return render_template(
        "trainer/trainer_blog_form.html",
        page_title="Edit Blog" if is_edit else "Create Blog Post",
        blog=blog,
        is_edit=is_edit,
        errors=errors,
        saved=request.args.get("saved"),
        user=user,
    )
