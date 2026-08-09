# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Quick Commands

```bash
# Run locally (Flask dev server)
python run.py          # serves http://localhost:8000

# Run tests (DB mocked)
python -m pytest -q

# Syntax-check Python
python -m compileall app config.py run.py wsgi.py tests

# MySQL CLI (local)
mysql -h localhost -u thecodin_user -p <db_name>
```

## Architecture Overview

**Pattern**: Flask application factory (`app/__init__.py`) with blueprint-based routing

```
run.py               → Local dev server (port 8000)
wsgi.py              → WSGI entrypoint (exposes `application`)
config.py            → Loads .env, defines Config class (DB, SMTP, OAuth, Razorpay)
app/
  __init__.py        → create_app(), security headers/CSP nonce, blueprints, jinja filters
  routes/            → Blueprints: public, auth, student, payments, trainer, admin*
  db.py              → PyMySQL wrapper (fetch_one/fetch_all/fetch_val/execute/insert)
  helpers.py         → Auth guards, flash, CSRF, coupons, markdown, image URLs, password hashing
  mail.py            → SMTP email + OTP sending
  social.py          → Google OAuth
  auth.py            → Route decorators (login/admin/trainer/student required)
  templates/         → Jinja2 templates (public, admin, student, trainer, auth, payments)
  career_settings.py → Static career-track settings
assets/              → CSS, JS, images (uploads land in assets/images/)
database/            → schema.sql (base), migrate_auth_refactor.sql, create_success_stories.sql
tests/               → Pytest smoke/payment tests (DB mocked via FakeDB)
```

### Routing

All requests go through Flask blueprints. Key routes:
- `/`, `/courses`, `/blogs`, `/blog/<slug>`, `/course/<id>`, `/internships`, `/career`, `/contact`, `/about`, `/search`
- `/login`, `/login?role=admin`, `/student-login`, `/trainer-login`, `/forgot-password`, `/reset-password`
- `/dashboard`, `/profile`, `/course-content/<id>`
- `/enroll`, `/enroll/<id>`, `/razorpay-payment/<id>`, `/verify-payment`, `/submit-payment/<id>`, `/payment-success`, `/payment-failed`
- `/admin/*` → admin blueprints registered with url_prefix `/admin`
- `/sitemap.xml`, `/robots.txt` are dynamic Flask routes

### Authentication System

| Role   | Login Method              | Session Field         |
|--------|---------------------------|-----------------------|
| Student| Google OAuth only         | `session['user_role'] = 'student'` |
| Trainer| Email + Password + OTP    | `session['user_role'] = 'trainer'` |
| Admin  | Email/password (from `/login?role=admin`) | `session['user_role'] = 'admin'` |

Key files:
- `app/routes/auth.py` - login, social OAuth, password reset, `/api/trainer_auth`, `/api/student_auth`
- `app/helpers.py` - Auth guards `is_logged_in()`, `is_admin()`, `is_trainer()`, `is_student()`, `current_user()`

### Database

Base schema in `database/schema.sql`. Auth refactor additions in `database/migrate_auth_refactor.sql`:
- `otp_tokens` table for trainer login OTPs
- `users` table columns: `gmail_id`, `otp_code`, `otp_expires_at`, `otp_verified`

Schema drift: the app tolerates missing tables/columns via `table_has_column()` in `app/helpers.py`
(e.g. local DB may lack `career_tracks`, `coupons`, `success_stories`, or `blogs.author_id`). Routes
guard these with `table_has_column` checks or try/except. If a table is missing locally, apply the
matching CREATE TABLE from `database/schema.sql` (idempotent `IF NOT EXISTS`).

### Password Hashing

bcrypt, compatible with PHP `password_hash`/`password_verify`. `verify_password()` in
`app/helpers.py` converts `$2y$` prefixes to `$2b$` so legacy PHP hashes verify correctly.

## Deployment

Currently **localhost only** — no production deploy config is in the repo (deploy files were
removed on request). When a real server is chosen later, deployment config must be recreated.

## Key Conventions

- **Flash messages**: Use `set_flash('success'|'danger'|'warning'|'info', $msg)` in routes, rendered in views via `get_flash()`
- **Redirects**: Always use Flask `redirect(url)` helper (returns 302)
- **CSRF**: Forms include `generate_csrf_token()`, validate with `validate_csrf_token($token)`
- **Rate limiting**: Use `rate_limit_check($key, $max_attempts, $window_seconds)` for auth endpoints
- **Images**: Course/blog/track images referenced by filename in DB; files live in `assets/images/` and are accessed via `get_image_url(path)`. Uploads from admin forms go to `assets/images/` (or `assets/images/profiles/`).
- **Markdown**: Blogs/content use `markdown_to_html()` for conversion
- **Date rendering**: Use the `pretty_date` Jinja filter (avoids Linux-only `strftime` flags that crash on Windows)
- **Money/decimals**: MySQL returns `Decimal`; coerce with `float()` before arithmetic to avoid `Decimal × float` TypeError

## Testing Checklist

Before pushing changes:
1. Python syntax: `python -m compileall -q app config.py run.py wsgi.py tests`
2. Tests: `python -m pytest -q` (currently 25 tests, DB mocked)
3. Auth flows work (student Google login, trainer OTP, admin password)
4. Admin routes require `is_admin()` guard
5. Trainer routes require `is_trainer()` guard
6. Payment flow (Razorpay) tested in sandbox mode

## Pending Task — Missing Images (resume next session)

The local app is feature-complete and tests pass, but many images are missing on disk so pages
show broken images. **Next session: copy the original image files into `assets/images/` and wire up
fallbacks.**

- **Missing course images**: `fsd.jpg`, `pp.jpg`, `ds.jpg`, `EHPT.jpg`, `ccc.jpg`, `maf.jpg`, `dabt.jpg`, `aad.jpg`, `moaat.jpg`
- **Missing blog images**: `blog-web.png`, `blog-python.png`, `blog-ai.png`
- **Missing about-team photos**: `jaffar-photo.JPG`, `Ubaid-photo.JPG` (hardcoded in `app/templates/public/about.html`)
- **Missing fallbacks**: `logo.png` / `logo.jpeg`, `placeholder.webp`, `default-avatar.png` (referenced in `app/helpers.py` `get_image_url()`)
- Only `assets/images/aaqib_Photo.JPG` and `assets/images/favicon.ico` exist today.
- User will provide the original image files (folder/download/old project) next session.
- After placing files, verify: home hero, header logo/favicon, `/courses`, `/blogs`, `/about`, admin settings previews all render.
