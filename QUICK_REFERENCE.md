# Quick Reference Guide

## Quick Start - Development

```bash
# 1. Activate virtual environment
.\.venv\Scripts\activate

# 2. Install dependencies
pip install -r requirements.txt

# 3. Update .env with your settings
# Copy from .env.example if needed

# 4. Run application
python app.py

# 5. Open browser
# http://localhost:5000
```

## Quick Start - Production

```bash
# 1. Update .env with production values
nano .env

# 2. Initialize database
python -c "from app import app, init_db; init_db()"

# 3. Create admin user
flask create-admin

# 4. Run with Gunicorn
gunicorn -w 4 -b 0.0.0.0:5000 wsgi:app

# OR use systemd (see DEPLOYMENT.md)
sudo systemctl start thecodingscience
```

## Environment Variables Checklist

### Required for Production
- [ ] `FLASK_ENV=production`
- [ ] `SECRET_KEY=` (generated random string)
- [ ] `SENDER_EMAIL=` (Gmail address)
- [ ] `SENDER_PASSWORD=` (app-specific password)
- [ ] `ADMIN_EMAIL=` (admin email)
- [ ] `ADMIN_PASSWORD=` (secure password)
- [ ] `DATABASE_URL=` (PostgreSQL recommended)

### Optional
- [ ] `WHATSAPP_GROUP_LINK=`
- [ ] `CONTACT_PHONE=`
- [ ] `CONTACT_EMAIL=`
- [ ] `UPI_ID_1=`, `UPI_ID_2=`, `UPI_ID_3=`

## File Structure

```
TheCodingScience/
├── app.py ........................ Main application
├── config.py ..................... Configuration management
├── wsgi.py ....................... Production entry point
├── requirements.txt .............. Dependencies
├── requirements-dev.txt .......... Dev dependencies
├── .env.example .................. Environment template
│
├── templates/ .................... HTML templates
│   ├── base.html ................. Base template
│   ├── index.html ................ Homepage
│   ├── courses.html .............. Course listing
│   ├── dashboard.html ............ Student dashboard
│   └── admin_*.html .............. Admin templates
│
├── static/ ....................... Static files
│   ├── css/style.css ............. Custom styles
│   ├── js/main.js ................ JavaScript
│   └── images/ ................... Course images
│
├── instance/ ..................... Instance folder (gitignored)
│   └── coding_science.db ......... Database
│
├── logs/ ......................... Application logs (gitignored)
│
├── Documentation:
├── README.md ..................... Project overview
├── DEPLOYMENT.md ................. Deployment guide
├── SECURITY.md ................... Security guidelines
├── DEVELOPMENT.md ................ Developer guide
├── PRODUCTION_CHECKLIST.md ....... Pre-launch checklist
└── PRODUCTION_READY.md ........... This summary
```

## Common Commands

### Flask Commands
```bash
# Create admin user
flask create-admin

# Database shell
flask shell

# Run in development
python app.py

# Run with Gunicorn (production)
gunicorn wsgi:app
```

### Database
```bash
# Reset database (development only)
rm instance/coding_science.db
python app.py

# Backup database
cp instance/coding_science.db backup_$(date +%Y%m%d).db
```

### Dependencies
```bash
# Install all
pip install -r requirements.txt

# Install dev tools
pip install -r requirements-dev.txt

# Freeze current
pip freeze > requirements.txt

# Check security
pip install safety
safety check
```

### Code Quality
```bash
# Format code
black app.py config.py wsgi.py

# Sort imports
isort app.py config.py wsgi.py

# Check quality
flake8 app.py
pylint app.py

# Type check
mypy app.py

# Run tests
pytest
```

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| `ModuleNotFoundError` | Activate venv: `.\.venv\Scripts\activate` |
| Port 5000 in use | Change port: `FLASK_PORT=5001` |
| Database locked | Delete `instance/coding_science.db` and restart |
| Email not sending | Check `.env` - verify SMTP credentials |
| Login fails | Ensure user account is active |
| CSRF token missing | Use proper forms (see templates) |
| 404 on static files | Check Nginx configuration for static files |
| High memory usage | Reduce Gunicorn workers: `-w 2` |

## Security Checklist

- [ ] Changed default `SECRET_KEY`
- [ ] Changed default admin password
- [ ] Removed hardcoded credentials
- [ ] Set `FLASK_ENV=production`
- [ ] Disabled debug mode
- [ ] Configured HTTPS/SSL
- [ ] Updated `.env` (not committed)
- [ ] Tested backup restoration
- [ ] Configured firewall
- [ ] Set up monitoring
- [ ] Created incident response plan

## Performance Tips

### Development
- Use Flask dev server (already built-in)
- Enable query logging: `SQLALCHEMY_ECHO=True`
- Use browser dev tools for frontend debugging

### Production
- Use Gunicorn with 2-4 workers per CPU
- Switch to PostgreSQL (not SQLite)
- Enable Redis for sessions
- Use CDN for static files
- Enable gzip compression
- Monitor with tools like DataDog

## Email Configuration (Gmail)

1. Enable 2-factor authentication on Gmail
2. Generate app password: https://myaccount.google.com/apppasswords
3. Add to .env:
   ```
   SENDER_EMAIL=your-email@gmail.com
   SENDER_PASSWORD=xxxx xxxx xxxx xxxx
   ```
4. Test with contact form or admin email send

## Database Migration (SQLite → PostgreSQL)

```bash
# 1. Install PostgreSQL driver
pip install psycopg2-binary

# 2. Create PostgreSQL database
createdb thecodingscience

# 3. Create database user
psql -c "CREATE USER tcs_app WITH PASSWORD 'password';"
psql -c "GRANT ALL ON thecodingscience TO tcs_app;"

# 4. Update .env
DATABASE_URL=postgresql://tcs_app:password@localhost:5432/thecodingscience

# 5. Initialize
python -c "from app import app, init_db; init_db()"
```

## Deployment Command

```bash
# Full deployment command
gunicorn \
  --workers 4 \
  --worker-class sync \
  --bind 0.0.0.0:5000 \
  --timeout 120 \
  --access-logfile - \
  --error-logfile - \
  wsgi:app
```

## Monitoring & Logging

```bash
# Tail logs (real-time)
tail -f logs/app.log

# View recent errors
tail -20 logs/app.log | grep ERROR

# Count errors
grep ERROR logs/app.log | wc -l

# Search logs
grep "email" logs/app.log
```

## Backup Procedures

```bash
# Backup database
sqlite3 instance/coding_science.db ".backup backup_$(date +%Y%m%d_%H%M%S).db"

# Restore from backup
sqlite3 instance/coding_science.db ".restore backup_20260124_120000.db"

# PostgreSQL backup
pg_dump thecodingscience > backup_20260124.sql

# PostgreSQL restore
psql thecodingscience < backup_20260124.sql
```

## Testing

```bash
# Run all tests
pytest

# Run with verbose output
pytest -v

# Run specific test
pytest tests/test_auth.py::test_login

# Generate coverage report
pytest --cov --cov-report=html

# Open coverage report
open htmlcov/index.html
```

## Useful URLs

- **Local Dev**: http://localhost:5000
- **Admin Panel**: /admin/panel
- **Student Dashboard**: /dashboard
- **Courses**: /courses
- **API Docs**: Check DEPLOYMENT.md for API endpoints

## Support Resources

| Need | Resource |
|------|----------|
| Setup help | DEVELOPMENT.md |
| Deployment | DEPLOYMENT.md |
| Security | SECURITY.md |
| Pre-launch | PRODUCTION_CHECKLIST.md |
| Full overview | PRODUCTION_READY.md |

## Key Contacts

- **Email**: academy@thecodingscience.com
- **Phone**: +717006196821
- **WhatsApp**: [Your WhatsApp Group]

---
**Version**: 1.0
**Last Updated**: 2026-01-24
**Status**: ✅ Production Ready
