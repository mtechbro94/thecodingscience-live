#!/bin/bash
# post_deploy.sh - Post-Deployment Script
# This script runs on the server after deployment via GitHub Actions
# It handles: database migrations, cache clearing, permissions, health checks

set -e  # Exit on error

echo "=================================="
echo "🚀 Post-Deployment Script Started"
echo "=================================="
echo "Date: $(date)"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
DEPLOY_PATH="/home/thecodin/public_html"
DB_HOST="${DB_HOST:-localhost}"
DB_USER="${DB_USER:-thecodin}"
DB_NAME="${DB_NAME:-thecodin_db}"

# Check if we're in the right directory
if [ ! -f "$DEPLOY_PATH/config.php" ]; then
    echo -e "${RED}❌ Error: config.php not found in $DEPLOY_PATH${NC}"
    exit 1
fi

cd "$DEPLOY_PATH"

# ============================================
# Step 1: Verify .env file exists
# ============================================
echo ""
echo -e "${YELLOW}Step 1/5: Verifying configuration...${NC}"

if [ ! -f ".env" ]; then
    echo -e "${RED}❌ Error: .env file not found!${NC}"
    echo "   Please create .env file with database and mail configuration"
    exit 1
fi

echo -e "${GREEN}✅ .env file found${NC}"

# ============================================
# Step 2: Set file permissions
# ============================================
echo ""
echo -e "${YELLOW}Step 2/5: Setting file permissions...${NC}"

# Make directories writable
chmod 755 assets/images/profiles/ 2>/dev/null || mkdir -p assets/images/profiles/ && chmod 755 assets/images/profiles/
chmod 755 storage/ 2>/dev/null || mkdir -p storage && chmod 755 storage/
chmod 755 logs/ 2>/dev/null || mkdir -p logs && chmod 755 logs/

# Ensure config files are readable
chmod 644 config.php
chmod 644 .env 2>/dev/null || true

echo -e "${GREEN}✅ File permissions set${NC}"

# ============================================
# Step 3: Run Database Migration
# ============================================
echo ""
echo -e "${YELLOW}Step 3/5: Running database migration...${NC}"

# Check if migration file exists
if [ ! -f "database/migrate_auth_refactor.sql" ]; then
    echo -e "${YELLOW}⚠️  Migration file not found (this is OK if already migrated)${NC}"
else
    # Check if migration already applied by checking for gmail_id column
    COLUMN_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='gmail_id';" 2>/dev/null || echo "0")
    
    if [ "$COLUMN_EXISTS" -eq 0 ]; then
        echo "Running migration..."
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/migrate_auth_refactor.sql 2>/dev/null || {
            echo -e "${RED}❌ Database migration failed${NC}"
            echo "   Make sure your database credentials are correct in .env"
            exit 1
        }
        echo -e "${GREEN}✅ Database migration completed${NC}"
    else
        echo -e "${GREEN}✅ Migration already applied${NC}"
    fi
fi

# ============================================
# Step 4: Clear old sessions and cache
# ============================================
echo ""
echo -e "${YELLOW}Step 4/5: Clearing cache and old sessions...${NC}"

# Clear PHP sessions (if accessible)
if [ -d "/tmp/php_sessions" ]; then
    find /tmp/php_sessions -name "sess_*" -mtime +7 -delete 2>/dev/null || true
fi

# Clear application cache if any
if [ -d "$DEPLOY_PATH/storage/cache" ]; then
    rm -rf $DEPLOY_PATH/storage/cache/* 2>/dev/null || true
fi

echo -e "${GREEN}✅ Cache and sessions cleared${NC}"

# ============================================
# Step 5: Health Checks
# ============================================
echo ""
echo -e "${YELLOW}Step 5/5: Running health checks...${NC}"

HEALTH_CHECK_PASSED=true

# Check 1: PHP syntax validation
echo "  • Checking PHP files..."
for php_file in api/*.php includes/*.php views/*.php; do
    if [ -f "$php_file" ]; then
        php -l "$php_file" > /dev/null 2>&1 || {
            echo -e "    ${RED}❌ Syntax error in $php_file${NC}"
            HEALTH_CHECK_PASSED=false
        }
    fi
done
if [ "$HEALTH_CHECK_PASSED" = true ]; then
    echo -e "    ${GREEN}✓ All PHP files have valid syntax${NC}"
fi

# Check 2: Required files exist
echo "  • Checking required files..."
REQUIRED_FILES=(
    "api/trainer_auth.php"
    "api/student_auth.php"
    "includes/mail.php"
    "views/trainer_login.php"
    "views/student_login.php"
    "views/profile.php"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo -e "    ${RED}❌ Missing required file: $file${NC}"
        HEALTH_CHECK_PASSED=false
    fi
done
if [ "$HEALTH_CHECK_PASSED" = true ]; then
    echo -e "    ${GREEN}✓ All required files present${NC}"
fi

# Check 3: Database connectivity
echo "  • Checking database..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT 1;" > /dev/null 2>&1 || {
    echo -e "    ${RED}❌ Database connection failed${NC}"
    HEALTH_CHECK_PASSED=false
}
if [ "$HEALTH_CHECK_PASSED" = true ]; then
    echo -e "    ${GREEN}✓ Database connection successful${NC}"
fi

# Check 4: New auth tables exist
echo "  • Checking new database tables..."
TABLES_OK=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='$DB_NAME' AND TABLE_NAME IN ('users', 'otp_tokens');" 2>/dev/null || echo "0")
if [ "$TABLES_OK" -eq 2 ]; then
    echo -e "    ${GREEN}✓ Auth tables exist${NC}"
else
    echo -e "    ${YELLOW}⚠️  Some tables may not exist yet${NC}"
fi

# ============================================
# Final Result
# ============================================
echo ""
echo "=================================="
if [ "$HEALTH_CHECK_PASSED" = true ]; then
    echo -e "${GREEN}✅ Deployment Completed Successfully!${NC}"
    echo "=================================="
    echo ""
    echo "📝 Next Steps:"
    echo "  1. Visit https://thecodingscience.com to verify site is up"
    echo "  2. Test trainer login: https://thecodingscience.com/trainer_login"
    echo "  3. Test student login: https://thecodingscience.com/student_login"
    echo "  4. Check admin panel for any errors"
    echo ""
    echo "🔗 Important URLs:"
    echo "  • Trainer Login: /trainer_login"
    echo "  • Student Login: /student_login"
    echo "  • Profile Management: /profile"
    echo ""
    exit 0
else
    echo -e "${RED}⚠️  Deployment Completed with Warnings${NC}"
    echo "=================================="
    echo "Please check the errors above and verify deployment manually."
    exit 1
fi
