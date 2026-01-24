# 🔧 CI/CD Setup with GitHub Actions

## What is CI/CD?

**CI/CD** = Continuous Integration / Continuous Deployment

```
Your Code → GitHub → Tests → Build → Deploy → Live Website
```

Every time you push code:
1. ✅ Tests run automatically
2. ✅ Code quality checked
3. ✅ Deployed to production
4. ❌ If tests fail, deployment blocked

---

## Setup Instructions

### Step 1: Create Render Deploy Hook

A "Deploy Hook" tells Render to redeploy when you push to GitHub.

#### 1.1 In Render Dashboard
1. Go to your service
2. Settings → Deploy Hook
3. Copy the webhook URL (looks like: `https://api.render.com/deploy/srv-xxxxx?key=xxxxx`)

#### 1.2 Add Hook to GitHub Secrets
1. GitHub Repo → Settings → Secrets and variables → Actions
2. Click "New repository secret"
3. Name: `RENDER_DEPLOY_HOOK_URL`
4. Value: Paste the Render webhook URL
5. Click "Add secret"

### Step 2: CI/CD Workflow is Ready!

The file `.github/workflows/ci-cd.yml` is already created with:

✅ **Testing Stage**:
- Installs dependencies
- Runs linting (flake8)
- Checks code formatting (black)
- Runs unit tests (pytest)

✅ **Deployment Stage**:
- Only runs if tests pass
- Calls Render deploy hook
- Website updates in 1-2 minutes

✅ **Notification Stage**:
- Summarizes deployment status

---

## How It Works

### On Every Push to GitHub

```
1. You: git push origin main
   ↓
2. GitHub: Detects push
   ↓
3. GitHub Actions: Starts workflow
   ↓
4. Tests: Run linting, formatting, tests
   ├─ ✅ All pass? → Continue
   └─ ❌ Any fail? → Skip deployment
   ↓
5. Deployment: Call Render webhook
   ↓
6. Render: Pull code and redeploy
   ↓
7. Website: Live with your changes (2-5 min)
```

---

## View Workflow Status

### On GitHub

1. Go to your repo
2. Click "Actions" tab
3. See all workflow runs
4. Click on a run to see details

**Shows**:
- ✅ Passed / ❌ Failed tests
- Build logs
- Deployment status
- Timing information

### Example Workflow Display

```
Commit: "Add new course feature"
  ✅ Tests (2 min)
    ✅ Setup Python
    ✅ Install dependencies
    ✅ Lint with flake8
    ✅ Format check (black)
    ✅ Tests with pytest
  ✅ Deploy (1 min)
    ✅ Call Render webhook
  ✅ Notify (10 sec)
    ✅ Workflow completed!

Total: 3 minutes
```

---

## Test Your CI/CD Pipeline

### Make a Test Push

```bash
# 1. Edit a file (e.g., add comment to app.py)
# 2. Save and stage
git add .

# 3. Commit with message
git commit -m "Test: Verify CI/CD pipeline"

# 4. Push to GitHub
git push origin main

# 5. Watch the magic!
```

### Monitor Progress

1. Go to GitHub repo
2. Click "Actions" tab
3. Click the latest workflow run
4. Watch tests run in real-time
5. If all pass, Render deploys automatically

---

## Continuous Integration Features

### 1. Code Quality Checks

**Linting (flake8)**:
```bash
# Checks for:
- Syntax errors
- Undefined variables
- Unused imports
- Code style violations
```

**Formatting (black)**:
```bash
# Ensures consistent code style
- Indentation
- Spacing
- Line length
```

### 2. Automated Testing

**pytest**:
```bash
# Run all tests in tests/ folder
pytest tests/ -v

# Example tests:
- Login works
- Course enrollment works
- Contact form works
- Admin panel accessible
```

### 3. Database Testing

```
Postgres test database
↓
Run all tests with real database
↓
Verify no SQL errors
```

---

## Creating Tests (Optional)

If you want to add tests:

### Step 1: Create Tests Folder
```bash
mkdir tests
```

### Step 2: Create Test File
```python
# File: tests/test_app.py
import pytest
from app import app

@pytest.fixture
def client():
    app.config['TESTING'] = True
    with app.test_client() as client:
        yield client

def test_home_page(client):
    """Test home page loads"""
    response = client.get('/')
    assert response.status_code == 200

def test_login_page(client):
    """Test login page loads"""
    response = client.get('/login')
    assert response.status_code == 200

def test_courses_page(client):
    """Test courses page loads"""
    response = client.get('/courses')
    assert response.status_code == 200
```

### Step 3: Run Locally
```bash
pytest tests/ -v
```

### Step 4: Commit and Push
```bash
git add tests/
git commit -m "Add unit tests"
git push origin main
```

Tests automatically run on push!

---

## GitHub Actions Benefits

### ✅ Automatic Testing
Every push is tested before deployment

### ✅ Deployment Blocking
If tests fail, deployment doesn't happen

### ✅ Code Quality
Linting and formatting enforced

### ✅ Documentation
Clear record of all deployments and tests

### ✅ Team Collaboration
See who broke what and when

---

## Troubleshooting CI/CD

### Workflow Won't Start
- Check if `.github/workflows/ci-cd.yml` exists
- Verify it's in the right folder
- Check GitHub Actions is enabled in repo settings

### Tests Failing
- Check the test output in GitHub Actions
- Read error messages
- Fix code locally
- Push fixed code to re-run

### Deployment Not Triggering
- Verify `RENDER_DEPLOY_HOOK_URL` secret is added
- Check secret value is correct
- Ensure it's named exactly `RENDER_DEPLOY_HOOK_URL`
- Go to Secrets → Actions and verify it's there

### Deploy Hook URL Not Working
- Copy webhook URL again from Render
- Update GitHub secret
- Delete existing secret first
- Add new secret with fresh URL

---

## Environment for Tests

The workflow uses test environment:

```env
# .github/workflows/ci-cd.yml
FLASK_ENV: testing
SECRET_KEY: test-secret-key
DATABASE_URL: postgresql://test_user:test_pass@localhost/test_db
```

This ensures:
- Tests use test database
- No production data affected
- Clean test environment each run

---

## Deployment Flow Details

### What Happens on Render

When webhook is called:

```
1. Render receives webhook
2. Pull latest code from GitHub
3. Run `pip install -r requirements.txt`
4. Run `gunicorn -w 4 -b 0.0.0.0:$PORT wsgi:app`
5. Verify service started
6. Route traffic to new version
7. Old version stops
8. New version live
```

Time: ~2-5 minutes

---

## Status Badges (Optional)

Add this to your README.md to show CI/CD status:

```markdown
![CI/CD Pipeline](https://github.com/YOUR-USERNAME/YOUR-REPO/workflows/Tests%20&%20Deployment/badge.svg)
```

Shows ✅ green if latest push passed all tests

---

## Next Steps

1. ✅ Push code to GitHub (if not done)
2. ✅ Add Render webhook secret
3. ✅ Test by pushing a change
4. ✅ Monitor in GitHub Actions
5. ✅ Verify deployment on Render
6. ✅ Check website is live with changes

---

## Summary

**What We Set Up**:
- ✅ Automatic tests on every push
- ✅ Code quality checks (linting, formatting)
- ✅ Automatic deployment if tests pass
- ✅ Clear visibility in GitHub Actions dashboard

**Result**: 
Every time you push to GitHub, your website automatically rebuilds and redeploys with zero manual steps!

---

**Status**: CI/CD Ready
**Next**: Create your GitHub repo and add Render deploy hook secret
