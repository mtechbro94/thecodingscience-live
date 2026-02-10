# Deploy to Hostinger - Complete Checklist

Your application is ready. Follow this checklist step-by-step.

---

## 📋 PRE-DEPLOYMENT (Do This First!)

- [ ] **Create Hostinger Account**
  - Go to https://hostinger.com
  - Choose VPS or Cloud Hosting plan
  - Complete setup

- [ ] **Get Domain Name**
  - Buy domain OR use existing domain
  - Point domain to Hostinger IP

- [ ] **Get SSH Access**
  - Check Hostinger email for SSH credentials
  - Username: usually `root`
  - Password: in your Hostinger account

- [ ] **Create Razorpay Test Credentials** (Optional - for testing)
  - Go to https://razorpay.com
  - Sign up for testing
  - Get Test API keys (not Live keys yet)
  - Test Key ID example: `rzp_test_xxxxxxxxxxxxx`

- [ ] **Get Gmail App Password** (for sending emails)
  - Go to https://myaccount.google.com/apppasswords
  - Generate app password
  - Use this in `.env` as `SENDER_PASSWORD`

---

## 🚀 DEPLOYMENT (Follow in Order)

### STEP 1: Read Guides (30 min)
- [ ] Read `HOSTINGER_DEPLOYMENT_QUICKSTART.md` (5 min)
- [ ] Read `HOSTINGER_DEPLOYMENT.md` completely (25 min)

### STEP 2: SSH and Initial Setup (15 min)
Commands from `HOSTINGER_DEPLOYMENT.md` STEP 1-2:

```bash
# SSH to server
ssh root@YOUR_HOSTINGER_IP

# Update system
apt update && apt upgrade -y

# Install packages
apt install -y python3 python3-pip python3-venv postgresql postgresql-contrib nginx git curl wget ssl-cert openssl ufw
```

- [ ] SSH into Hostinger
- [ ] Run apt update && upgrade
- [ ] Install required packages

### STEP 3: Firewall (5 min)
From `HOSTINGER_DEPLOYMENT.md` STEP 2:

```bash
ufw enable
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw status
```

- [ ] Enable firewall
- [ ] Allow SSH, HTTP, HTTPS

### STEP 4: PostgreSQL Setup (10 min)
From `HOSTINGER_DEPLOYMENT.md` STEP 3:

```bash
sudo systemctl start postgresql
sudo systemctl enable postgresql

sudo -u postgres psql

# In PostgreSQL shell:
CREATE DATABASE thecodingscience_db;
CREATE USER thecodingscience_user WITH PASSWORD 'your_secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE thecodingscience_db TO thecodingscience_user;
\q
```

- [ ] Start PostgreSQL
- [ ] Create database
- [ ] Create database user
- [ ] Grant permissions

### STEP 5: Clone Application (5 min)
From `HOSTINGER_DEPLOYMENT.md` STEP 4:

```bash
mkdir -p /var/www/thecodingscience
cd /var/www/thecodingscience
git clone https://github.com/mtechbro94/thecodingscience-live.git .
ls -la
```

- [ ] Create application directory
- [ ] Clone from GitHub
- [ ] Verify files exist

### STEP 6: Python Environment (10 min)
From `HOSTINGER_DEPLOYMENT.md` STEP 5:

```bash
cd /var/www/thecodingscience
python3 -m venv venv
source venv/bin/activate
pip install --upgrade pip setuptools wheel
pip install -r requirements.txt
pip list
deactivate
```

- [ ] Create virtual environment
- [ ] Install dependencies
- [ ] Verify all packages installed

### STEP 7: Environment Variables (5 min)
From `HOSTINGER_DEPLOYMENT.md` STEP 6:

```bash
nano /var/www/thecodingscience/.env
```

Fill in these values:

```
FLASK_ENV=production
FLASK_APP=app.py
SECRET_KEY=generate_a_random_string_here
DATABASE_URL=postgresql://thecodingscience_user:your_database_password@localhost/thecodingscience_db

SENDER_EMAIL=your-email@gmail.com
SENDER_PASSWORD=your_gmail_app_password

ADMIN_EMAIL=admin@thecodingscience.com
ADMIN_PASSWORD=change_this_later

RAZORPAY_KEY_ID=rzp_test_xxxxxxxxxxxxx
RAZORPAY_KEY_SECRET=your_key_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret

UPI_ID_1=yourbank@upi
UPI_ID_2=yourphone@upi
UPI_ID_3=yourname@upi
UPI_NAME=The Coding Science

ENABLE_RAZORPAY=true
ENABLE_UPI_MANUAL=true
WHATSAPP_GROUP_LINK=https://chat.whatsapp.com/your-link
```

- [ ] Create .env file
- [ ] Fill all environment variables
- [ ] Set correct permissions (chmod 600)

### STEP 8: Initialize Database (5 min)
From `HOSTINGER_DEPLOYMENT.md` STEP 7:

```bash
cd /var/www/thecodingscience
source venv/bin/activate
python app.py
# Wait for initialization, then press Ctrl+C
deactivate
```

- [ ] Run app initialization
- [ ] Verify database created
- [ ] Check database tables

### STEP 9: Set Up Gunicorn (10 min)
From `HOSTINGER_DEPLOYMENT.md` STEP 8:

```bash
sudo nano /etc/systemd/system/thecodingscience.service
# Copy the service file content from guide

sudo mkdir -p /var/log/thecodingscience
sudo chown -R www-data:www-data /var/www/thecodingscience
sudo systemctl daemon-reload
sudo systemctl enable thecodingscience
sudo systemctl start thecodingscience
sudo systemctl status thecodingscience
```

- [ ] Create Gunicorn service file
- [ ] Set permissions
- [ ] Start service
- [ ] Verify service is running

### STEP 10: Configure Nginx (10 min)
From `HOSTINGER_DEPLOYMENT.md` STEP 9:

```bash
sudo nano /etc/nginx/sites-available/thecodingscience
# Copy Nginx config from guide (replace your-domain.com)

sudo ln -s /etc/nginx/sites-available/thecodingscience /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

- [ ] Create Nginx config
- [ ] Replace your-domain.com
- [ ] Enable site
- [ ] Test and restart Nginx

### STEP 11: Set Up SSL (10 min)
From `HOSTINGER_DEPLOYMENT.md` STEP 10:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo mkdir -p /var/www/certbot

sudo certbot certonly --webroot \
  -w /var/www/certbot \
  -d your-domain.com \
  -d www.your-domain.com \
  --non-interactive \
  --agree-tos \
  --email your-email@gmail.com

# Update Nginx config with SSL (from guide)
sudo nano /etc/nginx/sites-available/thecodingscience
sudo nginx -t
sudo systemctl restart nginx
```

- [ ] Install Certbot
- [ ] Generate SSL certificate
- [ ] Update Nginx with SSL config
- [ ] Verify SSL working

### STEP 12: Test Deployment (10 min)

```bash
# Wait 2 minutes for DNS propagation, then:
curl https://your-domain.com
```

- [ ] Visit https://your-domain.com in browser
- [ ] Check homepage loads
- [ ] Test login page
- [ ] Test course enrollment
- [ ] Test payment gateway (use test card)

### STEP 13: Verify Logs (5 min)

```bash
# Check all errors
tail -f /var/log/thecodingscience/error.log
tail -f /var/log/thecodingscience/access.log
sudo systemctl status thecodingscience
```

- [ ] Check error logs
- [ ] Check access logs
- [ ] Verify no errors
- [ ] Keep monitoring

### STEP 14: Get Live Payment Keys (After Testing)

When ready to accept real payments:

1. Go to https://dashboard.razorpay.com
2. Complete KYC verification (takes 1-2 hours)
3. Get Live API keys (not test keys)
4. Update `.env`:

```bash
nano /var/www/thecodingscience/.env
# Replace test keys with LIVE keys
```

5. Restart:

```bash
sudo systemctl restart thecodingscience
```

- [ ] Complete Razorpay KYC
- [ ] Get live API keys
- [ ] Update .env
- [ ] Restart application

---

## ✅ Post-Deployment

### Daily
- [ ] Check error logs: `tail -f /var/log/thecodingscience/error.log`
- [ ] Verify application running: `sudo systemctl status thecodingscience`

### Weekly
- [ ] Check disk space: `df -h`
- [ ] Test payment gateway
- [ ] Review user registrations

### Monthly
- [ ] Update system: `sudo apt update && sudo apt upgrade -y`
- [ ] Check SSL expiry: `sudo certbot certificates`
- [ ] Review database size

---

## 🛠️ Common Issues & Fixes

### Port already in use
```bash
sudo lsof -i :80
sudo kill -9 PID
```

### App won't start
```bash
sudo systemctl status thecodingscience
tail -f /var/log/thecodingscience/error.log
```

### Database connection error
```bash
psql -U thecodingscience_user -d thecodingscience_db
```

### Static files not loading
```bash
sudo chown -R www-data:www-data /var/www/thecodingscience
sudo systemctl restart nginx
```

### SSL certificate issue
```bash
sudo certbot renew --force-renewal
```

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| `HOSTINGER_DEPLOYMENT.md` | Complete detailed guide with all commands |
| `HOSTINGER_DEPLOYMENT_QUICKSTART.md` | Quick reference checklist |
| `HOSTINGER_DEPLOYMENT_CHECKLIST.md` | This file - step-by-step checklist |

---

## 🎯 Success Criteria

After deployment, you should have:

✅ Application running at https://your-domain.com  
✅ HTTPS working with green padlock  
✅ Database initialized and connected  
✅ All pages loading correctly  
✅ Login/register working  
✅ Payment gateway ready  
✅ Logs showing no errors  
✅ Application auto-starts on reboot  

---

## ⏱️ Timeline

- Pre-deployment: 30 minutes (reading & preparation)
- Deployment: 90 minutes (following steps 1-11)
- Testing: 15 minutes
- Total: ~2.5 hours

**DNS propagation may take 2-24 hours. Website might show errors during this time.**

---

## 🚀 You're Ready!

Your application is production-ready and tested. Follow this checklist and you'll be live in a few hours!

**Start with STEP 1: Reading the guides**

Good luck! 💪

