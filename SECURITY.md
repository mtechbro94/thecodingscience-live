# Security Best Practices for Production

## Overview
This document outlines critical security practices that must be followed when deploying The Coding Science application to production.

## Critical Security Points

### 1. Environment Variables (.env)
**NEVER commit `.env` to version control**

```bash
# ✓ CORRECT: Use .env.example as template
git commit .env.example

# ✗ WRONG: Don't commit actual .env
# .env is in .gitignore
```

**All sensitive data must be in environment variables:**
- `SECRET_KEY` - Use `python -c "import secrets; print(secrets.token_hex(32))"` to generate
- `SENDER_EMAIL` and `SENDER_PASSWORD` - Use app-specific passwords, not account passwords
- Admin credentials - Generate strong random passwords
- Database URLs - Use production database credentials
- API keys - If added in future

### 2. SECRET_KEY Generation
```python
# Generate a secure SECRET_KEY
import secrets
key = secrets.token_hex(32)
print(f"SECRET_KEY={key}")
```

Use this for your `.env` file. Never reuse the same key across environments.

### 3. Session Security
Currently configured with:
- `SESSION_COOKIE_SECURE=True` (HTTPS only)
- `SESSION_COOKIE_HTTPONLY=True` (JavaScript cannot access)
- `SESSION_COOKIE_SAMESITE='Lax'` (CSRF protection)
- `PERMANENT_SESSION_LIFETIME=7` days

**Ensure `PREFERRED_URL_SCHEME='https'` in production**

### 4. Password Security
**Current Requirements:**
- Minimum 8 characters (increased from 6)
- Hashed using Werkzeug's secure hash (PBKDF2 with SHA256)

**To Improve Further:**
```python
# Use bcrypt for even stronger hashing
pip install flask-bcrypt
```

### 5. HTTPS/SSL Configuration
**In Production: HTTPS is Mandatory**

```nginx
# Nginx configuration
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    ssl_certificate /etc/letsencrypt/live/your-domain/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain/privkey.pem;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

### 6. Input Validation & Sanitization
All user inputs are now validated:

- **Email**: Regex validation + email-validator library
- **Phone**: Regex pattern check (10-15 digits)
- **Name**: Length check (max 120 chars)
- **Messages**: Length check (max 5000 chars)
- **Password**: Length check (min 8 chars)

**Database queries use SQLAlchemy ORM** - prevents SQL injection

### 7. Error Handling
Production error messages don't expose:
- File paths
- Database details
- System information
- Stack traces (visible only in logs)

### 8. Logging & Monitoring
```python
# Log files are rotated
# Max 10MB per file, keeping 10 backup files
# Location: logs/app.log
```

**Review logs regularly for:**
- Failed login attempts
- Database errors
- SMTP failures
- Unusual API access patterns

### 9. Admin Access
**Default admin credentials MUST be changed:**
```bash
# After first deployment:
flask create-admin
# Or set in .env:
ADMIN_EMAIL=your-admin@email.com
ADMIN_PASSWORD=strong-random-password
```

### 10. Database Security
**Do NOT use SQLite in production.** Switch to PostgreSQL:

```bash
# Install PostgreSQL adapter
pip install psycopg2-binary

# Update DATABASE_URL in .env
DATABASE_URL=postgresql://user:password@localhost:5432/thecodingscience
```

**Database user should have minimal privileges:**
```sql
CREATE USER tcs_app WITH PASSWORD 'strong-password';
CREATE DATABASE thecodingscience OWNER tcs_app;
GRANT ALL PRIVILEGES ON DATABASE thecodingscience TO tcs_app;
```

### 11. Email Security
**Gmail Configuration:**
1. Enable 2-factor authentication
2. Generate app-specific password (not account password)
3. Store only app-specific password in `.env`

```
SENDER_EMAIL=your-email@gmail.com
SENDER_PASSWORD=xxxx xxxx xxxx xxxx  # 16-char app password
```

Never use account password directly.

### 12. Firewall & Network Security
```bash
# Allow only necessary ports
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP (redirect to HTTPS)
sudo ufw allow 443/tcp  # HTTPS
sudo ufw allow 5000     # Flask (if internal only)
sudo ufw enable
```

### 13. User Data Protection
**GDPR/Privacy Considerations:**
- Store minimum necessary personal data
- Hash passwords securely
- Implement data deletion on account removal ✓ (Done)
- Log access to sensitive data
- Regular security audits

### 14. CORS Configuration
If API needs to serve cross-origin requests:

```python
# Install flask-cors
pip install flask-cors

# In app.py
from flask_cors import CORS
CORS(app, origins=['https://yourdomain.com'])
```

### 15. Rate Limiting
Prevent brute force attacks via Nginx:

```nginx
# Nginx rate limiting
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;

location /login {
    limit_req zone=login burst=10 nodelay;
    proxy_pass http://localhost:5000;
}
```

### 16. Security Headers (Implemented)
App uses Flask-Talisman for:
- ✓ Strict-Transport-Security (HSTS)
- ✓ X-Content-Type-Options: nosniff
- ✓ X-Frame-Options: SAMEORIGIN
- ✓ X-XSS-Protection
- ✓ Content-Security-Policy (optional - can be enabled)

### 17. Dependency Security
**Keep dependencies updated:**
```bash
# Check for vulnerabilities
pip install safety
safety check

# Update dependencies
pip install --upgrade -r requirements.txt

# Use pip-audit for additional checks
pip install pip-audit
pip-audit
```

**Pin all versions in requirements.txt** ✓ (Done)

### 18. API Security
If exposing APIs:
- Use API keys or OAuth 2.0
- Implement rate limiting
- Log all API access
- Monitor suspicious patterns
- Use HTTPS only

### 19. Backup Security
- Store backups in secure location
- Encrypt database backups
- Test restore procedures
- Keep offsite backup copy
- Limit access to backup files

```bash
# Secure backup
chmod 600 /backups/database_backup.sql
```

### 20. Regular Security Audits
**Monthly:**
- Review access logs
- Check for failed login attempts
- Monitor database access patterns
- Verify SSL certificate validity

**Quarterly:**
- Security library updates
- Penetration testing
- Code review for security issues
- Backup restoration testing

**Annually:**
- Full security assessment
- Update security policies
- Review disaster recovery plan
- Security training for team

## Security Incident Response

### If Compromised:
1. Immediately change `SECRET_KEY`
2. Force re-login of all users
3. Review access logs
4. Check for unauthorized data access
5. Notify users if necessary
6. Audit all admin actions
7. Review and rotate credentials

### Contact on Security Issues:
- Email: academy@thecodingscience.com
- Emergency: +917006196821

## Compliance Considerations

### GDPR (if serving EU users)
- [ ] Explicit user consent for data collection
- [ ] Right to be forgotten implementation
- [ ] Data breach notification procedure
- [ ] Privacy policy on website
- [ ] Cookie consent mechanism

### CCPA (if serving California users)
- [ ] Privacy policy
- [ ] Do not sell data option
- [ ] Data access requests
- [ ] Data deletion requests

### PCI-DSS (if accepting payments)
- [ ] Don't store credit card data
- [ ] Use Razorpay/Stripe instead of manual processing
- [ ] HTTPS only
- [ ] Regular security testing
- [ ] Access control

## Recommended Tools

### Monitoring
- **Uptime Monitoring**: UptimeRobot, StatusPage
- **Error Tracking**: Sentry, Rollbar
- **Performance**: New Relic, DataDog, Prometheus
- **Log Aggregation**: ELK Stack, Papertrail, Sumo Logic

### Security
- **Vulnerability Scanning**: Snyk, Dependabot
- **SAST**: Bandit (Python security linter)
- **DAST**: OWASP ZAP, Burp Suite
- **SSL Testing**: SSL Labs

### Development
- **Code Quality**: SonarQube, CodeClimate
- **Type Checking**: MyPy, Pylint
- **Testing**: Pytest, Coverage.py

## Final Checklist Before Launch

- [ ] Change all default credentials
- [ ] Generate strong SECRET_KEY
- [ ] Configure .env with production values
- [ ] Set FLASK_ENV=production
- [ ] Enable HTTPS/SSL
- [ ] Configure database (PostgreSQL recommended)
- [ ] Set up backups
- [ ] Configure logging
- [ ] Test email delivery
- [ ] Test payment flows
- [ ] Perform security audit
- [ ] Load test application
- [ ] Document all credentials (encrypted)
- [ ] Brief team on security policies
- [ ] Set up monitoring and alerts
- [ ] Create incident response plan
- [ ] Review and sign off security requirements

---
**Document Version**: 1.0
**Last Updated**: 2026-01-24
**Review Frequency**: Quarterly
