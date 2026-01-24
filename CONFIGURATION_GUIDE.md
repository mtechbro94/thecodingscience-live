# 🎨 Website Configuration & Dynamic Features Guide

## Overview

Your website is **100% configurable and dynamic**. No hardcoded values - everything is environment-based or database-driven.

---

## ✅ Fully Configurable Elements

### 1. Site Branding (No Code Changes Needed)

Everything can be changed via `.env` file:

```env
# Change website name and tagline
SITE_NAME=Your Company Name
SITE_TAGLINE=Your Custom Tagline

# Change location
CONTACT_LOCATION=Your City, State, Country

# Change contact info
CONTACT_EMAIL=your-email@yourdomain.com
CONTACT_PHONE=+91-your-number
```

These appear automatically across all pages because they're injected into every template.

### 2. Social Media Links (No Code Changes Needed)

Update social links in `.env`:

```env
INSTAGRAM_URL=https://www.instagram.com/your-handle
YOUTUBE_URL=https://youtube.com/@your-channel
FACEBOOK_URL=https://www.facebook.com/your-page
LINKEDIN_URL=https://www.linkedin.com/company/your-company
WHATSAPP_GROUP_LINK=https://chat.whatsapp.com/your-group-link
```

All links update automatically across the site.

### 3. Payment Configuration (No Code Changes Needed)

Change payment methods in `.env`:

```env
UPI_ID_1=your-upi-id@bank
UPI_ID_2=your-second-upi@bank
UPI_ID_3=your-third-upi@bank
UPI_NAME=Your Organization Name
```

Payment QR codes generate dynamically based on these values.

### 4. Email Configuration (No Code Changes Needed)

Configure email sending via `.env`:

```env
SENDER_EMAIL=noreply@yourdomain.com
SENDER_PASSWORD=your-app-specific-password
```

All emails use these credentials automatically.

### 5. Admin Credentials (No Code Changes Needed)

Set admin account via `.env`:

```env
ADMIN_EMAIL=admin@yourdomain.com
ADMIN_PASSWORD=your-secure-password
```

Admin account is created automatically on first run.

---

## 🗄️ Dynamic Content (Database-Driven)

### 1. Courses (100% Dynamic)

Courses are stored in database and fully editable:

```python
# Courses stored in: courses table
# Properties: name, description, duration, price, level, image, curriculum

# Add courses programmatically:
from app import db, Course

course = Course(
    name='Course Name',
    description='Course description',
    duration='3 Months',
    price=499,
    level='Beginner',
    curriculum='["Module 1", "Module 2"]'
)
db.session.add(course)
db.session.commit()
```

### 2. Student Data (100% Dynamic)

Student information stored dynamically:

```python
# Student data stored in: users table
# Properties: email, name, phone, password_hash, is_admin, is_active
# All editable via admin panel
```

### 3. Enrollments (100% Dynamic)

Course enrollments tracked dynamically:

```python
# Enrollments stored in: enrollments table
# Properties: user_id, course_id, status, payment_method, amount_paid
# Status can be: pending, completed, failed, refunded
```

### 4. Contact Messages (100% Dynamic)

Contact form submissions stored in database:

```python
# Messages stored in: contact_messages table
# Fully searchable and manageable via admin panel
```

### 5. Internship Applications (100% Dynamic)

Internship applications stored and tracked:

```python
# Applications stored in: internship_applications table
# Status can be: pending, approved, rejected
```

---

## 🎯 How Configurations Work

### Flow Diagram

```
.env file (Environment Variables)
    ↓
app.py (Reads via os.getenv())
    ↓
@app.context_processor (Injects into templates)
    ↓
Templates (Uses injected variables)
    ↓
Rendered HTML (All values from .env)
```

### Example: Changing Site Name

**Before**: Site always showed "The Coding Science"
**After**: Site shows whatever is in `SITE_NAME` variable

```bash
# No code changes needed!
# Just update .env:
SITE_NAME=My Academy

# Restart application
# Website now shows "My Academy" everywhere
```

---

## 🔧 Configuration Options

### Complete Configuration Reference

```env
# ====== SITE BRANDING ======
SITE_NAME=The Coding Science              # Website name
SITE_TAGLINE=Learn Technology...          # Tagline

# ====== CONTACT INFO ======
CONTACT_EMAIL=academy@example.com         # Contact email
CONTACT_PHONE=+91-1234567890              # Contact phone
CONTACT_LOCATION=City, State, Country     # Location

# ====== SOCIAL MEDIA ======
INSTAGRAM_URL=https://instagram.com/...   # Instagram profile
YOUTUBE_URL=https://youtube.com/@...      # YouTube channel
FACEBOOK_URL=https://facebook.com/...     # Facebook page
LINKEDIN_URL=https://linkedin.com/...     # LinkedIn company
WHATSAPP_GROUP_LINK=https://chat.whatsapp... # WhatsApp group

# ====== PAYMENT ======
UPI_ID_1=user@bank                         # UPI payment ID 1
UPI_ID_2=user@bank                         # UPI payment ID 2
UPI_ID_3=user@bank                         # UPI payment ID 3
UPI_NAME=Organization Name                # UPI recipient name

# ====== EMAIL ======
SENDER_EMAIL=noreply@example.com           # Email sender
SENDER_PASSWORD=app-password               # Email password

# ====== ADMIN ======
ADMIN_EMAIL=admin@example.com              # Admin email
ADMIN_PASSWORD=secure-password             # Admin password

# ====== DATABASE ======
DATABASE_URL=postgresql://...              # Database connection

# ====== SECURITY ======
SECRET_KEY=your-random-secret-key          # Flask secret key
FLASK_ENV=production                       # Environment (dev/test/prod)

# ====== LOGGING ======
LOG_LEVEL=INFO                             # Log level
LOG_FILE=logs/app.log                      # Log file path
```

---

## 🌐 Template Variables Available

All templates automatically have access to these variables:

```jinja2
<!-- In any template, these are available: -->

{{ site_name }}              → Your site name
{{ site_tagline }}           → Your site tagline
{{ contact_email }}          → Contact email
{{ contact_phone }}          → Contact phone
{{ contact_location }}       → Location
{{ social_links.instagram }} → Instagram URL
{{ social_links.youtube }}   → YouTube URL
{{ social_links.facebook }}  → Facebook URL
{{ social_links.linkedin }}  → LinkedIn URL
```

### Example Template Usage

```html
<!-- base.html or any template -->

<footer>
    <h3>{{ site_name }}</h3>
    <p>{{ site_tagline }}</p>
    
    <div class="social-links">
        <a href="{{ social_links.instagram }}">Instagram</a>
        <a href="{{ social_links.youtube }}">YouTube</a>
        <a href="{{ social_links.facebook }}">Facebook</a>
    </div>
    
    <div class="contact">
        <p>Email: {{ contact_email }}</p>
        <p>Phone: {{ contact_phone }}</p>
        <p>Location: {{ contact_location }}</p>
    </div>
</footer>
```

---

## 🚀 Changing Configuration Without Restarting

### For Development (Not Recommended)
If you need to change config without restarting:

```python
# In Flask shell:
from app import app
import os

# Change environment variable
os.environ['SITE_NAME'] = 'New Name'

# Application still uses old value until restart
# This is why restart is needed for production
```

### Best Practice (Recommended)
1. Update `.env` file
2. Restart application
3. Changes take effect immediately

---

## 📋 Configuration Checklist

Before going live, ensure all these are configured:

- [ ] **SITE_NAME** - Your company/website name
- [ ] **SITE_TAGLINE** - Your tagline/motto
- [ ] **CONTACT_EMAIL** - Support email address
- [ ] **CONTACT_PHONE** - Support phone number
- [ ] **CONTACT_LOCATION** - Your location
- [ ] **INSTAGRAM_URL** - Instagram profile link
- [ ] **YOUTUBE_URL** - YouTube channel link
- [ ] **FACEBOOK_URL** - Facebook page link
- [ ] **LINKEDIN_URL** - LinkedIn company link
- [ ] **WHATSAPP_GROUP_LINK** - WhatsApp group link
- [ ] **UPI_ID_1, 2, 3** - Payment UPI IDs
- [ ] **UPI_NAME** - Payment recipient name
- [ ] **SENDER_EMAIL** - Email sender address
- [ ] **SENDER_PASSWORD** - Email app password
- [ ] **ADMIN_EMAIL** - Admin account email
- [ ] **ADMIN_PASSWORD** - Admin account password
- [ ] **SECRET_KEY** - Secure random key
- [ ] **DATABASE_URL** - Database connection string

---

## 🎨 Customization Examples

### Example 1: Change Everything for Different Brand

```env
# Original
SITE_NAME=The Coding Science
CONTACT_EMAIL=academy@thecodingscience.com

# New Brand (Just change .env)
SITE_NAME=Tech Academy Pro
CONTACT_EMAIL=support@techacademypro.com
```

### Example 2: Multiple Environments

**Development .env**:
```env
SITE_NAME=The Coding Science [DEV]
FLASK_ENV=development
LOG_LEVEL=DEBUG
```

**Production .env**:
```env
SITE_NAME=The Coding Science
FLASK_ENV=production
LOG_LEVEL=INFO
```

### Example 3: White-Label Setup

Same code, different branding per client:

```bash
# Client 1
SITE_NAME=Client 1 Academy
CONTACT_EMAIL=support@client1.com
INSTAGRAM_URL=https://instagram.com/client1

# Client 2 (Same code)
SITE_NAME=Client 2 Academy
CONTACT_EMAIL=support@client2.com
INSTAGRAM_URL=https://instagram.com/client2
```

---

## 💾 Dynamic Database Features

### Courses Management
- ✅ Add/edit/delete courses via admin panel
- ✅ Change pricing dynamically
- ✅ Update course descriptions
- ✅ Add/remove modules
- ✅ Change course levels

### Student Management
- ✅ View all students
- ✅ Manage student accounts
- ✅ View student enrollments
- ✅ Track payment status
- ✅ Send emails to students

### Enrollment Management
- ✅ View all enrollments
- ✅ Verify pending enrollments
- ✅ Bulk verify enrollments
- ✅ Track payment methods
- ✅ Send confirmation emails

### Contact Management
- ✅ View all contact messages
- ✅ Search and filter messages
- ✅ Track inquiries
- ✅ Respond to messages

---

## 🔐 Security Note

**Important**: Never commit your `.env` file to version control!

```bash
# DO: Use .env.example
git commit .env.example ✅

# DON'T: Commit actual .env
git commit .env ❌

# Check .gitignore
cat .gitignore  # Should include .env
```

---

## 🆚 Configurable vs Hardcoded

### Before Production Conversion
```python
# HARDCODED (Bad)
site_name = 'The Coding Science'
contact_email = 'academy@thecodingscience.com'
instagram_url = 'https://www.instagram.com/thecodingscience'
# Had to change code to change site name!
```

### After Production Conversion
```python
# DYNAMIC (Good)
site_name = os.getenv('SITE_NAME', 'The Coding Science')
contact_email = os.getenv('CONTACT_EMAIL', 'academy@thecodingscience.com')
instagram_url = os.getenv('INSTAGRAM_URL', 'https://instagram.com/...')
# Just update .env file - no code changes!
```

---

## 📊 Configuration Impact

| Element | Type | How to Change | Requires Restart |
|---------|------|---------------|------------------|
| Site Name | Config | `.env` SITE_NAME | ✅ Yes |
| Contact Info | Config | `.env` CONTACT_* | ✅ Yes |
| Social Links | Config | `.env` *_URL | ✅ Yes |
| Payment IDs | Config | `.env` UPI_ID_* | ✅ Yes |
| Courses | Dynamic | Admin Panel | ❌ No |
| Students | Dynamic | Admin Panel | ❌ No |
| Enrollments | Dynamic | Admin Panel | ❌ No |
| Messages | Dynamic | Auto-saved | ❌ No |

---

## 🎯 Multi-Environment Setup

Your application supports three environments:

### Development
```env
FLASK_ENV=development
DEBUG=true
LOG_LEVEL=DEBUG
DATABASE_URL=sqlite:///coding_science.db
```

### Testing
```env
FLASK_ENV=testing
DEBUG=true
DATABASE_URL=sqlite:///:memory:
```

### Production
```env
FLASK_ENV=production
DEBUG=false
LOG_LEVEL=INFO
DATABASE_URL=postgresql://user:pass@host/db
```

---

## ✨ Summary

Your website is now:

✅ **100% Configurable** - All site settings in `.env`
✅ **Database-Driven** - All content in database
✅ **No Hardcoding** - No values hardcoded in code
✅ **Easy to Customize** - Change `.env` to customize
✅ **Multi-Environment** - Dev/Test/Prod support
✅ **Admin Panel Ready** - Manage content via admin
✅ **White-Label Ready** - Same code, different branding

---

**Status**: ✅ Production Ready & Fully Dynamic
**Configuration**: Complete & Flexible
**Customization**: Zero Code Changes Needed

🎨 Your website is ready for any configuration! 🚀
