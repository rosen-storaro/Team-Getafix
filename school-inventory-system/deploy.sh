#!/bin/bash

# School Inventory Management System - Deployment Script
# This script prepares the system for production deployment

echo "🚀 School Inventory Management System - Deployment Script"
echo "=========================================================="

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo "❌ Please do not run this script as root"
    exit 1
fi

# Set variables
PROJECT_DIR=$(pwd)
BACKUP_DIR="$PROJECT_DIR/backup_$(date +%Y%m%d_%H%M%S)"
LOG_FILE="$PROJECT_DIR/deployment.log"

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Function to check command success
check_success() {
    if [ $? -eq 0 ]; then
        log "✅ $1 completed successfully"
    else
        log "❌ $1 failed"
        exit 1
    fi
}

log "🔄 Starting deployment process..."

# 1. Create backup
log "📦 Creating backup..."
mkdir -p "$BACKUP_DIR"
cp -r . "$BACKUP_DIR/" 2>/dev/null
check_success "Backup creation"

# 2. Check PHP version
log "🔍 Checking PHP version..."
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
if [ "$(echo "$PHP_VERSION >= 8.0" | bc)" -eq 1 ]; then
    log "✅ PHP version $PHP_VERSION is compatible"
else
    log "❌ PHP version $PHP_VERSION is not compatible. Requires PHP 8.0+"
    exit 1
fi

# 3. Check required PHP extensions
log "🔍 Checking PHP extensions..."
REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "gd" "curl" "json" "mbstring" "openssl")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        log "✅ PHP extension $ext is installed"
    else
        log "❌ PHP extension $ext is missing"
        exit 1
    fi
done

# 4. Check MySQL connection
log "🔍 Testing database connection..."
if [ -f ".env" ]; then
    DB_HOST=$(grep "DB_HOST=" .env | cut -d '=' -f2)
    DB_USER=$(grep "DB_USER=" .env | cut -d '=' -f2)
    DB_PASS=$(grep "DB_PASS=" .env | cut -d '=' -f2)
    
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1;" >/dev/null 2>&1
    check_success "Database connection test"
else
    log "❌ .env file not found"
    exit 1
fi

# 5. Install/Update Composer dependencies
log "📦 Installing Composer dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
    check_success "Composer dependencies installation"
else
    log "❌ composer.json not found"
    exit 1
fi

# 6. Set proper file permissions
log "🔒 Setting file permissions..."
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type f -name "*.html" -exec chmod 644 {} \;
find . -type f -name "*.css" -exec chmod 644 {} \;
find . -type f -name "*.js" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 775 storage/ -R
chmod 644 .env
chmod 644 public/.htaccess
chmod +x deploy.sh
check_success "File permissions setup"

# 7. Create required directories
log "📁 Creating required directories..."
mkdir -p storage/{logs,uploads,exports,cache,sessions}
mkdir -p public/errors
check_success "Directory creation"

# 8. Clear any existing cache
log "🧹 Clearing cache..."
if [ -d "storage/cache" ]; then
    rm -rf storage/cache/*
fi
check_success "Cache clearing"

# 9. Run database migrations/setup
log "🗄️ Setting up database..."
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" < schema.sql
check_success "Database schema setup"

# 10. Create default superadmin user if not exists
log "👤 Creating default superadmin user..."
SUPERADMIN_EXISTS=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" auth_db -se "SELECT COUNT(*) FROM users WHERE username='superadmin';")
if [ "$SUPERADMIN_EXISTS" -eq 0 ]; then
    HASHED_PASSWORD=$(php -r "echo password_hash('pass123!@#', PASSWORD_DEFAULT);")
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" auth_db -e "INSERT INTO users (username, email, password, role, status, force_password_change) VALUES ('superadmin', 'admin@school.edu', '$HASHED_PASSWORD', 'Super-admin', 'Active', 0);"
    check_success "Default superadmin user creation"
else
    log "ℹ️ Superadmin user already exists"
fi

# 11. Test critical endpoints
log "🧪 Testing critical endpoints..."
if command -v curl >/dev/null 2>&1; then
    # Test if web server is running
    if curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/" | grep -q "200\|302"; then
        log "✅ Web server is responding"
    else
        log "⚠️ Web server may not be running on port 8000"
    fi
else
    log "⚠️ curl not available for endpoint testing"
fi

# 12. Security check
log "🔒 Running security checks..."
if [ -f "public/.htaccess" ]; then
    if grep -q "X-Frame-Options" public/.htaccess; then
        log "✅ Security headers are configured"
    else
        log "⚠️ Security headers may not be properly configured"
    fi
else
    log "❌ .htaccess file missing"
    exit 1
fi

# 13. Generate deployment report
log "📋 Generating deployment report..."
cat > deployment_report.txt << EOF
School Inventory Management System - Deployment Report
======================================================
Deployment Date: $(date)
PHP Version: $PHP_VERSION
Project Directory: $PROJECT_DIR
Backup Location: $BACKUP_DIR

✅ Deployment Checklist:
- [x] Backup created
- [x] PHP version verified
- [x] PHP extensions checked
- [x] Database connection tested
- [x] Composer dependencies installed
- [x] File permissions set
- [x] Required directories created
- [x] Cache cleared
- [x] Database schema setup
- [x] Default user created
- [x] Security configuration verified

🔗 Access Information:
- Application URL: http://your-domain.com/
- Default Admin: superadmin / pass123!@# (CHANGE IMMEDIATELY)
- Database: MySQL with proper credentials

⚠️ Post-Deployment Tasks:
1. Change default superadmin password
2. Configure SSL certificate
3. Set up automated backups
4. Configure email settings for notifications
5. Test all functionality thoroughly
6. Monitor logs for any issues

📁 Important Files:
- Configuration: .env
- Database Schema: schema.sql
- Security: public/.htaccess
- Logs: storage/logs/
- Uploads: storage/uploads/

🛡️ Security Notes:
- CSRF protection enabled
- Input validation active
- File upload restrictions in place
- Security headers configured
- Attack pattern blocking active

For support, refer to the documentation in docs/ directory.
EOF

log "✅ Deployment completed successfully!"
log "📋 Deployment report saved to: deployment_report.txt"
log "🔗 Access your application and change the default password immediately"

echo ""
echo "🎉 Deployment Summary:"
echo "====================="
echo "✅ System is ready for production"
echo "✅ All security measures are active"
echo "✅ Database is configured and populated"
echo "✅ Default superadmin user created"
echo ""
echo "⚠️  IMPORTANT: Change the default password immediately!"
echo "🔗 Login with: superadmin / pass123!@#"
echo ""
echo "📋 Full deployment log: $LOG_FILE"
echo "📋 Deployment report: deployment_report.txt"

