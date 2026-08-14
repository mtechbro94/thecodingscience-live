"""Smoke tests: app boots, routes register, key pages render with a mocked DB."""

import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))


def test_app_creates(app):
    assert app is not None
    assert app.test_client() is not None


def test_route_count(app):
    rules = [str(r) for r in app.url_map.iter_rules()]
    assert "/" in rules
    assert "/courses" in rules
    assert "/login" in rules
    assert "/admin/dashboard" in rules


def test_security_headers(app):
    client = app.test_client()
    rv = client.get("/courses")
    assert rv.status_code == 200
    headers = rv.headers
    assert headers.get("Content-Security-Policy") is not None
    assert headers.get("X-Content-Type-Options") == "nosniff"
    assert headers.get("X-Frame-Options") is not None


def test_csp_and_placeholder_fallback(app):
    client = app.test_client()
    rv = client.get("/")
    assert rv.status_code == 200
    csp = rv.headers.get("Content-Security-Policy", "")
    for allowed_source in [
        "https://unpkg.com",
        "https://code.jquery.com",
        "https://www.google-analytics.com",
        "https://www.google.com",
    ]:
        assert allowed_source in csp

    from app.helpers import get_image_url
    assert get_image_url("missing-course.jpg").endswith("/assets/images/placeholder.webp")
    assert get_image_url("", "profile").endswith("/assets/images/default-avatar.png")


def test_sitemap_renders(app, fakedb):
    def all_router(sql, params=None):
        if "FROM blogs" in sql:
            return [{"slug": "ml-guide", "updated_at": None}]
        if "FROM courses" in sql:
            return [{"id": 3, "updated_at": None}]
        return []

    fakedb._all_router = all_router
    client = app.test_client()
    rv = client.get("/sitemap.xml")
    assert rv.status_code == 200
    assert rv.mimetype == "application/xml"
    body = rv.get_data(as_text=True)
    assert "<urlset" in body
    assert "/courses" in body
    assert "/course/3" in body
    assert "/blog/ml-guide" in body


def test_robots_renders(app):
    client = app.test_client()
    rv = client.get("/robots.txt")
    assert rv.status_code == 200
    assert "User-agent" in rv.get_data(as_text=True)
    assert "/sitemap.xml" in rv.get_data(as_text=True)


def test_404_handler(app):
    client = app.test_client()
    rv = client.get("/does-not-exist-xyz")
    assert rv.status_code == 404


def test_student_profile_renders_with_date(client, fakedb):
    from datetime import datetime

    fakedb._one = {
        "id": 1, "name": "Test Student", "email": "s@example.com", "role": "student",
        "phone": None, "education": None, "expertise": None, "bio": None,
        "is_active": 1, "is_approved": 1, "profile_image": None,
        "created_at": datetime(2026, 1, 5),
    }
    with client.session_transaction() as sess:
        sess["user_id"] = 1
        sess["user_role"] = "student"
        sess["user_name"] = "Test Student"
        sess["user_email"] = "s@example.com"
    rv = client.get("/profile")
    assert rv.status_code == 200
    body = rv.get_data(as_text=True)
    assert "My Profile" in body
    assert "Member since" in body


def test_only_approved_courses_are_visible(app, fakedb):
    allowed = [
        "Python For Data Science",
        "Machine Learning with Python",
        "Generative AI For GenZ",
        "Agentic AI Mastery",
        "Full Stack Development With Python",
    ]
    all_courses = [
        {"id": 1, "name": "Python For Data Science", "summary": "", "description": "", "image": "", "is_featured": 1, "created_at": None, "price": 0, "level": "Beginner"},
        {"id": 2, "name": "Machine Learning with Python", "summary": "", "description": "", "image": "", "is_featured": 1, "created_at": None, "price": 0, "level": "Intermediate"},
        {"id": 3, "name": "Generative AI For GenZ", "summary": "", "description": "", "image": "", "is_featured": 1, "created_at": None, "price": 0, "level": "Advanced"},
        {"id": 4, "name": "Agentic AI Mastery", "summary": "", "description": "", "image": "", "is_featured": 1, "created_at": None, "price": 0, "level": "Advanced"},
        {"id": 5, "name": "Full Stack Development With Python", "summary": "", "description": "", "image": "", "is_featured": 1, "created_at": None, "price": 0, "level": "Advanced"},
        {"id": 6, "name": "Removed Old Course", "summary": "", "description": "", "image": "", "is_featured": 1, "created_at": None, "price": 0, "level": "Beginner"},
    ]

    def all_router(sql, params=None):
        if "FROM courses" in sql and "COUNT" not in sql:
            return all_courses
        if "FROM coupons" in sql:
            return []
        if "FROM career_tracks" in sql:
            return []
        return []

    fakedb._all_router = all_router

    client = app.test_client()
    rv = client.get("/courses")

    assert rv.status_code == 200
    body = rv.get_data(as_text=True)
    for name in allowed:
        assert name in body
    assert "Removed Old Course" not in body
