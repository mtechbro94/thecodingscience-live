# Production Deployment Guide

## Overview
This guide provides step-by-step instructions for deploying The Coding Science Flask application to a production environment.

## Pre-Deployment Checklist

### Security
- [ ] Change `SECRET_KEY` to a strong random string
- [ ] Change default `ADMIN_PASSWORD` to a secure password
- [ ] Ensure `.env` file is in `.gitignore` and never committed
- [ ] Update all email credentials securely
- [ ] Enable HTTPS/SSL on your domain
- [ ] Configure CORS if needed
- [ ] Set `FLASK_ENV=production`
- [ ] Disable debug mode

### Environment Setup
- [ ] Update `.env` file with production values
- [ ] Verify all required environment variables are set
- [ ] Test database connection
- [ ] Set up email account with app-specific password (Gmail)
- [ ] Configure UPI payment IDs
- [ ] Set up proper logging directory

## Deployment Steps

### 1. Install Dependencies

```bash
pip install -r requirements.txt
```

### 2. Set Environment Variables

Create or update `.env` file:

```env
FLASK_ENV=production
FLASK_APP=app.py
SECRET_KEY=your-very-secure-random-string-here
DATABASE_URL=sqlite:///coding_science.db
SENDER_EMAIL=your-email@gmail.com
SENDER_PASSWORD=your-app-specific-password
ADMIN_EMAIL=admin@thecodingscience.com
ADMIN_PASSWORD=your-secure-admin-password
WHATSAPP_GROUP_LINK=https://your-whatsapp-link
CONTACT_PHONE=+91-xxxxxxxxxxxx
CONTACT_EMAIL=academy@thecodingscience.com
```

### 3. Initialize Database

```bash
python -c "from app import app, init_db; init_db()"
```

### 4. Create Admin User (Optional)

```bash
python -c "from app import app; app.cli.commands['create-admin'].callback()"
```

Or use the command:
```bash
flask create-admin
```

### 5. Deploy with Gunicorn

Install Gunicorn (already in requirements.txt):

```bash
pip install gunicorn
```

Run the application:

```bash
gunicorn -w 4 -b 0.0.0.0:5000 wsgi:app
```

**Options:**
- `-w 4`: Number of worker processes (adjust based on CPU cores)
- `-b 0.0.0.0:5000`: Bind to all interfaces on port 5000
- `--timeout 120`: Request timeout in seconds
- `--access-logfile -`: Log to stdout
- `--error-logfile -`: Error logs to stdout

### 6. Configure Reverse Proxy (Nginx Example)

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;

    location / {
        proxy_pass http://localhost:5000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 120s;
    }
}
```

### 7. Set Up SSL/TLS

Use Let's Encrypt for free SSL:

```bash
sudo apt-get install certbot nginx-certbot
sudo certbot certonly --nginx -d your-domain.com
```

### 8. Enable HTTPS in Flask

Update `config.py` to use `https://` for PREFERRED_URL_SCHEME in production.

### 9. Process Management (Systemd)

Create `/etc/systemd/system/thecodingscience.service`:

```ini
[Unit]
Description=The Coding Science Flask Application
After=network.target

[Service]
User=www-data
WorkingDirectory=/path/to/TheCodingScience
ExecStart=/path/to/venv/bin/gunicorn -w 4 -b 127.0.0.1:5000 wsgi:app
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
sudo systemctl daemon-reload
sudo systemctl enable thecodingscience
sudo systemctl start thecodingscience
```

### 10. Set Up Logging

Create logs directory:

```bash
mkdir -p logs
chmod 755 logs
```

Logs are configured in `config.py` and will be written to `logs/app.log`.

### 11. Database Backup

Set up automated backups:

```bash
# Daily backup script
0 2 * * * /path/to/backup_db.sh
```

## Email Configuration (Gmail)

1. Enable 2-factor authentication on your Gmail account
2. Generate an app password: https://myaccount.google.com/apppasswords
3. Add the app password to `.env`:
   ```
   SENDER_EMAIL=your-email@gmail.com
   SENDER_PASSWORD=your-16-character-app-password
   ```

## UPI Payment Configuration

Update `.env` with your UPI IDs:

```env
UPI_ID_1=your-upi@bank
UPI_ID_2=your-upi@bank
UPI_ID_3=your-upi@bank
UPI_NAME=The Coding Science
```

## Monitoring & Maintenance

### View Application Logs

```bash
# Systemd logs
sudo journalctl -u thecodingscience -f

# Application logs
tail -f logs/app.log
```

### Database Maintenance

```bash
# Vacuum database (optimize)
sqlite3 instance/coding_science.db "VACUUM;"

# Backup database
cp instance/coding_science.db instance/coding_science.db.backup
```

### Update Application

```bash
git pull origin main
pip install -r requirements.txt
sudo systemctl restart thecodingscience
```

## Performance Optimization

### 1. Database Indexing
- Ensure proper indexes on frequently queried columns ✓ (Done in models)

### 2. Caching
- Consider adding Redis for session caching
- Cache frequently accessed courses

### 3. Static Files
- Serve via CDN or Nginx (don't use Flask for static files)
- Minify CSS/JS

### 4. Database Connection Pool
- Use proper connection pooling for production databases (PostgreSQL)

## Security Headers

The application includes Flask-Talisman for security headers:
- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection enabled
- Strict-Transport-Security (HSTS)

## Troubleshooting

### Database Lock Error
```bash
sqlite3 instance/coding_science.db ".timeout 5000"
```

### Port Already in Use
```bash
lsof -i :5000
kill -9 <PID>
```

### Email Not Sending
1. Check `.env` credentials
2. Verify app password (not regular password)
3. Check firewall port 587
4. Review logs for SMTP errors

### High Memory Usage
- Reduce number of Gunicorn workers
- Check for database connection leaks
- Monitor log file size

## Backup & Recovery

### Automated Backup Script

Create `backup_db.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/path/to/backups"
DATE=$(date +%Y%m%d_%H%M%S)
cp /path/to/instance/coding_science.db $BACKUP_DIR/coding_science_$DATE.db
find $BACKUP_DIR -name "*.db" -mtime +30 -delete
```

## Support

For issues or questions:
- Email: academy@thecodingscience.com
- Phone: +917006196821
- WhatsApp: [Your WhatsApp Group]

---
Last Updated: 2026-01-24
Version: 1.0
