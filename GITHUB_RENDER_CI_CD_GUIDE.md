# 🚀 GitHub + CI/CD Deployment Guide

## Complete Setup for Automatic Redeployment

---

## 📋 Prerequisites

Before starting, you need:
1. GitHub account (free)
2. GitHub repository created
3. Git installed locally
4. Free hosting platform account

---

## 🏠 Free Hosting Platform Options

### Option 1: Render (Recommended) ⭐
- **Free Tier**: Yes (500 hours/month)
- **Database**: PostgreSQL included
- **CI/CD**: Built-in GitHub integration
- **Automatic Deploys**: Yes
- **Custom Domain**: Yes
- **Website**: render.com

### Option 2: Railway
- **Free Tier**: $5/month credit
- **Database**: PostgreSQL included
- **CI/CD**: GitHub integration
- **Automatic Deploys**: Yes
- **Website**: railway.app

### Option 3: PythonAnywhere
- **Free Tier**: Yes (limited)
- **Database**: MySQL included
- **CI/CD**: Manual deployment
- **Website**: pythonanywhere.com

### Option 4: Heroku (Legacy)
- **Free Tier**: Removed (was popular)
- **Not Recommended**: No longer free

### Option 5: Vercel
- **Free Tier**: Yes (frontend only)
- **Note**: Better for frontend, not ideal for Flask
- **Website**: vercel.com

**🏆 Recommended**: **Render** (Best free tier + automatic CI/CD)

---

## 📱 Step-by-Step Setup (Using Render)

### Step 1: Prepare Your GitHub Repository

#### 1.1 Initialize Git (if not done)
```bash
cd c:\Users\Mtechbro-94\Desktop\TheCodingScience

# Check if git is initialized
git status

# If not initialized:
git init
```

#### 1.2 Add All Files
```bash
# Add all files except those in .gitignore
git add .

# Check what's being added (should not include .env or __pycache__)
git status
```

#### 1.3 Create Initial Commit
```bash
git commit -m "Initial commit: Production-ready Flask application"
```

#### 1.4 Set Remote Repository
```bash
# Replace with your GitHub repo URL
git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPO-NAME.git

# Verify remote
git remote -v
```

#### 1.5 Push to GitHub
```bash
# Push to main branch
git branch -M main
git push -u origin main
```

---

### Step 2: Create Production-Ready Configuration

#### 2.1 Create `Procfile` (for Render)
```
# File: Procfile (no extension)
web: gunicorn -w 4 -b 0.0.0.0:$PORT wsgi:app
```

#### 2.2 Create `runtime.txt` (specify Python version)
```
# File: runtime.txt
python-3.11.7
```

#### 2.3 Create `.dockerignore` (for containerization)
```
# File: .dockerignore
__pycache__
.pytest_cache
.venv
venv
.git
.gitignore
.env
instance/
logs/
*.pyc
*.pyo
*.db
```

#### 2.4 Update `requirements.txt` (ensure production packages)
```
# Already done! Just verify it has:
Flask==2.3.3
gunicorn==21.2.0
Flask-Talisman==1.1.0
# ... all other packages
```

---

### Step 3: Set Up Render Account

#### 3.1 Create Account
1. Go to https://render.com
2. Sign up with GitHub account (easier authentication)
3. Verify email

#### 3.2 Connect GitHub Repository
1. Dashboard → New +
2. Select "Web Service"
3. Connect GitHub repository
4. Authorize Render to access GitHub

#### 3.3 Configure Build Settings
```
Name: the-coding-science
Environment: Python 3
Region: Choose closest to users
Build Command: pip install -r requirements.txt
Start Command: gunicorn -w 4 -b 0.0.0.0:$PORT wsgi:app
```

#### 3.4 Add Environment Variables
In Render dashboard → Environment:

```
# Required variables (copy from your .env):
FLASK_ENV=production
SECRET_KEY=your-super-secret-key-here
DATABASE_URL=<Render will provide PostgreSQL>
SENDER_EMAIL=your-email@gmail.com
SENDER_PASSWORD=your-app-password
ADMIN_EMAIL=admin@thecodingscience.com
ADMIN_PASSWORD=secure-password
SITE_NAME=The Coding Science
CONTACT_EMAIL=academy@thecodingscience.com
CONTACT_PHONE=+91-1234567890
CONTACT_LOCATION=Jammu and Kashmir, India
INSTAGRAM_URL=https://instagram.com/thecodingscience
YOUTUBE_URL=https://youtube.com/thecodingscience
FACEBOOK_URL=https://facebook.com/thecodingscience
LINKEDIN_URL=https://linkedin.com/company/thecodingscience
WHATSAPP_GROUP_LINK=https://chat.whatsapp.com/yourgroup
UPI_ID_1=your-upi@bank
UPI_ID_2=your-upi@bank
UPI_ID_3=your-upi@bank
UPI_NAME=The Coding Science
```

---

### Step 4: Enable Automatic Deployment

#### 4.1 In Render Dashboard
1. Go to your service
2. Settings → Auto-Deploy
3. Enable "Auto-deploy new commits"
4. Save

#### 4.2 Deployment Process
- **Automatic**: Every push to `main` branch triggers deployment
- **Build logs**: Visible in Render dashboard
- **Rollback**: Can revert to previous deployment if needed

---

## 🔄 GitHub → Render Automation Flow

```
┌─────────────────────┐
│  Your Local Machine │
│  (Edit Code)        │
└──────────┬──────────┘
           │
           │ git push
           │
┌──────────▼──────────┐
│     GitHub          │
│  (Repository)       │
└──────────┬──────────┘
           │
           │ Webhook trigger
           │
┌──────────▼──────────┐
│      Render         │
│  1. Pull code       │
│  2. Build app       │
│  3. Run tests       │
│  4. Deploy          │
│  5. Start server    │
└─────────────────────┘
           │
           ▼
    Website Live!
```

---

## 📝 GitHub Workflow

### Regular Development

```bash
# 1. Make changes to your code
# Edit files as needed

# 2. Stage changes
git add .

# 3. Commit with meaningful message
git commit -m "Feature: Add new course management functionality"

# 4. Push to GitHub
git push origin main

# 5. Render automatically:
#    - Detects the push
#    - Pulls latest code
#    - Runs build
#    - Deploys to live server
#    - Your site updates!
```

### Check Deployment Status

```bash
# View recent commits on GitHub
# https://github.com/YOUR-USERNAME/YOUR-REPO

# Check deployment status on Render
# https://dashboard.render.com → Your Service → Deployments
```

---

## 🔒 Security Notes for GitHub

### What to NEVER Commit
```
# .gitignore should exclude:
.env                  ✅ Excluded
instance/             ✅ Excluded
__pycache__/          ✅ Excluded
*.db                  ✅ Excluded
logs/                 ✅ Excluded
.venv/                ✅ Excluded
```

### Verify Before Pushing
```bash
# Check what will be pushed
git status

# Should NOT show:
# .env
# instance/
# __pycache__/
```

### Environment Variables
- Never add `.env` to GitHub
- Add environment variables in Render dashboard instead
- Render keeps credentials secure

---

## 🛠️ Initial Database Setup on Render

### First Deployment

When you first deploy, the database won't have tables or data.

#### Option A: Render SQL Console (Easy)
```bash
# In Render dashboard:
# 1. Go to your PostgreSQL database
# 2. Click "Connect" → SQL
# 3. Paste initialization SQL
```

#### Option B: SSH into Render Service
```bash
# Connect to deployed app
render connect

# Then run:
python -c "from app import app, init_db; init_db()"
```

#### Option C: Create Admin via Render Console
```bash
# Navigate to your service
# Settings → Post-Deploy Command
# Add: "python -c 'from app import app, init_db; init_db()'"
```

---

## 📊 Deployment Stages

### Stage 1: Build
```
✓ Pull code from GitHub
✓ Install dependencies (pip install -r requirements.txt)
✓ Run tests (optional)
✓ Compile static files (optional)
```

### Stage 2: Release
```
✓ Set environment variables
✓ Initialize database (if needed)
✓ Collect static files
✓ Run migrations (if needed)
```

### Stage 3: Deploy
```
✓ Start application server (Gunicorn)
✓ Bind to port $PORT
✓ Health checks
✓ Route traffic
```

---

## 🔍 Monitoring & Logs

### View Logs on Render
```
Dashboard → Your Service → Logs

Shows:
- Deployment progress
- Server startup messages
- Application errors
- Database connections
```

### Common Issues

#### Deployment Fails
1. Check build logs
2. Verify environment variables
3. Check `requirements.txt`
4. Check Python version compatibility

#### Website Not Loading
1. Check service status in Render
2. Check logs for errors
3. Verify database connection
4. Check environment variables

#### Database Issues
1. Verify DATABASE_URL is set
2. Check PostgreSQL connection
3. Run migration if needed
4. Check database user permissions

---

## 📱 Custom Domain Setup

### Add Custom Domain to Render

1. Purchase domain (Namecheap, GoDaddy, etc.)
2. Render dashboard → Settings → Custom Domains
3. Add your domain
4. Update DNS:
   ```
   CNAME: your-site.render.com
   TTL: 3600
   ```
5. Wait for DNS propagation (up to 48 hours)

---

## 🚀 Post-Deployment Checklist

- [ ] Website loads at provided URL
- [ ] All pages working
- [ ] Contact form working
- [ ] Admin panel accessible
- [ ] Database connected
- [ ] Emails sending
- [ ] Payment QR codes generating
- [ ] Social links working
- [ ] Custom domain (if added)
- [ ] SSL/HTTPS enabled (automatic on Render)

---

## 📋 Quick Reference

### Your New URLs

```
Render App URL:
https://your-app-name.onrender.com

Admin Panel:
https://your-app-name.onrender.com/admin/panel

Render Dashboard:
https://dashboard.render.com

GitHub Repository:
https://github.com/YOUR-USERNAME/YOUR-REPO
```

### Daily Workflow

```
1. Edit code locally
2. Test locally: python app.py
3. Push to GitHub: git push origin main
4. Render automatically redeploys
5. Website updates in 2-5 minutes
6. Check Render logs if issues
```

---

## 🎯 Troubleshooting

### Issue: "Build Failed"
**Solution**: 
1. Check build logs on Render
2. Verify requirements.txt has all dependencies
3. Check Python version in runtime.txt

### Issue: "Service Not Starting"
**Solution**:
1. Check Procfile is correct
2. Verify start command
3. Check environment variables
4. View application logs

### Issue: "502 Bad Gateway"
**Solution**:
1. Application might be restarting
2. Check logs for errors
3. Verify database connection
4. Check memory usage

### Issue: "Database Connection Failed"
**Solution**:
1. Verify DATABASE_URL in environment
2. Check PostgreSQL status
3. Verify credentials
4. Check if migrations needed

---

## 🔐 Environment Variables

### Development (.env local)
```env
FLASK_ENV=development
DEBUG=true
DATABASE_URL=sqlite:///coding_science.db
```

### Production (Render)
```env
FLASK_ENV=production
DEBUG=false
DATABASE_URL=postgresql://user:pass@host:port/db
# ... other production variables
```

---

## 📊 Costs

### Render Free Tier
- **Web Service**: Up to 750 hours/month (free)
- **PostgreSQL**: 90-day credit, then paid
- **Bandwidth**: Included
- **SSL**: Included
- **Custom Domain**: Yes
- **Total Cost**: Free to start, minimal if needed

### Typical Monthly Cost
```
Free: If under 750 hours/month + minimal DB
Paid: $7-20/month if production database needed
```

---

## 🎉 You're Live!

After completing these steps:

✅ Code on GitHub
✅ Auto-deployed to Render
✅ Every push triggers deployment
✅ Website updates in minutes
✅ SSL/HTTPS enabled
✅ Database included
✅ Admin panel working
✅ Zero manual deployment needed

---

## 📖 Next Steps

1. **Create GitHub repo** (if not done)
2. **Push code to GitHub**
3. **Create Render account**
4. **Connect GitHub → Render**
5. **Add environment variables**
6. **Enable auto-deploy**
7. **Test by pushing changes**
8. **Monitor logs**

---

**Status**: Ready for GitHub + Render CI/CD Setup
**Platform**: Render (recommended)
**Deployment**: Automatic on every push
**Cost**: Free tier available

🚀 Your website will be live in 30 minutes!
