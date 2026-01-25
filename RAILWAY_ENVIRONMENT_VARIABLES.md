# 🔧 Environment Variables Configuration Guide

## Setting Environment Variables on Railway

To ensure your contact information (phone number, email, etc.) displays correctly on the live app, you need to set environment variables on Railway.

### Step 1: Go to Railway Dashboard
1. Visit https://railway.app
2. Login to your account
3. Select your **The Coding Science** project

### Step 2: Access Variables Settings
1. Click on your project
2. Click the **Variables** tab (or Settings → Variables)
3. You'll see a form to add environment variables

### Step 3: Add Required Variables

Add these environment variables with your information:

#### **Contact Information** (Required)
| Variable | Value | Example |
|----------|-------|---------|
| `CONTACT_EMAIL` | Your email address | `academy@thecodingscience.com` |
| `CONTACT_PHONE` | Your phone number | `+917006196821` |
| `CONTACT_LOCATION` | Your location | `Jammu and Kashmir, India` |

#### **Site Configuration** (Optional)
| Variable | Value | Example |
|----------|-------|---------|
| `SITE_NAME` | Your site name | `The Coding Science` |
| `SITE_TAGLINE` | Your tagline | `Learn Technology, Transform Your Future` |

#### **Email Configuration** (Required for emails to work)
| Variable | Value | Example |
|----------|-------|---------|
| `SENDER_EMAIL` | Gmail address | `your-email@gmail.com` |
| `SENDER_PASSWORD` | Gmail app password | (16-char password) |

#### **Payment Configuration** (Required)
| Variable | Value | Example |
|----------|-------|---------|
| `UPI_ID_1` | UPI ID 1 | `yourname@okaxis` |
| `UPI_ID_2` | UPI ID 2 | `yourname@okhdfcbank` |
| `UPI_ID_3` | UPI ID 3 | `yourname@okicici` |
| `UPI_NAME` | Display name | `The Coding Science` |

#### **Social Media Links** (Optional)
| Variable | Value |
|----------|-------|
| `INSTAGRAM_URL` | Your Instagram profile URL |
| `YOUTUBE_URL` | Your YouTube channel URL |
| `FACEBOOK_URL` | Your Facebook page URL |
| `LINKEDIN_URL` | Your LinkedIn company URL |

#### **WhatsApp Group** (Optional)
| Variable | Value |
|----------|-------|
| `WHATSAPP_GROUP_LINK` | Your WhatsApp group invite link |

#### **Security** (Critical!)
| Variable | Value |
|----------|-------|
| `SECRET_KEY` | Generate a random 32+ character string |

---

## 📝 Step-by-Step Example

### Adding CONTACT_PHONE Variable

1. **Click "Add Variable"** button on Railway Variables page
2. **Enter Key:** `CONTACT_PHONE`
3. **Enter Value:** `+917006196821`
4. **Click Add** (or press Enter)

### Generate a Gmail App Password

For email functionality to work:

1. Go to https://myaccount.google.com/security
2. Enable **2-Step Verification** (if not already)
3. Go to **App passwords**
4. Select **Mail** and **Windows Computer**
5. Copy the 16-character password
6. Add as `SENDER_PASSWORD` on Railway

### Generate SECRET_KEY

Run this in Python to generate a secure key:

```python
import secrets
print(secrets.token_urlsafe(32))
```

Copy the output and add as `SECRET_KEY` on Railway.

---

## ✅ After Adding Variables

1. **Railway will automatically restart** the app with new variables
2. **Wait 2-3 minutes** for deployment to complete
3. **Visit your live app** and verify:
   - Phone number shows in footer and contact page
   - Emails are being sent
   - All contact info is correct

---

## 🔍 Verify Variables Are Set

1. Go to Railway project
2. Click **Variables** tab
3. Confirm all variables are visible
4. Check the **Deployments** tab to see if latest deployment is "Success"

---

## 🆘 Troubleshooting

### Phone Number Still Not Showing?
- ✅ Verify `CONTACT_PHONE` is set on Railway
- ✅ Hard refresh browser (Ctrl+Shift+R)
- ✅ Clear browser cache
- ✅ Wait for deployment to complete (green checkmark)

### Emails Not Working?
- ✅ Verify `SENDER_EMAIL` and `SENDER_PASSWORD` are correct
- ✅ Check Gmail app password (not your main password)
- ✅ Enable "Less secure apps" if using regular Gmail password
- ✅ Check Railway logs for SMTP errors

### Variables Not Updating?
- ✅ Check deployment status in Railway dashboard
- ✅ Look for red X marks (errors) next to variables
- ✅ Verify variable names are EXACTLY correct (case-sensitive)
- ✅ Wait for auto-redeploy to complete

---

## 📞 Your Contact Details (To Add on Railway)

```
Email: academy@thecodingscience.com
Phone: +917006196821
Location: Jammu and Kashmir, India
Instagram: https://www.instagram.com/thecodingscience
YouTube: https://youtube.com/@thecodingscience-q7z
Facebook: https://www.facebook.com/share/184mEoARX8/
LinkedIn: https://www.linkedin.com/company/the-coding-science/
WhatsApp: [Your group invite link]
```

---

**Once variables are set, your live app will display all contact information correctly!** ✅

For more details, see [DEPLOYMENT.md](DEPLOYMENT.md)
