# Hostinger Deployment Quick Start

Quick reference for deployment. For detailed steps, see `HOSTINGER_DEPLOYMENT.md`

---

## What You Have

✅ Application code in GitHub  
✅ Requirements.txt with all dependencies  
✅ Database models prepared  
✅ Payment gateway configured  
✅ Nginx config documentation  
✅ SSL setup instructions  

---

## Pre-Deployment Checklist

- [ ] Hostinger VPS/Cloud account created
- [ ] Domain pointed to Hostinger IP
- [ ] SSH credentials obtained
- [ ] Razorpay account created
- [ ] Gmail App Password generated
- [ ] GitHub repository ready (already done ✓)

---

## Deployment Steps (14 Total)

### Phase 1: Server Setup (15 min)
1. SSH into server
2. Update system
3. Install packages

### Phase 2: Database (10 min)
4. Install PostgreSQL
5. Create database & user

### Phase 3: Application (15 min)
6. Clone from GitHub
7. Create Python venv
8. Install dependencies

### Phase 4: Configuration (5 min)
9. Create .env file with secrets

### Phase 5: Web Server (20 min)
10. Set up Gunicorn service
11. Configure Nginx
12. Set up SSL with Let's Encrypt

### Phase 6: Testing & Monitoring (10 min)
13. Test your website
14. Monitor logs

**Total Time: ~1.5 hours**

---

## Commands Reference

```bash
# SSH to server
ssh root@YOUR_HOSTINGER_IP

# Clone application
git clone https://github.com/mtechbro94/thecodingscience-live.git /var/www/thecodingscience

# Create venv
python3 -m venv /var/www/thecodingscience/venv

# Install dependencies
source /var/www/thecodingscience/venv/bin/activate
pip install -r requirements.txt

# Create .env file
nano /var/www/thecodingscience/.env

# Create PostgreSQL database
sudo -u postgres psql
CREATE DATABASE thecodingscience_db;
CREATE USER thecodingscience_user WITH PASSWORD 'password';
GRANT ALL PRIVILEGES ON DATABASE thecodingscience_db TO thecodingscience_user;

# Start application
sudo systemctl start thecodingscience

# Check status
sudo systemctl status thecodingscience

# View logs
tail -f /var/log/thecodingscience/error.log
```

---

## Environment Variables Needed

```
FLASK_ENV=production
SECRET_KEY=random_string_here
DATABASE_URL=postgresql://user:password@localhost/db
SENDER_EMAIL=your_email@gmail.com
SENDER_PASSWORD=gmail_app_password
RAZORPAY_KEY_ID=test_key_id
RAZORPAY_KEY_SECRET=test_key_secret
UPI_ID_1=upi_id_here
UPI_NAME=The Coding Science
```

See `HOSTINGER_DEPLOYMENT.md` Step 6 for complete list.

---

## After Deployment

### Test Everything
1. Visit https://your-domain.com
2. Check all pages load
3. Test login/register
4. Test course enrollment
5. Test payment gateway

### Get Live Keys
1. Complete Razorpay KYC
2. Get live API keys
3. Update .env
4. Restart application

### Monitor
- Check error logs daily
- Monitor disk space
- Check SSL expiry (auto-renews)
- Keep application updated

---

## Troubleshooting

**App won't start?**
```bash
sudo systemctl status thecodingscience
tail -f /var/log/thecodingscience/error.log
```

**Database connection error?**
```bash
psql -U thecodingscience_user -d thecodingscience_db
```

**Static files not loading?**
```bash
sudo chown -R www-data:www-data /var/www/thecodingscience
sudo systemctl restart nginx
```

**SSL certificate issue?**
```bash
sudo certbot renew --force-renewal
sudo systemctl restart nginx
```

For more help, see `HOSTINGER_DEPLOYMENT.md` troubleshooting section.

---

## Files You Created

| File | Purpose |
|------|---------|
| `HOSTINGER_DEPLOYMENT.md` | Complete 14-step guide |
| `HOSTINGER_DEPLOYMENT_QUICKSTART.md` | This quick reference |

---

## Next Steps

1. **Read**: `HOSTINGER_DEPLOYMENT.md` (complete guide)
2. **Execute**: Follow steps 1-14 in order
3. **Test**: Visit your domain and test features
4. **Monitor**: Check logs and status regularly
5. **Upgrade**: Update Razorpay to live keys

---

## Support Resources

- Hostinger Help: https://www.hostinger.com/help
- Flask Docs: https://flask.palletsprojects.com
- Gunicorn Docs: https://gunicorn.org
- Nginx Docs: https://nginx.org
- PostgreSQL Docs: https://www.postgresql.org/docs
- Razorpay Docs: https://razorpay.com/docs

---

**Your application is ready to deploy. Good luck! 🚀**

