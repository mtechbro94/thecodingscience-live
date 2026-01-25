# 🚀 Deployment & CI/CD Guide - The Coding Science

## Overview
This guide explains how to deploy your Flask application with automated CI/CD using GitHub Actions.

---

## 📋 Pre-Deployment Checklist

- [ ] All tests pass locally
- [ ] Environment variables are configured
- [ ] Database is initialized
- [ ] `.env.example` is up to date
- [ ] Code is committed and pushed to GitHub
- [ ] GitHub repository is connected
- [ ] Deployment platform (Railway/Render) is configured
- [ ] Domain is configured (optional)

---

## 🔧 Local Testing

### 1. Install Dependencies
```bash
pip install -r requirements.txt
pip install pytest pytest-cov
```

### 2. Run Tests
```bash
pytest tests/ -v
```

### 3. Check Code Quality
```bash
pip install flake8
flake8 app.py
```

### 4. Run the App Locally
```bash
export FLASK_ENV=development
python app.py
# Visit http://localhost:5000
```

---

## 🐙 GitHub Setup

### 1. Initialize Git Repository
```bash
git init
git add .
git commit -m "Initial commit: The Coding Science Flask app"
git branch -M main
git remote add origin https://github.com/your-username/your-repo.git
git push -u origin main
```

### 2. Create GitHub Actions Secrets
Go to **Settings → Secrets and variables → Actions** and add:

- **RAILWAY_TOKEN**: Your Railway API token
- **DATABASE_URL**: (optional if using Railway database)
- **SECRET_KEY**: Your Flask secret key

### How to Get Railway Token:
1. Visit https://railway.app
2. Sign in to your account
3. Go to Account Settings → API Tokens
4. Generate a new token
5. Copy and add to GitHub Secrets

---

## 🚀 Deployment with Railway

### Option 1: Deploy via GitHub Actions (Recommended)

**The CI/CD pipeline automatically deploys when you push to `main` branch:**

1. Commit your changes
2. Push to GitHub:
   ```bash
   git add .
   git commit -m "Your message"
   git push origin main
   ```
3. GitHub Actions automatically:
   - ✅ Runs tests
   - ✅ Checks security
   - ✅ Checks code quality
   - ✅ Deploys to Railway (if all checks pass)

### Option 2: Manual Deployment

If GitHub Actions fails or you want to deploy manually:

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Link project
railway link

# Deploy
railway up
```

---

## 📦 Environment Variables Setup

### Create `.env` file in project root:
```bash
cp .env.example .env
```

### Edit `.env` with your values:
```
FLASK_ENV=production
FLASK_APP=app.py
SECRET_KEY=your-super-secret-key-here

# Database
DATABASE_URL=sqlite:///coding_science.db

# Site Info
SITE_NAME=The Coding Science
SITE_TAGLINE=Learn Technology, Transform Your Future

# Email
SENDER_EMAIL=your-email@gmail.com
SENDER_PASSWORD=your-app-password

# Social Media
INSTAGRAM_URL=https://instagram.com/thecodingscience
YOUTUBE_URL=https://youtube.com/@thecodingscience
FACEBOOK_URL=https://www.facebook.com/share/184mEoARX8/
LINKEDIN_URL=https://www.linkedin.com/company/the-coding-science/
WHATSAPP_GROUP_LINK=https://chat.whatsapp.com/your-link

# UPI Payment
UPI_ID_1=your-upi@bank
UPI_NAME=The Coding Science

# Admin
ADMIN_EMAIL=admin@thecodingscience.com
ADMIN_PASSWORD=secure-password
```

---

## 🔐 Security Checklist

- [ ] Never commit `.env` file to GitHub (it's in `.gitignore`)
- [ ] Use strong SECRET_KEY (minimum 32 characters)
- [ ] Enable HTTPS on production domain
- [ ] Set `FLASK_ENV=production`
- [ ] Regularly update dependencies
- [ ] Monitor GitHub Security Alerts
- [ ] Keep API tokens safe
- [ ] Use environment variables for all secrets

---

## 📊 Monitoring Deployments

### GitHub Actions Dashboard
1. Go to your GitHub repository
2. Click **Actions** tab
3. View real-time build logs

### Railway Dashboard
1. Visit https://railway.app
2. Select your project
3. Monitor logs and metrics
4. View deployment history

---

## 🐛 Troubleshooting

### Issue: Tests Fail on GitHub Actions
**Solution:**
1. Check the error logs in GitHub Actions
2. Run `pytest tests/ -v` locally
3. Fix issues and commit again

### Issue: Deployment Fails
**Solution:**
1. Check Railway logs: `railway logs`
2. Verify environment variables in Railway dashboard
3. Check `requirements.txt` has all dependencies

### Issue: Database Not Initialized
**Solution:**
```bash
python -c "from app import db, app; app.app_context().push(); db.create_all(); print('Database initialized')"
```

### Issue: 404 or 500 Errors After Deploy
**Solution:**
1. Check Railway logs: `railway logs -f`
2. Verify SECRET_KEY is set
3. Restart the app: `railway down && railway up`

---

## 📚 File Structure for Deployment

```
TheCodingScience/
├── app.py                    # Main Flask app
├── config.py                 # Configuration
├── wsgi.py                   # WSGI entry point for production
├── requirements.txt          # Python dependencies
├── .env.example              # Example environment variables
├── .gitignore               # Git ignore rules
├── Procfile                 # Deployment configuration
├── runtime.txt              # Python version
├── .github/
│   └── workflows/
│       └── ci-cd.yml        # GitHub Actions workflow
├── templates/               # HTML templates
├── static/                  # CSS, JS, images
├── tests/                   # Test files
│   └── test_app.py
└── logs/                    # Application logs

```

---

## 🔄 CI/CD Pipeline Flow

```
Push Code to GitHub
        ↓
GitHub Actions Triggered
        ↓
Run Tests (Python 3.9, 3.10, 3.11)
        ↓
Security Check (Bandit, Safety)
        ↓
Code Quality Check (Pylint, Flake8)
        ↓
All Checks Pass?
    ├─ YES → Deploy to Railway
    └─ NO → Send Failure Notification
```

---

## 🎯 Next Steps

1. **Set up GitHub Repository**
   - Push code to GitHub with CI/CD workflow

2. **Configure Railway Account**
   - Create Railway project
   - Get API token

3. **Add GitHub Secrets**
   - Add RAILWAY_TOKEN to GitHub secrets

4. **Make Your First Deployment**
   - Push to main branch
   - Watch GitHub Actions automatically deploy

5. **Monitor Production**
   - Check Railway logs
   - Monitor uptime and performance

---

## 📞 Support Resources

- **Railway Docs:** https://docs.railway.app
- **GitHub Actions Docs:** https://docs.github.com/en/actions
- **Flask Docs:** https://flask.palletsprojects.com
- **Python Pytest:** https://docs.pytest.org

---

## 🎉 Congratulations!

Your Flask application is now set up for automated CI/CD deployment! 🚀

Every push to `main` branch will automatically:
- Run tests
- Check security
- Deploy to production (if all checks pass)

---

**Last Updated:** January 25, 2026
**Status:** ✅ Production Ready
