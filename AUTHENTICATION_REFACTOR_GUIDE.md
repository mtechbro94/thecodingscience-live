# Authentication System Refactor - Deployment Guide

## Overview
This document describes the complete authentication system refactor for The Coding Science platform, implementing separate login flows for trainers (Email + OTP) and students (Gmail OAuth only).

---

## Architecture Changes

### Authentication Flows

#### 1. **Trainer Authentication** (New)
- **Login URL**: `/trainer_login`
- **Flow**: Email + Password → OTP Email Verification → Dashboard
- **Database**: Uses `users` table with new OTP fields
- **API Endpoint**: `/api/trainer_auth.php`
- **Actions**:
  - `send_otp` - Send OTP code for an existing trainer login
  - `verify_otp` - Verify OTP and authenticate trainer
- **Features**:
  - 10-minute OTP expiration
  - Access limited to trainer accounts already created by the team
  - Profile management (name, phone, education, expertise, bio)

#### 2. **Student Authentication** (Updated)
- **Login URL**: `/student_login`
- **Flow**: Gmail OAuth Only (No password needed)
- **Database**: Uses `users` table with `gmail_id` field
- **API Endpoint**: `/api/student_auth.php`
- **Actions**:
  - `verify_gmail` - Verify Gmail token and create/login student
- **Features**:
  - Seamless Gmail integration
  - Auto-account creation on first login
  - Profile management (name, phone, profile photo)

---

## Database Changes

### Migration File
**File**: `database/migrate_auth_refactor.sql`

### Supporting Tables
```sql
CREATE TABLE `otp_tokens` {
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11),
  `email` VARCHAR(120) NOT NULL,
  `otp_code` VARCHAR(6) NOT NULL,
  `purpose` ENUM('login', 'registration', 'password_reset'),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME,
  PRIMARY KEY (`id`)
}
```

### New Columns in `users` Table
```sql
ALTER TABLE `users` ADD COLUMN `username` VARCHAR(100) UNIQUE;
ALTER TABLE `users` ADD COLUMN `gmail_id` VARCHAR(255) UNIQUE;
ALTER TABLE `users` ADD COLUMN `otp_code` VARCHAR(6);
ALTER TABLE `users` ADD COLUMN `otp_expires_at` DATETIME;
ALTER TABLE `users` ADD COLUMN `otp_verified` TINYINT(1) DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

---

## New Files Created

### 1. **Authentication APIs**
- `api/trainer_auth.php` - Trainer OTP authentication
- `api/student_auth.php` - Student Gmail OAuth verification

### 2. **Mail Utilities**
- `includes/mail.php` - Email and OTP utilities
  - `send_email()` - Send emails via PHPMailer
  - `send_otp_email()` - Send OTP codes
  - `cleanup_expired_otps()` - Clean expired tokens

### 3. **Login Pages**
- `views/trainer_login.php` - Trainer login with OTP
- `views/student_login.php` - Student login with Gmail

### 4. **Database**
- `database/migrate_auth_refactor.sql` - Migration script

---

## Modified Files

### 1. **SocialAuth.php** (`includes/SocialAuth.php`)
- **Removed**: GitHub OAuth configuration
- **Kept**: Google OAuth only
- **Updated**: User info retrieval (only Google)

### 2. **Social Login** (`views/social_login.php`)
- **Changed**: Only allows 'google' provider
- **Error Message**: Updated for single provider

### 3. **Social Callback** (`views/social_callback.php`)
- **Updated**: Works with new `gmail_id` field system
- **Removed**: Legacy OAuth provider system
- **Redirect**: Always redirects to `/student_login`

### 4. **Login Page** (`views/login.php`)
- **Removed**: GitHub button from trainer section
- **Kept**: Role selection and trainer OTP flow

### 5. **Profile Page** (`views/profile.php`)
- **Enhanced**: Support for both students and trainers
- **New Fields** (Trainers): Education, Expertise, Bio
- **Improved**: File upload handling

---

## Environment Variables Required

Add/Update in `.env` file:

```env
# Gmail OAuth (from Google Cloud Console)
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret

# Email Configuration (for OTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
MAIL_FROM=noreply@thecodingscience.com

# Site Configuration
SITE_NAME=The Coding Science
SITE_URL=https://thecodingscience.com
```

---

## Deployment Steps

### Step 1: Database Migration
```bash
# On your HostMyIdea server via SSH:
mysql -u thecodin -p your_database < /home/thecodin/public_html/database/migrate_auth_refactor.sql
```

### Step 2: Update .env
Add/update the environment variables listed above.

### Step 3: Composer Dependencies
If not using PHPMailer yet, install it:
```bash
composer require phpmailer/phpmailer
```

### Step 4: File Permissions
```bash
# Ensure upload directory is writable
chmod 755 /home/thecodin/public_html/assets/images/profiles/
```

### Step 5: Clear Sessions (Optional)
```bash
# Clear any old sessions to prevent conflicts
rm -rf /tmp/php_sessions/* 2>/dev/null || true
```

### Step 6: Deploy Code
Push all changes to GitHub, which will trigger automatic deployment via GitHub Actions.

---

## Testing Checklist

### Trainer Flow
- [ ] Navigate to `/trainer_login`
- [ ] Enter email and password
- [ ] Receive OTP email
- [ ] Enter OTP and verify login
- [ ] Check redirect to `/trainer_dashboard`
- [ ] Verify unknown trainer emails are rejected with the access request message
- [ ] Update profile (name, phone, education, expertise, bio)
- [ ] Upload profile photo
- [ ] Verify profile photo displays correctly

### Student Flow
- [ ] Navigate to `/student_login`
- [ ] Click "Continue with Google"
- [ ] Complete Google OAuth flow
- [ ] Check redirect to `/dashboard`
- [ ] Update profile (name, phone)
- [ ] Upload profile photo
- [ ] Verify profile photo displays correctly

### Session Management
- [ ] Login as trainer, check `$_SESSION['user_role']` === 'trainer'
- [ ] Login as student, check `$_SESSION['user_role']` === 'student'
- [ ] Verify auth guards redirect correctly (is_trainer(), is_logged_in())
- [ ] Test logout works for both roles
- [ ] Test "Forgot Password" flow (if applicable)

### Registration and Access Rules
- [ ] Verify `/register` redirects to `/student_login`
- [ ] Verify student signup/login works only through Google
- [ ] Verify trainer access messaging points users to `/contact`

### GitHub Removal
- [ ] Verify GitHub button not visible on any login page
- [ ] Verify GitHub OAuth config not referenced in SocialAuth.php
- [ ] Check GitHub environment vars not needed

### Email Delivery (OTP)
- [ ] Verify OTP emails are received within 30 seconds
- [ ] Test OTP expires after 10 minutes
- [ ] Test "Resend OTP" button works
- [ ] Verify OTP code format (6 digits)

---

## Security Considerations

### Best Practices Implemented
1. **OTP Security**:
   - Random 6-digit codes
   - 10-minute expiration
   - Single-use tokens
   - Stored in database (not session)

2. **Password Security**:
   - PASSWORD_DEFAULT hashing (Bcrypt)
   - Never logged or displayed
   - Verified before OTP generation

3. **OAuth Security**:
   - CSRF protection via state parameter
   - JWT token verification
   - Gmail ID stored separately

4. **Session Security**:
   - Session regeneration after login
   - HTTPS enforced (configure via .env)
   - Secure cookies should be enabled

### Recommendations
- [ ] Set `session.secure = 1` in php.ini (HTTPS only)
- [ ] Set `session.httponly = 1` in php.ini (JavaScript can't access)
- [ ] Implement rate limiting on OTP endpoints
- [ ] Monitor login attempts for suspicious activity
- [ ] Add 2FA option for students in future

---

## Troubleshooting

### OTP Email Not Received
- **Check**: SMTP credentials in `.env`
- **Check**: $MAIL_FROM address is valid
- **Check**: PHPMailer is installed: `composer show phpmailer/phpmailer`
- **Check**: Server supports SMTP (usually enabled on HostMyIdea)
- **Log**: Check error logs in `/var/log/mail.log`

### Gmail OAuth Not Working
- **Check**: GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET are set
- **Check**: Redirect URI in Google Console matches `/social-callback/google`
- **Check**: Google account has "Less secure app access" enabled (if needed)
- **Check**: Browser console for JavaScript errors

### Profile Photo Upload Fails
- **Check**: `assets/images/profiles/` directory exists and is writable
- **Check**: File size < 5MB
- **Check**: File type is JPG, PNG, GIF, or WebP
- **Check**: Disk space is available

### Users Redirected Unexpectedly
- **Check**: Role detection via `is_trainer()` function
- **Check**: Session data is properly set after login
- **Check**: Redirect guards in dashboard pages

---

## Rollback Plan

If you need to revert authentication changes:

1. **Database**: Create backup before migration
```bash
mysqldump -u thecodin -p database_name > backup_before_auth_refactor.sql
```

2. **Code**: Previous version available in git
```bash
git log --oneline | grep -i auth
git revert <commit-hash>  # or git checkout <previous-tag>
```

3. **Environment**: Keep backup of old `.env`

---

## Future Enhancements

1. **Trainer Features**:
   - 2FA with authenticator app
   - Login activity log
   - Device management

2. **Student Features**:
   - Google Meet integration
   - Calendar sync
   - Progress tracking

3. **Admin Features**:
   - Trainer approval dashboard
   - User management
   - Analytics

---

## Support & Documentation

- **Trainer Login**: `/trainer_login` - Email + OTP
- **Student Login**: `/student_login` - Gmail Only
- **Profile Management**: `/profile` - Works for both roles
- **API Docs**: See inline comments in `/api/*.php`

---

**Last Updated**: 2026-03-31  
**Version**: 1.0  
**Status**: Ready for Deployment
