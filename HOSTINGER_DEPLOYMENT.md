# Hostinger Deployment Guide - The Coding Science

Complete step-by-step guide to deploy your Flask application on Hostinger.

---

## Prerequisites

Before starting, you need:
- ✅ Hostinger account (VPS or Cloud Hosting)
- ✅ Domain name pointing to Hostinger IP
- ✅ SSH credentials from Hostinger
- ✅ Razorpay API keys (test during deployment, live later)
- ✅ Application pushed to GitHub (already done ✓)

---

## STEP 1: Get SSH Access & Initial Setup

### 1.1 Connect to your Hostinger server via SSH

```bash
ssh root@YOUR_HOSTINGER_IP
# When prompted, enter your Hostinger password
```

### 1.2 Update system packages

```bash
apt update && apt upgrade -y
```

### 1.3 Install required system packages

```bash
apt install -y python3 python3-pip python3-venv postgresql postgresql-contrib nginx git curl wget ssl-cert openssl ufw
```

### 1.4 Verify Python installation

```bash
python3 --version
pip3 --version
```

---

## STEP 2: Configure Firewall (UFW)

```bash
# Enable firewall
ufw enable

# Allow SSH (important! do this first)
ufw allow 22/tcp

# Allow HTTP
ufw allow 80/tcp

# Allow HTTPS
ufw allow 443/tcp

# Verify rules
ufw status
```

---

## STEP 3: Set Up PostgreSQL Database

### 3.1 Start PostgreSQL

```bash
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### 3.2 Create database and user

```bash
sudo -u postgres psql

# Inside PostgreSQL shell, run these commands:
CREATE DATABASE thecodingscience_db;
CREATE USER thecodingscience_user WITH PASSWORD 'your_secure_password_here';
ALTER ROLE thecodingscience_user SET client_encoding TO 'utf8';
ALTER ROLE thecodingscience_user SET default_transaction_isolation TO 'read committed';
ALTER ROLE thecodingscience_user SET default_transaction_deferrable TO on;
ALTER ROLE thecodingscience_user SET default_transaction_level TO 'read committed';
GRANT ALL PRIVILEGES ON DATABASE thecodingscience_db TO thecodingscience_user;
\q

# Exit PostgreSQL shell with \q
```

### 3.3 Test database connection

```bash
psql -U thecodingscience_user -d thecodingscience_db -h localhost
# Type: \q to exit
```

---

## STEP 4: Set Up Application Directory

### 4.1 Create application directory

```bash
mkdir -p /var/www/thecodingscience
cd /var/www/thecodingscience
```

### 4.2 Clone your GitHub repository

```bash
git clone https://github.com/mtechbro94/thecodingscience-live.git .
# This clones into current directory (.)
```

### 4.3 Verify files

```bash
ls -la
# You should see: app.py, requirements.txt, config.py, templates/, static/, etc.
```

---

## STEP 5: Create Python Virtual Environment

### 5.1 Create venv

```bash
cd /var/www/thecodingscience
python3 -m venv venv
```

### 5.2 Activate venv

```bash
source venv/bin/activate
# Your prompt should now show (venv)
```

### 5.3 Install dependencies

```bash
pip install --upgrade pip setuptools wheel
pip install -r requirements.txt
```

### 5.4 Verify installation

```bash
pip list
# Should show: Flask, Flask-SQLAlchemy, Flask-Login, gunicorn, razorpay, etc.
```

---

## STEP 6: Configure Environment Variables

### 6.1 Create .env file

```bash
nano /var/www/thecodingscience/.env
```

### 6.2 Add environment variables

Copy and paste this, then modify with YOUR values:

```
# Flask Configuration
FLASK_ENV=production
FLASK_APP=app.py
SECRET_KEY=your_flask_secret_key_change_this_to_random_string

# Database
DATABASE_URL=postgresql://thecodingscience_user:your_database_password@localhost/thecodingscience_db

# Email Configuration
SENDER_EMAIL=your_email@gmail.com
SENDER_PASSWORD=your_gmail_app_password

# Admin Credentials (change after first login!)
ADMIN_EMAIL=admin@thecodingscience.com
ADMIN_PASSWORD=change_this_secure_password

# Razorpay (get from your Razorpay dashboard)
RAZORPAY_KEY_ID=rzp_test_xxxxxxxxxxxxx
RAZORPAY_KEY_SECRET=your_razorpay_key_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret

# UPI Configuration
UPI_ID_1=yourbank@upi
UPI_ID_2=yourphone@upi
UPI_ID_3=yourname@upi
UPI_NAME=The Coding Science

# Payment Gateway Toggles
ENABLE_RAZORPAY=true
ENABLE_UPI_MANUAL=true

# URLs
WHATSAPP_GROUP_LINK=https://chat.whatsapp.com/your-group-link
```

### 6.3 Save and exit

Press `Ctrl + X`, then `Y`, then `Enter`

### 6.4 Set correct permissions

```bash
chmod 600 /var/www/thecodingscience/.env
```

---

## STEP 7: Initialize Database

### 7.1 Deactivate venv first if active

```bash
deactivate
```

### 7.2 Switch to app user and activate venv

```bash
cd /var/www/thecodingscience
source venv/bin/activate
```

### 7.3 Run database initialization

```bash
python app.py
# Wait for: "Application context created" and "Database initialized"
# Press Ctrl+C to stop
```

### 7.4 Verify database

```bash
psql -U thecodingscience_user -d thecodingscience_db -h localhost
\dt
# Should show tables: user, course, enrollment, etc.
\q
```

---

## STEP 8: Configure Gunicorn

### 8.1 Create systemd service file

```bash
sudo nano /etc/systemd/system/thecodingscience.service
```

### 8.2 Add this content

```ini
[Unit]
Description=Gunicorn application server for The Coding Science
After=network.target

[Service]
Type=notify
User=www-data
WorkingDirectory=/var/www/thecodingscience
Environment="PATH=/var/www/thecodingscience/venv/bin"
ExecStart=/var/www/thecodingscience/venv/bin/gunicorn \
    --workers 3 \
    --worker-class sync \
    --bind unix:/var/www/thecodingscience/thecodingscience.sock \
    --timeout 30 \
    --access-logfile /var/log/thecodingscience/access.log \
    --error-logfile /var/log/thecodingscience/error.log \
    wsgi:app

[Install]
WantedBy=multi-user.target
```

### 8.3 Save and exit

Press `Ctrl + X`, then `Y`, then `Enter`

### 8.4 Create log directory

```bash
sudo mkdir -p /var/log/thecodingscience
sudo chown www-data:www-data /var/log/thecodingscience
```

### 8.5 Set directory permissions

```bash
sudo chown -R www-data:www-data /var/www/thecodingscience
sudo chmod -R 755 /var/www/thecodingscience
```

### 8.6 Enable and start service

```bash
sudo systemctl daemon-reload
sudo systemctl enable thecodingscience
sudo systemctl start thecodingscience
sudo systemctl status thecodingscience
# Should show: "active (running)"
```

### 8.7 Check if socket created

```bash
ls -la /var/www/thecodingscience/thecodingscience.sock
# Should exist
```

---

## STEP 9: Configure Nginx Reverse Proxy

### 9.1 Create Nginx config

```bash
sudo nano /etc/nginx/sites-available/thecodingscience
```

### 9.2 Add this configuration

Replace `your-domain.com` with your actual domain:

```nginx
upstream thecodingscience_app {
    server unix:/var/www/thecodingscience/thecodingscience.sock fail_timeout=0;
}

server {
    listen 80;
    server_name your-domain.com www.your-domain.com;

    client_max_body_size 20M;

    location / {
        proxy_pass http://thecodingscience_app;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_redirect off;
    }

    location /static/ {
        alias /var/www/thecodingscience/static/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
}
```

### 9.3 Save and exit

Press `Ctrl + X`, then `Y`, then `Enter`

### 9.4 Enable the site

```bash
sudo ln -s /etc/nginx/sites-available/thecodingscience /etc/nginx/sites-enabled/
```

### 9.5 Test Nginx config

```bash
sudo nginx -t
# Should show: "syntax is ok" and "test is successful"
```

### 9.6 Restart Nginx

```bash
sudo systemctl restart nginx
sudo systemctl status nginx
```

---

## STEP 10: Set Up SSL with Let's Encrypt

### 10.1 Install Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 10.2 Create certbot directory

```bash
sudo mkdir -p /var/www/certbot
```

### 10.3 Get SSL certificate

```bash
sudo certbot certonly --webroot \
  -w /var/www/certbot \
  -d your-domain.com \
  -d www.your-domain.com \
  --non-interactive \
  --agree-tos \
  --email your-email@gmail.com
```

### 10.4 Update Nginx config with SSL

```bash
sudo nano /etc/nginx/sites-available/thecodingscience
```

Replace the entire content with:

```nginx
upstream thecodingscience_app {
    server unix:/var/www/thecodingscience/thecodingscience.sock fail_timeout=0;
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
    
    location / {
        return 301 https://$server_name$request_uri;
    }
}

# HTTPS server
server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    # SSL certificates
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;

    client_max_body_size 20M;

    location / {
        proxy_pass http://thecodingscience_app;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_redirect off;
    }

    location /static/ {
        alias /var/www/thecodingscience/static/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### 10.5 Save and exit

Press `Ctrl + X`, then `Y`, then `Enter`

### 10.6 Test and restart Nginx

```bash
sudo nginx -t
sudo systemctl restart nginx
```

### 10.7 Set up auto-renewal

```bash
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
sudo systemctl status certbot.timer
```

---

## STEP 11: Deploy Updates from GitHub

When you make changes and push to GitHub:

```bash
cd /var/www/thecodingscience
git pull origin main
source venv/bin/activate
pip install -r requirements.txt
sudo systemctl restart thecodingscience
```

---

## STEP 12: Test Your Deployment

### 12.1 Open your domain in browser

```
https://your-domain.com
```

You should see your website!

### 12.2 Test main routes

- Home: `https://your-domain.com/`
- Courses: `https://your-domain.com/courses`
- Login: `https://your-domain.com/login`
- Admin: `https://your-domain.com/admin/panel`

### 12.3 Test payment gateway

1. Register a test user
2. Browse courses
3. Click "Enroll Now"
4. Test Razorpay payment (use test keys)
5. Test UPI QR code

---

## STEP 13: Monitor Your Application

### 13.1 Check application status

```bash
sudo systemctl status thecodingscience
```

### 13.2 View error logs

```bash
tail -f /var/log/thecodingscience/error.log
```

### 13.3 View access logs

```bash
tail -f /var/log/thecodingscience/access.log
```

### 13.4 Check Nginx status

```bash
sudo systemctl status nginx
```

### 13.5 Check database connection

```bash
psql -U thecodingscience_user -d thecodingscience_db -h localhost
\q
```

---

## STEP 14: Get Live Razorpay Keys (After Testing)

1. Go to https://dashboard.razorpay.com
2. Complete KYC verification
3. Navigate to Settings → API Keys
4. Copy Live Key (not Test Key)
5. Update `.env` file:

```bash
nano /var/www/thecodingscience/.env
# Replace RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET with LIVE keys
```

6. Restart application:

```bash
sudo systemctl restart thecodingscience
```

---

## Troubleshooting

### Issue: Port 80/443 already in use

```bash
sudo lsof -i :80
sudo lsof -i :443
# Kill the process using sudo kill -9 <PID>
```

### Issue: Gunicorn fails to start

```bash
sudo systemctl status thecodingscience
tail -f /var/log/thecodingscience/error.log
# Check logs for specific error
```

### Issue: Database connection error

```bash
# Verify PostgreSQL is running
sudo systemctl status postgresql

# Check if database exists
psql -U thecodingscience_user -d thecodingscience_db -h localhost

# Verify .env DATABASE_URL is correct
grep DATABASE_URL /var/www/thecodingscience/.env
```

### Issue: Static files not loading

```bash
# Check permissions
sudo chown -R www-data:www-data /var/www/thecodingscience
sudo chmod -R 755 /var/www/thecodingscience

# Test Nginx config
sudo nginx -t
sudo systemctl restart nginx
```

### Issue: SSL certificate error

```bash
# Check certificate
sudo certbot certificates

# Renew manually
sudo certbot renew --force-renewal

# Check Let's Encrypt logs
sudo tail -f /var/log/letsencrypt/letsencrypt.log
```

---

## Security Checklist

- [ ] Change admin password after first login
- [ ] Update email credentials (Gmail App Password)
- [ ] Set strong database password
- [ ] Update `SECRET_KEY` in `.env`
- [ ] Disable test mode in Razorpay
- [ ] Use live Razorpay keys
- [ ] Set `FLASK_ENV=production`
- [ ] Enable HTTPS (should be automatic)
- [ ] Configure firewall properly
- [ ] Regular backups of database

---

## Regular Maintenance

### Daily
- Monitor error logs
- Check application status

### Weekly
- Check disk space: `df -h`
- Review database size: `du -sh /var/lib/postgresql`

### Monthly
- Update system: `sudo apt update && sudo apt upgrade -y`
- Check SSL certificate expiry: `sudo certbot certificates`

### Quarterly
- Database backup
- Application backup
- Security audit

---

## Contact & Support

For more help:
- Check logs: `/var/log/thecodingscience/`
- Nginx errors: `sudo nginx -t`
- Database issues: Contact Hostinger support
- Payment issues: Razorpay documentation

---

## Quick Command Reference

```bash
# Start/stop/restart application
sudo systemctl start thecodingscience
sudo systemctl stop thecodingscience
sudo systemctl restart thecodingscience

# Check status
sudo systemctl status thecodingscience

# View logs
tail -f /var/log/thecodingscience/error.log
tail -f /var/log/thecodingscience/access.log

# Update from GitHub
cd /var/www/thecodingscience && git pull origin main

# Restart after update
sudo systemctl restart thecodingscience

# Check certificate
sudo certbot certificates
```

---

## Deployment Complete! 🚀

After following all steps:
1. Your website is live at `https://your-domain.com`
2. Payment gateway is ready
3. Database is configured
4. SSL is enabled
5. Application auto-starts on reboot

**Congratulations on your production deployment!**

