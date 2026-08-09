# The Coding Science

Production-ready Flask website for The Coding Science, including public marketing pages, student enrollment flows, trainer tools, and an admin panel.

## Stack

- Flask (Python 3.11) with blueprint-based routing
- MySQL/MariaDB (PyMySQL, PDO-style query helpers)
- Bootstrap-based frontend with custom CSS/JS
- Google OAuth for student authentication
- Email OTP for trainer authentication
- Razorpay payment integration

## Core Features

- Public pages for home, courses, blogs, internships, career tracks, contact, and search
- Student login with Google
- Trainer login with password plus OTP verification
- Admin dashboard for users, courses, blogs, messages, enrollments, settings, internships, coupons, and success stories
- Profile management for students and trainers
- Course enrollment and payment flow (Razorpay + UPI fallback)
- Blog publishing workflow for admin and trainers

## Authentication Model

- Students: Google sign-in only
- Trainers: existing trainer account required, then email OTP verification
- Admins: email/password login from `/login?role=admin`

Important routes:

- `/login`
- `/login?role=admin`
- `/student_login` and `/student-login`
- `/trainer_login` and `/trainer-login`
- `/admin/dashboard`

## Project Structure

```text
app/                 Flask application package
  routes/            Blueprints (public, auth, student, payments, trainer, admin)
  templates/         Jinja2 templates
  db.py              Database wrapper (PyMySQL)
  helpers.py         Auth guards, flash, CSRF, coupons, markdown, uploads
  mail.py            SMTP email + OTP sending
  social.py          Google OAuth
  auth.py            Route decorators (login/admin/trainer/student required)
config.py            Environment and app configuration
wsgi.py              WSGI entrypoint
run.py               Local development server (port 8000)
assets/              CSS, JS, images
database/            Schema and migration SQL
tests/               Pytest smoke tests (DB mocked)
```

## Local Setup

### 1. Requirements

- Python 3.11+
- MySQL or MariaDB
- Google OAuth credentials
- SMTP credentials
- Razorpay credentials for payment flows

### 2. Create `.env`

Use the same keys expected by [config.py](/c:/Users/Mtechbro-94/Desktop/thecodingscience-live/config.py):

```env
ENVIRONMENT=development
DB_HOST=localhost
DB_NAME=your_database
DB_USER=root
DB_PASS=
SITE_URL=http://localhost:8000
SITE_NAME="The Coding Science"
SECRET_KEY=your-random-secret
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
SMTP_HOST=smtp.gmail.com
SMTP_USER=
SMTP_PASS=
SMTP_PORT=465
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

### 3. Import the database

For a clean setup, start with:

- [database/schema.sql](/c:/Users/Mtechbro-94/Desktop/thecodingscience-live/database/schema.sql)

For auth-related incremental changes used in production, review:

- [database/migrate_auth_refactor.sql](/c:/Users/Mtechbro-94/Desktop/thecodingscience-live/database/migrate_auth_refactor.sql)

### 4. Install and run locally

```bash
pip install -r requirements.txt
python run.py          # Flask dev server on http://localhost:8000
```

Or via `start-local.bat` on Windows.

Then open:

```text
http://localhost:8000
```

### 5. Run tests

```bash
python -m pytest -q
```

Tests mock the database layer, so they run without a MySQL server.

## Production Notes

- Admin pages require an authenticated user with role `admin`.
- Trainer access is provisioned by the team; trainer self-registration is not part of the active production flow.

## Maintenance Checklist

- Verify Google OAuth credentials after domain or callback changes
- Verify SMTP delivery for OTP and password reset emails
- Confirm Razorpay keys before payment testing
- Check file permissions for profile uploads on the server

## License

Private project for The Coding Science.
