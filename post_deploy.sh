#!/bin/bash
# post_deploy.sh - Post-Deployment Script (Flask/Gunicorn)
# This script runs on the server after deployment via GitHub Actions
# It handles: venv setup, dependency install, database migrations, permissions, service restart

set -e  # Exit on error

echo "=================================="
echo "Post-Deployment Script Started"
echo "=================================="
echo "Date: $(date)"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
DEPLOY_PATH="${DEPLOY_PATH:-/home/thecodin/public_html}"
APP_DIR="$(pwd)"
VENV_DIR="$APP_DIR/venv"

# Optional override via environment (set by GitHub Actions)
if [ -n "${DEPLOYMENT_PATH}" ]; then
  DEPLOY_PATH="${DEPLOYMENT_PATH}"
fi

DB_HOST="${DB_HOST:-localhost}"
DB_USER="${DB_USER:-thecodin}"
DB_NAME="${DB_NAME:-thecodin_db}"
DB_PASS="${DB_PASS:-}"

# Load .env values (fallback) if not set
function env_get() {
  local key="$1"
  local value
  value=$(grep -E "^$key=" .env 2>/dev/null | tail -n1 | cut -d'=' -f2-)
  if [[ ($value == \"*\" || $value == \'*\' ) ]]; then
    value="${value:1:-1}"
  fi
  echo "$value"
}

if [ -f ".env" ]; then
  DB_HOST="${DB_HOST:-$(env_get DB_HOST)}"
  DB_USER="${DB_USER:-$(env_get DB_USER)}"
  DB_NAME="${DB_NAME:-$(env_get DB_NAME)}"
  DB_PASS="${DB_PASS:-$(env_get DB_PASS)}"
  if [ -z "$DB_PASS" ]; then
    DB_PASS="${DB_PASS:-$(env_get PROD_DB_PASS)}"
  fi
fi

if [ -n "$DB_PASS" ]; then
  export MYSQL_PWD="$DB_PASS"
fi

if [ -z "$DB_PASS" ]; then
  echo -e "${YELLOW}??  Warning: DB_PASS is empty. Check .env file.${NC}"
else
  echo -e "${GREEN}? DB_PASS loaded successfully.${NC}"
fi

# ============================================
# Step 1: Verify .env file exists
# ============================================
echo ""
echo -e "${YELLOW}Step 1/6: Verifying configuration...${NC}"

cd "$APP_DIR"

if [ ! -f ".env" ]; then
    echo -e "${RED}? Error: .env file not found in $(pwd)!${NC}"
    echo "   Please create .env file with database and mail configuration"
    exit 1
fi

echo -e "${GREEN}? .env file found${NC}"

G_CLIENT_ID=$(env_get GOOGLE_CLIENT_ID)
if [ -z "$G_CLIENT_ID" ]; then
    echo -e "${YELLOW}??  Warning: GOOGLE_CLIENT_ID is missing from .env. Gmail Login will not work!${NC}"
else
    echo -e "${GREEN}? GOOGLE_CLIENT_ID configured.${NC}"
fi

# ============================================
# Step 2: Set file permissions
# ============================================
echo ""
echo -e "${YELLOW}Step 2/6: Setting file permissions...${NC}"

chmod 755 assets/images/profiles/ 2>/dev/null || mkdir -p assets/images/profiles/ && chmod 755 assets/images/profiles/
chmod 755 assets/uploads/resources/ 2>/dev/null || mkdir -p assets/uploads/resources/ && chmod 755 assets/uploads/resources/
chmod 644 .env 2>/dev/null || true

echo -e "${GREEN}? File permissions set${NC}"

# ============================================
# Step 3: Python venv + dependencies
# ============================================
echo ""
echo -e "${YELLOW}Step 3/6: Setting up Python environment...${NC}"

if ! command -v python3 >/dev/null 2>&1; then
    echo -e "${RED}? Error: python3 not found. Install Python 3.11+ first.${NC}"
    exit 1
fi

if [ ! -d "$VENV_DIR" ]; then
    python3 -m venv "$VENV_DIR"
    echo -e "${GREEN}? Virtual environment created${NC}"
else
    echo -e "${GREEN}? Virtual environment already exists${NC}"
fi

"$VENV_DIR/bin/pip" install --upgrade pip --quiet
"$VENV_DIR/bin/pip" install -r requirements.txt --quiet
echo -e "${GREEN}? Dependencies installed${NC}"

# ============================================
# Step 4: Run Database Migration
# ============================================
echo ""
echo -e "${YELLOW}Step 4/6: Running database migration...${NC}"

echo "  Attempting DB connection with:"
echo "    Host: $DB_HOST"
echo "    User: $DB_USER"
echo "    Name: $DB_NAME"
echo "    Password: (passed via env)"

column_exists() {
    local table_name="$1"
    local column_name="$2"
    mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" -N -B -e "
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = '$DB_NAME'
          AND TABLE_NAME = '$table_name'
          AND COLUMN_NAME = '$column_name';
    " 2>/dev/null | tr -d '[:space:]'
}

index_exists() {
    local table_name="$1"
    local index_name="$2"
    mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" -N -B -e "
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = '$DB_NAME'
          AND TABLE_NAME = '$table_name'
          AND INDEX_NAME = '$index_name';
    " 2>/dev/null | tr -d '[:space:]'
}

table_exists() {
    local table_name="$1"
    mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" -N -B -e "
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = '$DB_NAME'
          AND TABLE_NAME = '$table_name';
    " 2>/dev/null | tr -d '[:space:]'
}

run_sql() {
    local sql="$1"
    mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" -e "$sql" || {
        echo -e "${RED}? Database migration failed${NC}"
        echo "   Failed SQL: $sql"
        exit 1
    }
}

echo "Running incremental auth migration..."

if [ "$(column_exists users username)" = "0" ]; then
    run_sql "ALTER TABLE \`users\` ADD COLUMN \`username\` VARCHAR(100) UNIQUE DEFAULT NULL AFTER \`email\`;"
fi

if [ "$(column_exists users gmail_id)" = "0" ]; then
    run_sql "ALTER TABLE \`users\` ADD COLUMN \`gmail_id\` VARCHAR(255) UNIQUE DEFAULT NULL AFTER \`username\`;"
fi

if [ "$(column_exists users otp_code)" = "0" ]; then
    run_sql "ALTER TABLE \`users\` ADD COLUMN \`otp_code\` VARCHAR(6) DEFAULT NULL AFTER \`gmail_id\`;"
fi

if [ "$(column_exists users otp_expires_at)" = "0" ]; then
    run_sql "ALTER TABLE \`users\` ADD COLUMN \`otp_expires_at\` DATETIME DEFAULT NULL AFTER \`otp_code\`;"
fi

if [ "$(column_exists users otp_verified)" = "0" ]; then
    run_sql "ALTER TABLE \`users\` ADD COLUMN \`otp_verified\` TINYINT(1) DEFAULT 0 AFTER \`otp_expires_at\`;"
fi

if [ "$(column_exists users updated_at)" = "0" ]; then
    run_sql "ALTER TABLE \`users\` ADD COLUMN \`updated_at\` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER \`created_at\`;"
fi

if [ "$(index_exists users idx_username)" = "0" ]; then
    run_sql "CREATE INDEX \`idx_username\` ON \`users\`(\`username\`);"
fi

if [ "$(index_exists users idx_gmail_id)" = "0" ]; then
    run_sql "CREATE INDEX \`idx_gmail_id\` ON \`users\`(\`gmail_id\`);"
fi

if [ "$(index_exists users idx_otp_code)" = "0" ]; then
    run_sql "CREATE INDEX \`idx_otp_code\` ON \`users\`(\`otp_code\`);"
fi

if [ "$(table_exists otp_tokens)" = "0" ]; then
    run_sql "CREATE TABLE \`otp_tokens\` (
      \`id\` INT(11) NOT NULL AUTO_INCREMENT,
      \`user_id\` INT(11) DEFAULT NULL,
      \`email\` VARCHAR(120) NOT NULL,
      \`otp_code\` VARCHAR(6) NOT NULL,
      \`purpose\` ENUM('login', 'registration', 'password_reset') DEFAULT 'login',
      \`created_at\` DATETIME DEFAULT CURRENT_TIMESTAMP,
      \`expires_at\` DATETIME NOT NULL,
      \`used_at\` DATETIME DEFAULT NULL,
      PRIMARY KEY (\`id\`),
      UNIQUE KEY \`email_purpose\` (\`email\`, \`purpose\`),
      KEY \`user_id\` (\`user_id\`),
      KEY \`email\` (\`email\`),
      KEY \`otp_code\` (\`otp_code\`),
      KEY \`expires_at\` (\`expires_at\`),
      CONSTRAINT \`fk_otp_user\` FOREIGN KEY (\`user_id\`) REFERENCES \`users\` (\`id\`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
fi

if [ "$(table_exists success_stories)" = "0" ]; then
    run_sql "CREATE TABLE \`success_stories\` (
      \`id\` INT AUTO_INCREMENT PRIMARY KEY,
      \`name\` VARCHAR(255) NOT NULL,
      \`title\` VARCHAR(255) NOT NULL,
      \`content\` TEXT NOT NULL,
      \`rating\` INT DEFAULT 5,
      \`photo_path\` VARCHAR(255) DEFAULT NULL,
      \`avatar_bg\` VARCHAR(50) DEFAULT 'bg-primary',
      \`is_active\` TINYINT(1) DEFAULT 1,
      \`sort_order\` INT DEFAULT 0,
      \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
fi

if [ "$(column_exists success_stories photo_path)" = "0" ]; then
    run_sql "ALTER TABLE \`success_stories\` ADD COLUMN \`photo_path\` VARCHAR(255) DEFAULT NULL AFTER \`rating\`;"
fi

echo -e "${GREEN}? Database migration completed${NC}"

# ============================================
# Step 5: Health Checks
# ============================================
echo ""
echo -e "${YELLOW}Step 5/6: Running health checks...${NC}"

HEALTH_CHECK_PASSED=true

# Check 1: Python syntax + app boots
echo "  Checking Python app imports..."
"$VENV_DIR/bin/python" -c "from app import create_app; create_app()" 2>/dev/null || {
    echo -e "    ${RED}? App failed to boot${NC}"
    HEALTH_CHECK_PASSED=false
}
if [ "$HEALTH_CHECK_PASSED" = true ]; then
    echo -e "    ${GREEN}V App boots successfully${NC}"
fi

# Check 2: Required files exist
echo "  Checking required files..."
REQUIRED_FILES=(
    "wsgi.py"
    "config.py"
    "app/__init__.py"
    "app/db.py"
    "app/routes/payments.py"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo -e "    ${RED}? Missing required file: $file${NC}"
        HEALTH_CHECK_PASSED=false
    fi
done
if [ "$HEALTH_CHECK_PASSED" = true ]; then
    echo -e "    ${GREEN}V All required files present${NC}"
fi

# Check 3: Database connectivity
echo "  Checking database..."
mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" -N -e "SELECT 1;" || {
    echo -e "    ${RED}? Database connection failed${NC}"
    HEALTH_CHECK_PASSED=false
}
if [ "$HEALTH_CHECK_PASSED" = true ]; then
    echo -e "    ${GREEN}V Database connection successful${NC}"
fi

# ============================================
# Step 6: Restart Gunicorn service
# ============================================
echo ""
echo -e "${YELLOW}Step 6/6: Restarting Gunicorn service...${NC}"

RESTARTED=false

if command -v systemctl >/dev/null 2>&1 && systemctl list-units --type=service 2>/dev/null | grep -q "thecodingscience"; then
    sudo systemctl restart thecodingscience && RESTARTED=true
elif [ -f "/etc/supervisor/conf.d/thecodingscience.conf" ]; then
    sudo supervisorctl restart thecodingscience && RESTARTED=true
elif [ -x "$APP_DIR/restart.sh" ]; then
    "$APP_DIR/restart.sh" && RESTARTED=true
else
    echo -e "    ${YELLOW}?? No systemd/supervisor unit found. Start manually with:${NC}"
    echo "    $VENV_DIR/bin/gunicorn -c gunicorn.conf.py wsgi:app"
fi

if [ "$RESTARTED" = true ]; then
    echo -e "    ${GREEN}V Gunicorn restarted${NC}"
else
    echo -e "    ${YELLOW}?? Gunicorn not auto-restarted. Verify the service manually.${NC}"
fi

# ============================================
# Final Result
# ============================================
echo ""
echo "=================================="
if [ "$HEALTH_CHECK_PASSED" = true ]; then
    echo -e "${GREEN}? Deployment Completed Successfully!${NC}"
    echo "=================================="
    echo ""
    echo "?? Next Steps:"
    echo "  1. Visit https://thecodingscience.com to verify site is up"
    echo "  2. Test trainer login: https://thecodingscience.com/trainer_login"
    echo "  3. Test student login: https://thecodingscience.com/student_login"
    echo "  4. Check admin panel for any errors"
    echo ""
    echo "?? Important URLs:"
    echo "  Trainer Login: /trainer_login"
    echo "  Student Login: /student_login"
    echo "  Profile Management: /profile"
    echo ""
    exit 0
else
    echo -e "${RED}??  Deployment Completed with Warnings${NC}"
    echo "=================================="
    echo "Please check the errors above and verify deployment manually."
    exit 1
fi
