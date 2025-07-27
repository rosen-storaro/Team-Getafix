# Installation Guide - School Inventory Management System

This comprehensive guide will walk you through the complete installation process for the School Inventory Management System.

## 📋 Pre-Installation Checklist

### System Requirements Verification

Before beginning the installation, ensure your system meets the following requirements:

**Operating System:**
- Linux (Ubuntu 20.04+ recommended)
- Windows Server 2019+ with IIS
- macOS 10.15+ (for development)

**Web Server:**
- Apache 2.4+ (recommended)
- Nginx 1.18+
- IIS 10+ (Windows)

**PHP Requirements:**
- PHP 8.0 or higher (PHP 8.4 recommended)
- Memory limit: 256MB minimum, 512MB recommended
- Max execution time: 300 seconds
- File upload limit: 10MB minimum

**Database:**
- MySQL 8.0+ or MariaDB 10.5+
- Storage: 1GB minimum, 5GB recommended
- Character set: utf8mb4 (for full Unicode support)

**Hardware:**
- RAM: 1GB minimum, 2GB recommended
- Storage: 2GB free space minimum
- CPU: 1 core minimum, 2+ cores recommended

### Required PHP Extensions

Verify the following PHP extensions are installed:

```bash
# Check PHP version
php --version

# Check installed extensions
php -m | grep -E "(pdo|mysql|gd|curl|json|mbstring|openssl|zip)"
```

Required extensions:
- `pdo` - Database abstraction layer
- `pdo_mysql` - MySQL database driver
- `gd` - Image processing for photo uploads
- `curl` - HTTP client for external API calls
- `json` - JSON data handling
- `mbstring` - Multi-byte string handling
- `openssl` - Encryption and security
- `zip` - Archive handling for exports

### Installing Missing Extensions

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install php8.4-pdo php8.4-mysql php8.4-gd php8.4-curl php8.4-json php8.4-mbstring php8.4-zip
```

**CentOS/RHEL:**
```bash
sudo yum install php-pdo php-mysql php-gd php-curl php-json php-mbstring php-zip
```

**Windows:**
Edit `php.ini` and uncomment the required extensions:
```ini
extension=pdo
extension=pdo_mysql
extension=gd
extension=curl
extension=mbstring
extension=openssl
extension=zip
```

## 🚀 Installation Methods

### Method 1: Automated Installation (Recommended)

The automated installation script handles all setup tasks including dependency installation, database configuration, and security hardening.

#### Step 1: Download and Extract
```bash
# Navigate to your web directory
cd /var/www/html

# Extract the project files
# (Assuming you have the project archive)
unzip school-inventory-system.zip
cd school-inventory-system
```

#### Step 2: Run Deployment Script
```bash
# Make the script executable
chmod +x deploy.sh

# Run the automated deployment
./deploy.sh
```

The script will:
- ✅ Verify system requirements
- ✅ Install Composer dependencies
- ✅ Create database schemas
- ✅ Set proper file permissions
- ✅ Create default admin user
- ✅ Configure security settings
- ✅ Generate deployment report

#### Step 3: Web Server Configuration

**Apache Configuration:**
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/school-inventory-system/public
    
    <Directory /var/www/html/school-inventory-system/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/school-inventory-system/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Security headers
    add_header X-Frame-Options "DENY";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
}
```

#### Step 4: SSL Configuration (Production)
```bash
# Install Certbot for Let's Encrypt
sudo apt install certbot python3-certbot-apache

# Generate SSL certificate
sudo certbot --apache -d your-domain.com
```

### Method 2: Manual Installation

For users who prefer manual control over the installation process.

#### Step 1: System Preparation

**Install PHP and Extensions:**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.4 php8.4-cli php8.4-fpm php8.4-mysql php8.4-gd php8.4-curl php8.4-json php8.4-mbstring php8.4-zip apache2 mysql-server

# Start services
sudo systemctl start apache2
sudo systemctl start mysql
sudo systemctl enable apache2
sudo systemctl enable mysql
```

**Install Composer:**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

#### Step 2: Database Setup

**Create MySQL User and Databases:**
```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create user
CREATE USER 'inventory_user'@'localhost' IDENTIFIED BY 'your_secure_password';

-- Create databases
CREATE DATABASE auth_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE reports_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant permissions
GRANT ALL PRIVILEGES ON auth_db.* TO 'inventory_user'@'localhost';
GRANT ALL PRIVILEGES ON inventory_db.* TO 'inventory_user'@'localhost';
GRANT ALL PRIVILEGES ON reports_db.* TO 'inventory_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;
EXIT;
```

**Import Database Schema:**
```bash
mysql -u inventory_user -p < schema.sql
```

#### Step 3: Project Configuration

**Create Environment File:**
```bash
cp .env.example .env
nano .env
```

**Configure .env file:**
```env
# Database Configuration
DB_HOST=localhost
DB_USER=inventory_user
DB_PASS=your_secure_password

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Security
ENCRYPTION_KEY=your_32_character_encryption_key_here
SESSION_LIFETIME=3600

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM_NAME=School Inventory System
```

**Install Dependencies:**
```bash
composer install --no-dev --optimize-autoloader
```

**Set File Permissions:**
```bash
# Set proper permissions
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type f -name "*.html" -exec chmod 644 {} \;
find . -type f -name "*.css" -exec chmod 644 {} \;
find . -type f -name "*.js" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Storage directory needs write permissions
chmod 775 storage/ -R

# Secure sensitive files
chmod 600 .env
```

**Create Required Directories:**
```bash
mkdir -p storage/{logs,uploads,exports,cache,sessions}
mkdir -p public/errors
```

#### Step 4: Create Default Admin User

```sql
-- Connect to auth database
mysql -u inventory_user -p auth_db

-- Insert default superadmin user
INSERT INTO users (username, email, password_hash, role, status, created_at) 
VALUES (
    'superadmin', 
    'admin@school.edu', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Super-admin', 
    'Active', 
    NOW()
);
```

## 🔧 Post-Installation Configuration

### Security Hardening

**1. Change Default Passwords:**
- Login with `superadmin` / `pass123!@#`
- Immediately change the password to a strong, unique password
- Create additional admin accounts as needed

**2. Configure Email Notifications:**
```env
# Gmail configuration example
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-school-email@gmail.com
SMTP_PASS=your-app-specific-password
SMTP_ENCRYPTION=tls
```

**3. Set Up SSL Certificate:**
```bash
# For production, always use HTTPS
sudo certbot --apache -d your-domain.com
```

**4. Configure Firewall:**
```bash
# Ubuntu UFW example
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

### Application Configuration

**1. Add Categories and Locations:**
- Login as superadmin
- Navigate to Admin → Categories
- Add relevant categories (Computers, Audio/Visual, etc.)
- Navigate to Admin → Locations
- Add school locations (Library, Computer Lab, etc.)

**2. Configure System Settings:**
- Set low stock thresholds
- Configure notification preferences
- Set up backup schedules

**3. Create User Accounts:**
- Add admin accounts for staff
- Create user accounts for teachers/students
- Configure role-based permissions

### Performance Optimization

**1. Enable PHP OPcache:**
```ini
# Add to php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
```

**2. Configure MySQL:**
```ini
# Add to my.cnf
[mysqld]
innodb_buffer_pool_size=256M
query_cache_type=1
query_cache_size=64M
```

**3. Enable Compression:**
The `.htaccess` file includes gzip compression configuration. Ensure `mod_deflate` is enabled:
```bash
sudo a2enmod deflate
sudo systemctl restart apache2
```

## 🧪 Testing Installation

### Automated Testing
```bash
# Run the built-in tests
php tests/installation-test.php
```

### Manual Testing Checklist

**1. Basic Functionality:**
- [ ] Can access the application homepage
- [ ] Can login with superadmin account
- [ ] Can change password successfully
- [ ] Can access dashboard

**2. Inventory Management:**
- [ ] Can add new categories
- [ ] Can add new locations
- [ ] Can add inventory items
- [ ] Can upload item photos
- [ ] Can search and filter items

**3. Request Workflow:**
- [ ] Can create new requests
- [ ] Can approve/decline requests
- [ ] Can process returns
- [ ] Can view request history

**4. Reports and Analytics:**
- [ ] Can view dashboard statistics
- [ ] Can generate CSV exports
- [ ] Can view analytics charts

**5. Security Features:**
- [ ] CSRF protection working
- [ ] File upload restrictions active
- [ ] Security headers present
- [ ] Session security configured

### Performance Testing
```bash
# Test database connection
mysql -u inventory_user -p -e "SELECT 1;"

# Test PHP configuration
php -i | grep -E "(memory_limit|max_execution_time|upload_max_filesize)"

# Test web server response
curl -I http://your-domain.com/
```

## 🔍 Troubleshooting

### Common Issues and Solutions

**1. Database Connection Error**
```
Error: SQLSTATE[HY000] [1045] Access denied for user
```
**Solution:**
- Verify database credentials in `.env`
- Check MySQL user permissions
- Ensure MySQL service is running

**2. Permission Denied Errors**
```
Error: Permission denied writing to storage directory
```
**Solution:**
```bash
sudo chown -R www-data:www-data storage/
chmod 775 storage/ -R
```

**3. PHP Extension Missing**
```
Error: Class 'PDO' not found
```
**Solution:**
```bash
sudo apt install php8.4-pdo php8.4-mysql
sudo systemctl restart apache2
```

**4. .htaccess Not Working**
```
Error: 404 Not Found for all routes
```
**Solution:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**5. Email Notifications Not Working**
```
Error: SMTP connection failed
```
**Solution:**
- Verify SMTP settings in `.env`
- Check firewall rules for SMTP ports
- Use app-specific passwords for Gmail

### Log Files

Check these log files for detailed error information:

**Application Logs:**
- `storage/logs/security.log` - Security events
- `storage/logs/application.log` - Application errors

**System Logs:**
- `/var/log/apache2/error.log` - Apache errors
- `/var/log/mysql/error.log` - MySQL errors
- `/var/log/php8.4-fpm.log` - PHP-FPM errors

### Debug Mode

For troubleshooting, temporarily enable debug mode:
```env
# In .env file
APP_DEBUG=true
```

**⚠️ Important:** Always disable debug mode in production!

## 📊 Monitoring and Maintenance

### Regular Maintenance Tasks

**Daily:**
- Monitor system logs for errors
- Check disk space usage
- Verify backup completion

**Weekly:**
- Review security logs
- Update system packages
- Check database performance

**Monthly:**
- Review user accounts and permissions
- Analyze usage statistics
- Plan capacity upgrades

### Backup Strategy

**Database Backup:**
```bash
# Create backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u inventory_user -p auth_db inventory_db reports_db > backup_$DATE.sql
gzip backup_$DATE.sql
```

**File Backup:**
```bash
# Backup uploaded files and configuration
tar -czf files_backup_$DATE.tar.gz storage/ .env
```

### Monitoring Setup

**Log Rotation:**
```bash
# Add to /etc/logrotate.d/school-inventory
/var/www/html/school-inventory-system/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    notifempty
    create 644 www-data www-data
}
```

**Health Check Script:**
```bash
#!/bin/bash
# health-check.sh
curl -f http://your-domain.com/api/health || echo "Application down!"
```

## 🎯 Next Steps

After successful installation:

1. **Security Review:**
   - Change all default passwords
   - Review user permissions
   - Configure SSL certificate
   - Set up firewall rules

2. **Data Migration:**
   - Import existing inventory data
   - Create user accounts
   - Set up categories and locations

3. **Training:**
   - Train administrators on system usage
   - Create user documentation
   - Set up support procedures

4. **Go Live:**
   - Announce system availability
   - Monitor initial usage
   - Gather user feedback

## 📞 Support

For installation support:

1. **Check Documentation:**
   - Review this installation guide
   - Check the main README.md
   - Review API documentation

2. **Log Analysis:**
   - Check application logs in `storage/logs/`
   - Review web server error logs
   - Examine database logs

3. **Community Resources:**
   - PHP documentation: https://php.net/docs
   - MySQL documentation: https://dev.mysql.com/doc/
   - Bootstrap documentation: https://getbootstrap.com/docs/

---

**🎉 Installation Complete!**

Your School Inventory Management System is now ready for use. Remember to:
- Change the default superadmin password
- Configure email notifications
- Set up regular backups
- Monitor system performance

For user guidance, see `USER_MANUAL.md`  
For API details, see `API.md`

