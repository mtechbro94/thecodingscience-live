# 🚀 Render Deployment Setup (Step-by-Step)

## ✅ GitHub Status
**Repository**: https://github.com/mtechbro94/thecodingscience-live
**Branch**: main
**Status**: ✅ Code pushed successfully

---

## 📋 Phase 1: Create Render Account

### Step 1.1: Go to Render.com
- Open: https://render.com
- Click "Get Started" or "Sign Up"

### Step 1.2: Sign Up
**Recommended**: Sign up with GitHub account (easier authentication)
- Click "Sign up with GitHub"
- Authorize Render to access your GitHub account
- Verify email if needed

---

## 📱 Phase 2: Create Web Service on Render

### Step 2.1: Dashboard Setup
1. After login, go to Render Dashboard
2. Click **"New +"** button (top right)
3. Select **"Web Service"**

### Step 2.2: Connect GitHub Repository
1. Click **"Connect a repository"**
2. Find and select: `mtechbro94/thecodingscience-live`
3. Click **"Connect"**

### Step 2.3: Configure Web Service

Fill in the following fields:

```
Name:                          the-coding-science
Environment:                   Python 3
Build Command:                 pip install -r requirements.txt
Start Command:                 gunicorn -w 4 -b 0.0.0.0:$PORT wsgi:app
Region:                        Choose closest to your users
Plan:                          Free
```

### Step 2.4: Environment Variables

**CRITICAL**: Add all environment variables before deploying.

In Render dashboard, go to **Environment** section and add:

#### Part A: Core Configuration
```
FLASK_ENV                      production
SECRET_KEY                     generate-a-random-string-here
FLASK_APP                      wsgi:app
```

To generate SECRET_KEY, use this in terminal:
```bash
python -c "import secrets; print(secrets.token_hex(32))"
```

#### Part B: Database
Render will automatically provide PostgreSQL. Copy the full connection string:
```
DATABASE_URL                   postgresql://user:pass@host:port/db
```
(Render generates this - you'll get it after service creation)

#### Part C: Site Configuration
```
SITE_NAME                      The Coding Science
SITE_TAGLINE                   Learn, Code, Create
CONTACT_EMAIL                  academy@thecodingscience.com
CONTACT_PHONE                  +91-XXXXXXXXXX
CONTACT_LOCATION              Jammu and Kashmir, India
```

#### Part D: Social Media
```
INSTAGRAM_URL                  https://instagram.com/thecodingscience
YOUTUBE_URL                    https://youtube.com/@thecodingscience
FACEBOOK_URL                   https://facebook.com/thecodingscience
LINKEDIN_URL                   https://linkedin.com/company/thecodingscience
WHATSAPP_GROUP_LINK           https://chat.whatsapp.com/yourgroup
```

#### Part E: Payment Methods
```
UPI_ID_1                       your-upi@bank
UPI_ID_2                       your-upi@bank
UPI_ID_3                       your-upi@bank
UPI_NAME                       The Coding Science
```

#### Part F: Email Configuration
```
SENDER_EMAIL                   your-email@gmail.com
SENDER_PASSWORD                your-app-password
```

**Note**: For Gmail, use App Password (not regular password):
1. Enable 2-Factor Authentication in Google Account
2. Go to https://myaccount.google.com/apppasswords
3. Generate app password for "Mail"
4. Use that password here

#### Part G: Admin Credentials
```
ADMIN_EMAIL                    admin@thecodingscience.com
ADMIN_PASSWORD                 your-secure-password-here
```

#### Part H: Logging
```
LOG_LEVEL                      INFO
LOG_FILE                       /tmp/app.log
```

---

## 🔐 Phase 3: Database Setup

### Step 3.1: Render PostgreSQL

After creating the web service:

1. Still in Render dashboard
2. Click **"New +"**
3. Select **"PostgreSQL"**
4. Fill in:
   ```
   Name:          the-coding-science-db
   Region:        Same as web service
   Database:      coding_science
   User:          postgres
   Plan:          Free
   ```

### Step 3.2: Connect Database to Web Service

1. Go to your web service (the-coding-science)
2. Settings → Environment
3. Add DATABASE_URL from PostgreSQL service:
   - Copy the connection string from PostgreSQL service info
   - Paste into DATABASE_URL

### Step 3.3: Initialize Database

After first deployment, initialize the database:

**Option A: Using Render Shell**
1. Go to web service
2. Shell tab
3. Run: `python -c "from app import app, db; app.app_context().push(); db.create_all()"`

**Option B: Add Post-Deploy Command**
1. Settings → Post-Deploy Command
2. Add: `python -c "from app import app, db; app.app_context().push(); db.create_all()"`

---

## 🔗 Phase 4: Get Deploy Hook URL

This is critical for CI/CD automation.

### Step 4.1: In Render Dashboard
1. Go to your Web Service: **the-coding-science**
2. Click **"Settings"** (left sidebar)
3. Scroll down to **"Deploy Hook"**
4. You'll see a URL like:
   ```
   https://api.render.com/deploy/srv-xxxxxxxxxxxxx?key=xxxxxxxxxxxxx
   ```

### Step 4.2: Copy the URL
- Click the copy button next to the URL
- Save it somewhere safe (you'll need it for GitHub)

---

## 🚀 Phase 5: Deploy

### Step 5.1: Trigger Initial Deployment
1. Go back to Web Service
2. Click **"Deploy latest commit"**
3. Wait for deployment (2-5 minutes)

### Step 5.2: Monitor Deployment
- Check Logs tab to see build progress
- Should see:
  ```
  ✓ Building Docker image...
  ✓ Pushing image to registry...
  ✓ Deploying image...
  ✓ Running migrations...
  ✓ Service is live!
  ```

### Step 5.3: Verify Website
- Go to URL provided: `https://the-coding-science.onrender.com`
- Check if website loads
- Try admin panel: `/admin/panel`
- Test contact form

---

## 📝 Phase 6: GitHub Actions Setup

### Step 6.1: Add GitHub Secret
1. Go to GitHub: https://github.com/mtechbro94/thecodingscience-live
2. Click **Settings** tab
3. Left sidebar → **Secrets and variables** → **Actions**
4. Click **"New repository secret"**
5. Fill in:
   ```
   Name:  RENDER_DEPLOY_HOOK_URL
   Value: (Paste the full URL from Step 4.2)
   ```
6. Click **"Add secret"**

### Step 6.2: Verify CI/CD Workflow
1. Go to **Actions** tab in GitHub
2. Should see **"Tests & Deployment"** workflow
3. Initial commit should trigger the workflow

---

## ✅ Post-Deployment Checklist

After everything is set up, verify:

- [ ] Website loads at: https://the-coding-science.onrender.com
- [ ] Home page displays correctly
- [ ] Navigation works (courses, services, about, etc.)
- [ ] Admin panel accessible at: `/admin/panel`
- [ ] Admin login works with credentials set
- [ ] Contact form works
- [ ] Can create student accounts
- [ ] Can enroll in courses
- [ ] Database is connected (check Render logs)
- [ ] No error messages in browser console
- [ ] Images load properly
- [ ] CSS styling applied correctly
- [ ] GitHub secret `RENDER_DEPLOY_HOOK_URL` is set
- [ ] GitHub Actions workflow is green (all passed)

---

## 🔄 CI/CD Automation Testing

### Step 1: Make a Test Change
```bash
cd c:\Users\Mtechbro-94\Desktop\TheCodingScience

# Edit a file (e.g., add comment to README.md)
# Then stage and commit:
git add README.md
git commit -m "Test: Verify CI/CD automation"
git push origin main
```

### Step 2: Watch GitHub Actions
1. Go to GitHub Actions tab
2. Click the latest workflow run
3. Watch it execute (should take 3-5 minutes)
4. All tests should pass (green checkmarks)

### Step 3: Watch Render Deploy
1. Go to Render dashboard
2. Click your web service
3. See "New deployment in progress"
4. Wait for "Live" status

### Step 4: Verify Website Updated
- Your test change should appear on website

---

## 🎯 Your Production URLs

```
Website:              https://the-coding-science.onrender.com
Admin Panel:          https://the-coding-science.onrender.com/admin/panel
GitHub Repo:          https://github.com/mtechbro94/thecodingscience-live
GitHub Actions:       https://github.com/mtechbro94/thecodingscience-live/actions
Render Dashboard:     https://dashboard.render.com
```

---

## 📊 Deployment Architecture

```
Your Code (Local)
       ↓
    git push
       ↓
GitHub Repository
       ↓
GitHub Actions
  - Run tests
  - Check formatting
  - Verify code quality
       ↓
    ✅ Tests Pass?
       ↓
  Call Render Webhook
       ↓
Render Service
  - Pull latest code
  - Install dependencies
  - Start Gunicorn
  - Database connected
       ↓
PostgreSQL Database
       ↓
  Website Live! 🎉
```

---

## 🔍 Troubleshooting

### Issue: Deployment Failed
**Check**:
- View build logs in Render
- Verify environment variables are set
- Check requirements.txt is valid
- Check Python version (should be 3.11)

### Issue: Website Not Loading
**Check**:
- Service is running (Render dashboard)
- Environment variables are set
- Database connection is working
- Check error logs

### Issue: Database Error
**Check**:
- PostgreSQL service is running
- DATABASE_URL is correctly set
- Run: `python -c "from app import app, db; app.app_context().push(); db.create_all()"`
- Check Render PostgreSQL dashboard

### Issue: GitHub Actions Not Running
**Check**:
- `.github/workflows/ci-cd.yml` exists in repo
- GitHub Actions is enabled
- Secrets are set correctly

### Issue: CI/CD Not Triggering Deploy
**Check**:
- `RENDER_DEPLOY_HOOK_URL` secret exists
- Secret value is correct (full URL)
- Check GitHub Actions logs for errors

---

## 🎉 Success!

Once everything is set up:

✅ Every `git push` automatically tests and deploys
✅ Website updates in 2-5 minutes
✅ No manual deployment needed
✅ Zero-downtime deployments
✅ Automatic HTTPS/SSL
✅ Professional production setup

---

## 📞 Quick Reference

```bash
# After setup, just use normal git workflow:
git add .
git commit -m "Your feature"
git push origin main

# Automation takes over:
# 1. GitHub Actions runs tests (2-3 min)
# 2. Render deploys (2-5 min)
# 3. Website updates automatically

# Check status:
# - GitHub Actions: https://github.com/mtechbro94/thecodingscience-live/actions
# - Render Dashboard: https://dashboard.render.com
```

---

**Status**: Ready for Render Setup
**Next Steps**: Follow phases 1-6 above in order
**Time Estimate**: 20-30 minutes
**Cost**: Free tier available ($0/month)

🚀 Your website will be live and auto-deploying!
