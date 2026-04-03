# The Coding Science

Production-ready PHP website for The Coding Science, including public marketing pages, student enrollment flows, trainer tools, and an admin panel.

Live site: `https://thecodingscience.com`

## Stack

- PHP with a custom router in [index.php](/c:/Users/Mtechbro-94/Desktop/thecodingscience-live/index.php)
- MySQL/MariaDB
- Bootstrap-based frontend with custom CSS/JS
- Google OAuth for student authentication
- Email OTP for trainer authentication
- Razorpay payment integration
- GitHub Actions deployment to HostMyIdea via SSH

## Core Features

- Public pages for home, courses, blogs, internships, career tracks, contact, and search
- Student login with Google
- Trainer login with password plus OTP verification
- Admin dashboard for users, courses, blogs, messages, enrollments, settings, internships, coupons, and success stories
- Profile management for students and trainers
- Course enrollment and payment flow
- Blog publishing workflow for admin and trainers

## Authentication Model

- Students: Google sign-in only
- Trainers: existing trainer account required, then email OTP verification
- Admins: email/password login from `/login?role=admin`

Important routes:

- `/login`
- `/login?role=admin`
- `/student_login`
- `/trainer_login`
- `/admin/dashboard`

## Project Structure

```text
admin/               Admin panel pages
api/                 Authentication and AJAX endpoints
assets/              CSS, JS, images
config/              Feature-specific config
database/            Schema and migration SQL
includes/            Shared helpers, DB, mail, auth utilities
views/               Frontend pages and route targets
.github/workflows/   GitHub Actions deployment workflow
index.php            Main application router
config.php           Environment and app configuration
post_deploy.sh       Server-side post-deploy script
```

## Local Setup

### 1. Requirements

- PHP 8.x
- MySQL or MariaDB
- A web server or PHP built-in server
- Google OAuth credentials
- SMTP credentials
- Razorpay credentials for payment flows

### 2. Create `.env`

Use the same keys expected by [config.php](/c:/Users/Mtechbro-94/Desktop/thecodingscience-live/config.php):

```env
ENVIRONMENT=development
DB_HOST=localhost
DB_NAME=your_database
DB_USER=root
DB_PASS=
SITE_URL=http://localhost:8000
SITE_NAME="The Coding Science"
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

### 4. Run locally

Example using PHP built-in server:

```bash
php -S localhost:8000 index.php
```

Then open:

```text
http://localhost:8000
```

## Deployment

Production deployment is handled by GitHub Actions:

- Workflow: [.github/workflows/deploy.yml](/c:/Users/Mtechbro-94/Desktop/thecodingscience-live/.github/workflows/deploy.yml)
- Trigger: push to `main`
- Deployment method: tarball upload over SSH to HostMyIdea
- Post-deploy tasks: [post_deploy.sh](/c:/Users/Mtechbro-94/Desktop/thecodingscience-live/post_deploy.sh)

### Required GitHub Secrets

- `SSH_PRIVATE_KEY`
- `SSH_HOST`
- `SSH_USER`
- `DEPLOYMENT_PATH`
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `PROD_DB_PASS`
- `SITE_URL`
- `SITE_NAME`
- `RAZORPAY_KEY_ID`
- `RAZORPAY_KEY_SECRET`
- `SMTP_HOST`
- `SMTP_USER`
- `SMTP_PASS`
- `SMTP_PORT`
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`

## Production Notes

- Markdown files are intentionally excluded from deployment.
- `post_deploy.sh` handles permission setup and incremental database updates.
- Admin pages require an authenticated user with role `admin`.
- Trainer access is provisioned by the team; trainer self-registration is not part of the active production flow.

## Maintenance Checklist

- Verify Google OAuth credentials after domain or callback changes
- Verify SMTP delivery for OTP and password reset emails
- Confirm Razorpay keys in production before payment testing
- Review GitHub Actions logs after each deploy
- Check file permissions for profile uploads on the server

## License

Private project for The Coding Science.
