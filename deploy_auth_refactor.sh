#!/bin/bash
# Quick Deployment Script for Authentication System Refactor
# Usage: bash deploy_auth_refactor.sh

set -e

echo "🔐 Authentication System Refactor - Deployment Script"
echo "=================================================="

# Step 1: Database Migration
echo ""
echo "Step 1/4: Running database migration..."
read -p "Enter database host (default: localhost): " db_host
read -p "Enter database user (default: thecodin): " db_user
read -p "Enter database name: " db_name

db_host=${db_host:-localhost}
db_user=${db_user:-thecodin}

mysql -h "$db_host" -u "$db_user" -p "$db_name" < database/migrate_auth_refactor.sql
echo "✅ Database migration completed"

# Step 2: Composer Dependencies
echo ""
echo "Step 2/4: Installing PHP dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
    echo "✅ Dependencies installed"
else
    echo "⚠️  composer.json not found. Skipping..."
    read -p "Do you want to install PHPMailer? (y/n): " install_phpmailer
    if [ "$install_phpmailer" = "y" ]; then
        composer require phpmailer/phpmailer
    fi
fi

# Step 3: File Permissions
echo ""
echo "Step 3/4: Setting file permissions..."
chmod 755 assets/images/profiles/ 2>/dev/null || mkdir -p assets/images/profiles/ && chmod 755 assets/images/profiles/
echo "✅ Permissions set correctly"

# Step 4: Environment Variables
echo ""
echo "Step 4/4: Verifying environment variables..."
if [ -f ".env" ]; then
    if grep -q "GOOGLE_CLIENT_ID" .env; then
        echo "✅ .env file found with Google credentials"
    else
        echo "⚠️  Missing Google OAuth credentials in .env"
        echo "   Required variables:"
        echo "   - GOOGLE_CLIENT_ID"
        echo "   - GOOGLE_CLIENT_SECRET"
    fi
    
    if grep -q "SMTP_HOST" .env; then
        echo "✅ .env file found with SMTP configuration"
    else
        echo "⚠️  Missing SMTP configuration in .env"
        echo "   Required variables:"
        echo "   - SMTP_HOST"
        echo "   - SMTP_USER"
        echo "   - SMTP_PASS"
    fi
else
    echo "❌ .env file not found!"
    exit 1
fi

echo ""
echo "=================================================="
echo "✅ Authentication System Refactor Deployed!"
echo "=================================================="
echo ""
echo "📝 Next Steps:"
echo "1. Test trainer login: https://yourdomain.com/trainer_login"
echo "2. Test student login: https://yourdomain.com/student_login"
echo "3. Check email delivery for OTP codes"
echo "4. Test profile management for both roles"
echo ""
echo "📚 Documentation: See AUTHENTICATION_REFACTOR_GUIDE.md"
echo ""
