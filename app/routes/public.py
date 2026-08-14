from flask import Blueprint, render_template

import json
from datetime import datetime

from config import Config
from app.db import db
from app.helpers import (
    approved_course_fallback,
    filter_visible_courses,
    get_image_url,
    get_internships_by_category,
    get_setting,
    search_content,
)

bp = Blueprint("public", __name__)


def _get_setting_int(key, default=0):
    try:
        return int(get_setting(key, default))
    except (TypeError, ValueError):
        return default


@bp.route("/")
def home():
    try:
        courses = db().fetch_all(
            "SELECT * FROM courses WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 4"
        )
    except Exception:
        courses = []

    if not courses:
        try:
            courses = db().fetch_all("SELECT * FROM courses ORDER BY created_at DESC LIMIT 4")
        except Exception:
            courses = []

    courses = filter_visible_courses(courses)[:4]
    if not courses:
        courses = approved_course_fallback()[:4]

    try:
        if table_has_column("blogs", "author_id"):
            blogs = db().fetch_all(
                "SELECT b.*, u.name AS author_name, u.profile_image AS author_image "
                "FROM blogs b LEFT JOIN users u ON b.author_id = u.id "
                "WHERE b.is_published = 1 ORDER BY b.created_at DESC LIMIT 3"
            )
        else:
            blogs = db().fetch_all(
                "SELECT b.*, b.author AS author_name, NULL AS author_image "
                "FROM blogs b WHERE b.is_published = 1 ORDER BY b.created_at DESC LIMIT 3"
            )
    except Exception:
        blogs = []

    try:
        total_courses = int(db().fetch_val("SELECT COUNT(*) FROM courses") or 0)
    except Exception:
        total_courses = 0
    try:
        total_students = int(
            db().fetch_val(
                "SELECT COUNT(*) FROM users WHERE role = 'student' AND is_active = 1"
            )
            or 0
        )
    except Exception:
        total_students = 0
    try:
        total_certifications = int(
            db().fetch_val("SELECT COUNT(*) FROM enrollments WHERE status = 'completed'") or 0
        )
    except Exception:
        total_certifications = 0

    satisfaction_rate = 95

    try:
        dynamic_stories = db().fetch_all(
            "SELECT * FROM success_stories WHERE is_active = 1 "
            "ORDER BY sort_order ASC, created_at DESC"
        )
    except Exception:
        dynamic_stories = []

    return render_template(
        "public/home.html",
        page_title="Home",
        courses=courses,
        blogs=blogs,
        total_courses=total_courses,
        total_students=total_students,
        total_certifications=total_certifications,
        satisfaction_rate=satisfaction_rate,
        dynamic_stories=dynamic_stories,
        hero_bg=get_setting("hero_background", ""),
    )


@bp.route("/about")
def about():
    return render_template("public/about.html", page_title="About Us")


@bp.route("/contact", methods=["GET", "POST"])
def contact():
    from flask import request

    from app.helpers import rate_limit_check, set_flash, sanitize
    from app.mail import send_email
    from config import Config

    if request.method == "POST":
        if not rate_limit_check("contact_form", 5, 600):
            set_flash("danger", "Too many contact form submissions. Please try again later.")
            return render_template("public/contact.html", page_title="Contact Us")

        name = sanitize(request.form.get("name", ""))
        email = sanitize(request.form.get("email", ""))
        subject = sanitize(request.form.get("subject", ""))
        message = sanitize(request.form.get("message", ""))
        company = sanitize(request.form.get("company", ""))

        if not company:
            try:
                db().insert(
                    "INSERT INTO contact_messages (name, email, subject, message) "
                    "VALUES (%s, %s, %s, %s)",
                    (name, email, subject, message),
                )
                email_subject = f"New Contact Form Message: {subject or 'No Subject'}"
                email_message = (
                    f"<h3>New Contact Message</h3>"
                    f"<p><strong>Name:</strong> {name}</p>"
                    f"<p><strong>Email:</strong> {email}</p>"
                    f"<p><strong>Subject:</strong> {subject}</p>"
                    f"<p><strong>Message:</strong> {message}</p>"
                )
                send_email(Config.CONTACT_EMAIL, email_subject, email_message, True)
                set_flash("success", "Your message has been sent. We will get back to you soon!")
            except Exception:
                set_flash("danger", "Something went wrong. Please try again.")
        return render_template("public/contact.html", page_title="Contact Us")

    return render_template("public/contact.html", page_title="Contact Us")


@bp.route("/search")
def search():
    from flask import request

    from app.helpers import sanitize

    query = sanitize(request.args.get("q", ""))
    results = search_content(query) if query else []
    return render_template("public/search.html", page_title="Search Results", query=query, results=results)


@bp.route("/courses")
def courses():
    from flask import request

    query = request.args.get("q", "").strip()
    level = request.args.get("level", "").strip()

    sql = "SELECT * FROM courses WHERE 1=1"
    params = []
    if query:
        sql += " AND (name LIKE %s OR summary LIKE %s OR description LIKE %s)"
        like = f"%{query}%"
        params.extend([like, like, like])
    if level:
        sql += " AND level = %s"
        params.append(level)

    try:
        courses = db().fetch_all(sql + " ORDER BY created_at DESC", params)
    except Exception:
        courses = []

    courses = filter_visible_courses(courses)
    if not courses:
        courses = approved_course_fallback()

    try:
        coupons = db().fetch_all(
            "SELECT * FROM coupons WHERE is_active = 1 "
            "AND (valid_from IS NULL OR valid_from <= NOW()) "
            "AND (valid_until IS NULL OR valid_until >= NOW()) "
            "ORDER BY discount_value DESC"
        )
    except Exception:
        coupons = []

    career_tracks = []
    try:
        career_tracks = db().fetch_all("SELECT * FROM career_tracks WHERE is_active = 1 ORDER BY sort_order ASC")
    except Exception:
        pass

    levels = []
    if level:
        levels = [level]

    return render_template(
        "public/courses.html",
        page_title="Courses",
        courses=courses,
        coupons=coupons,
        career_tracks=career_tracks,
        active_query=query,
        active_level=level,
        levels=levels,
    )


@bp.route("/course/<int:course_id>")
def course_detail(course_id):
    from flask import abort

    from app.helpers import current_user, is_logged_in

    course = db().fetch_one("SELECT * FROM courses WHERE id = %s", (course_id,))
    if not course:
        abort(404)

    is_enrolled = False
    if is_logged_in():
        is_enrolled = bool(
            db().fetch_val(
                "SELECT COUNT(*) FROM enrollments WHERE user_id = %s AND course_id = %s",
                (current_user()["id"], course_id),
            )
        )

    reviews = db().fetch_all(
        "SELECT * FROM course_reviews WHERE course_id = %s AND is_approved = 1 "
        "ORDER BY created_at DESC LIMIT 5",
        (course_id,),
    )

    rating_row = db().fetch_one(
        "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews "
        "FROM course_reviews WHERE course_id = %s AND is_approved = 1",
        (course_id,),
    )
    avg_rating = float(rating_row["avg_rating"] or 0) if rating_row else 0
    total_reviews = int(rating_row["total_reviews"] or 0) if rating_row else 0

    return render_template(
        "public/course_detail.html",
        page_title=course["name"],
        course=course,
        is_enrolled=is_enrolled,
        reviews=reviews,
        avg_rating=avg_rating,
        total_reviews=total_reviews,
        og_title=course["name"],
        og_description=course.get("summary"),
        og_image=get_image_url(course.get("image")) if course.get("image") else None,
    )


@bp.route("/blogs")
def blogs():
    from flask import request

    from app.helpers import table_has_column

    category = request.args.get("category", "").strip()
    blogs_have_category = table_has_column("blogs", "category")
    if table_has_column("blogs", "author_id"):
        author_select = "b.*, u.name AS author_name FROM blogs b LEFT JOIN users u ON b.author_id = u.id"
    else:
        author_select = "b.*, b.author AS author_name FROM blogs b"
    sql = f"SELECT {author_select} WHERE b.is_published = 1"
    params = []
    if category and blogs_have_category:
        sql += " AND b.category = %s"
        params.append(category)

    all_blogs = db().fetch_all(sql + " ORDER BY b.created_at DESC", params)

    if blogs_have_category:
        categories = db().fetch_all("SELECT DISTINCT category FROM blogs WHERE is_published = 1")
    else:
        categories = []

    return render_template(
        "public/blogs.html",
        page_title="Blogs",
        blogs=all_blogs,
        categories=categories,
        active_category=category,
    )


@bp.route("/blog/<slug>")
def blog_detail(slug):
    from flask import abort

    from app.helpers import markdown_to_html, table_has_column

    if table_has_column("blogs", "author_id"):
        author_select = "b.*, u.name AS author_name, u.profile_image AS author_image " \
            "FROM blogs b LEFT JOIN users u ON b.author_id = u.id"
    else:
        author_select = "b.*, b.author AS author_name, NULL AS author_image FROM blogs b"

    blog = db().fetch_one(
        f"SELECT {author_select} "
        "WHERE b.slug = %s AND b.is_published = 1",
        (slug,),
    )
    if not blog:
        abort(404)

    return render_template(
        "public/blog_detail.html",
        page_title=blog["title"],
        blog=blog,
        blog_content=markdown_to_html(blog.get("content") or ""),
    )


@bp.route("/internships")
def internships():
    internships = get_internships_by_category()
    return render_template(
        "public/internships.html",
        page_title="Internships",
        internships=internships,
    )


@bp.route("/career")
def career():
    from app.career_settings import CAREER_SETTINGS

    return render_template(
        "public/career.html",
        page_title="Career",
        career=CAREER_SETTINGS,
        internships=get_internships_by_category(),
    )


@bp.route("/career-track/<slug>")
def career_track(slug):
    from flask import redirect

    from app.career_settings import CAREER_SETTINGS

    try:
        track = db().fetch_one(
            "SELECT * FROM career_tracks WHERE slug = %s AND is_active = 1", (slug,)
        )
    except Exception:
        return redirect("/career")
    if not track:
        return redirect("/courses")

    try:
        track_courses = db().fetch_all(
            "SELECT c.*, ct.sort_order FROM career_track_courses ct "
            "JOIN courses c ON c.id = ct.course_id "
            "WHERE ct.track_id = %s ORDER BY ct.sort_order ASC",
            (track["id"],),
        )
    except Exception:
        track_courses = []

    curriculum = track.get("curriculum")
    curriculum = json.loads(curriculum) if curriculum else []

    outcomes = [line for line in (track.get("outcomes") or "").strip().split("\n") if line]
    requirements = [line for line in (track.get("requirements") or "").strip().split("\n") if line]

    return render_template(
        "public/career_track.html",
        page_title=track["name"],
        track=track,
        track_courses=track_courses,
        curriculum=curriculum,
        outcomes=outcomes,
        requirements=requirements,
        career=CAREER_SETTINGS,
    )


@bp.route("/register")
def register():
    from flask import redirect
    return redirect("/login")


@bp.route("/sitemap.xml")
def sitemap():
    from flask import Response

    from app.helpers import table_has_column

    base = Config.site_url()
    entries = []

    static_pages = [
        ("/", "1.0", "weekly"),
        ("/about", "0.8", "monthly"),
        ("/courses", "0.9", "weekly"),
        ("/blogs", "0.8", "daily"),
        ("/contact", "0.7", "monthly"),
    ]
    for path, priority, freq in static_pages:
        entries.append({"loc": base + path, "priority": priority, "changefreq": freq})

    today = datetime.now().strftime("%Y-%m-%d")

    blogs_have_updated = table_has_column("blogs", "updated_at")
    blogs_update_col = "slug, updated_at" if blogs_have_updated else "slug, created_at AS updated_at"
    blogs = db().fetch_all(
        f"SELECT {blogs_update_col} FROM blogs WHERE is_published = 1 ORDER BY id DESC"
    ) if table_has_column("blogs", "is_published") else db().fetch_all(
        f"SELECT {blogs_update_col} FROM blogs ORDER BY id DESC"
    )
    for blog in blogs:
        lastmod = blog["updated_at"].strftime("%Y-%m-%d") if blog.get("updated_at") else today
        entries.append({
            "loc": base + "/blog/" + blog["slug"],
            "changefreq": "weekly",
            "lastmod": lastmod,
            "priority": "0.7",
        })

    courses_have_published = table_has_column("courses", "is_published")
    courses_have_updated = table_has_column("courses", "updated_at")
    courses_update_col = "id, updated_at" if courses_have_updated else "id, created_at AS updated_at"
    if courses_have_published:
        courses = db().fetch_all(
            f"SELECT {courses_update_col} FROM courses WHERE is_published = 1 ORDER BY id DESC"
        )
    else:
        courses = db().fetch_all(f"SELECT {courses_update_col} FROM courses ORDER BY id DESC")
    for course in courses:
        lastmod = course["updated_at"].strftime("%Y-%m-%d") if course.get("updated_at") else today
        entries.append({
            "loc": base + "/course/" + str(course["id"]),
            "changefreq": "weekly",
            "lastmod": lastmod,
            "priority": "0.8",
        })

    lines = ['<?xml version="1.0" encoding="UTF-8"?>']
    lines.append('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">')
    for entry in entries:
        lines.append("  <url>")
        lines.append(f"    <loc>{entry['loc']}</loc>")
        if entry.get("changefreq"):
            lines.append(f"    <changefreq>{entry['changefreq']}</changefreq>")
        if entry.get("lastmod"):
            lines.append(f"    <lastmod>{entry['lastmod']}</lastmod>")
        lines.append(f"    <priority>{entry['priority']}</priority>")
        lines.append("  </url>")
    lines.append("</urlset>")

    return Response("\n".join(lines), mimetype="application/xml")


@bp.route("/robots.txt")
def robots():
    from flask import Response

    base = Config.site_url()
    content = (
        "User-agent: *\n"
        "Disallow: /admin\n"
        "Disallow: /api/\n"
        "Disallow: /login\n"
        "Disallow: /student-login\n"
        "Disallow: /trainer-login\n"
        "Disallow: /profile\n"
        "Disallow: /dashboard\n"
        f"Sitemap: {base}/sitemap.xml\n"
    )
    return Response(content, mimetype="text/plain")
