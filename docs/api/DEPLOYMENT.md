# Deployment Guide

## Overview

Panduan lengkap untuk deploy BukuBisnis API ke production server dengan performa optimal dan keamanan maksimal.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Setup](#server-setup)
3. [Application Deployment](#application-deployment)
4. [Database Configuration](#database-configuration)
5. [Web Server Configuration](#web-server-configuration)
6. [SSL/HTTPS Setup](#sslhttps-setup)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring Setup](#monitoring-setup)
9. [Backup Strategy](#backup-strategy)
10. [Maintenance](#maintenance)

## Prerequisites

### System Requirements

-   **OS**: Ubuntu 20.04 LTS atau Ubuntu 22.04 LTS
-   **PHP**: 8.3 atau higher
-   **Database**: MySQL 8.0+ atau PostgreSQL 14+
-   **Web Server**: Nginx 1.18+ atau Apache 2.4+
-   **Cache**: Redis 6.0+
-   **Memory**: Minimum 2GB RAM (recommended 4GB+)
-   **Storage**: Minimum 20GB SSD

### Required PHP Extensions

```bash
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

sudo apt install -y \
    php8.3 \
    php8.3-fpm \
    php8.3-mysql \
    php8.3-pgsql \
    php8.3-redis \
    php8.3-curl \
    php8.3-json \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-zip \
    php8.3-gd \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-tokenizer \
    php8.3-fileinfo \
    php8.3-sqlite3
```

## Server Setup

### 1. Create Application User

```bash
# Create dedicated user for the application
sudo adduser --system --group --shell /bin/bash bukubisnis
sudo usermod -aG www-data bukubisnis

# Create application directory
sudo mkdir -p /var/www/bukubisnis
sudo chown bukubisnis:bukubisnis /var/www/bukubisnis
```

### 2. Install Composer

```bash
cd /home/bukubisnis
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 3. Install Node.js (for frontend assets)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 4. Install Redis

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Configure Redis security
sudo nano /etc/redis/redis.conf
# Add these lines:
# requirepass your_secure_redis_password
# bind 127.0.0.1

sudo systemctl restart redis-server
```

### 5. Install Database (MySQL)

```bash
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE bukubisnis_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bukubisnis'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON bukubisnis_production.* TO 'bukubisnis'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Application Deployment

### 1. Clone Repository

```bash
sudo -u bukubisnis git clone https://github.com/your-repo/bukubisnis-api.git /var/www/bukubisnis
cd /var/www/bukubisnis
sudo -u bukubisnis composer install --no-dev --optimize-autoloader
```

### 2. Environment Configuration

```bash
sudo -u bukubisnis cp .env.example .env
sudo -u bukubisnis nano .env
```

```env
# Production Environment Configuration
APP_NAME="BukuBisnis API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bukubisnis_production
DB_USERNAME=bukubisnis
DB_PASSWORD=your_secure_password

# Cache Configuration
BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_secure_redis_password
REDIS_PORT=6379

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,api.yourdomain.com
SESSION_DOMAIN=.yourdomain.com

# CORS Configuration
FRONTEND_URL=https://yourdomain.com
```

### 3. Application Setup

```bash
# Generate application key
sudo -u bukubisnis php artisan key:generate

# Run database migrations
sudo -u bukubisnis php artisan migrate --force

# Cache configuration
sudo -u bukubisnis php artisan config:cache
sudo -u bukubisnis php artisan route:cache
sudo -u bukubisnis php artisan view:cache

# Set proper permissions
sudo chown -R bukubisnis:www-data /var/www/bukubisnis
sudo chmod -R 755 /var/www/bukubisnis
sudo chmod -R 775 /var/www/bukubisnis/storage
sudo chmod -R 775 /var/www/bukubisnis/bootstrap/cache
```

### 4. Install Frontend Dependencies (if needed)

```bash
sudo -u bukubisnis npm install
sudo -u bukubisnis npm run build
```

## Database Configuration

### MySQL Optimization

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
# Basic Configuration
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Connection Configuration
max_connections = 200
max_connect_errors = 10000
wait_timeout = 300
interactive_timeout = 300

# Query Cache (for MySQL 5.7, disabled in 8.0+)
query_cache_type = 1
query_cache_size = 64M

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Security
sql_mode = STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO
```

```bash
sudo systemctl restart mysql
```

### Database Backup Script

```bash
sudo nano /usr/local/bin/backup-bukubisnis.sh
```

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/bukubisnis"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="bukubisnis_production"
DB_USER="bukubisnis"
DB_PASSWORD="your_secure_password"

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u$DB_USER -p$DB_PASSWORD $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Application backup (excluding vendor and node_modules)
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    -C /var/www bukubisnis

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
sudo chmod +x /usr/local/bin/backup-bukubisnis.sh

# Add to crontab for daily backups at 2 AM
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-bukubisnis.sh >> /var/log/bukubisnis-backup.log 2>&1
```

## Web Server Configuration

### Nginx Configuration

```bash
sudo apt install -y nginx
sudo nano /etc/nginx/sites-available/bukubisnis-api
```

```nginx
# Rate limiting zones
limit_req_zone $binary_remote_addr zone=api_auth:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api_general:10m rate=60r/m;

# Upstream PHP-FPM
upstream php-fpm {
    server unix:/var/run/php/php8.3-fpm.sock;
}

# HTTP to HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.yourdomain.com;
    root /var/www/bukubisnis/public;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/bukubisnis.crt;
    ssl_certificate_key /etc/ssl/private/bukubisnis.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'" always;

    # Remove server tokens
    server_tokens off;

    # Logging
    access_log /var/log/nginx/bukubisnis-access.log;
    error_log /var/log/nginx/bukubisnis-error.log;

    # Index file
    index index.php;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Handle API routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Authentication endpoints with stricter rate limiting
    location ~ ^/api/auth/(register|login) {
        limit_req zone=api_auth burst=10 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # General API endpoints
    location ~ ^/api/ {
        limit_req zone=api_general burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass php-fpm;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;

        # Security
        fastcgi_param HTTP_PROXY "";

        # Performance
        fastcgi_buffering on;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    location ~ \.(env|log|conf)$ {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Static assets caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
```

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/bukubisnis-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### PHP-FPM Optimization

```bash
sudo nano /etc/php/8.3/fpm/pool.d/bukubisnis.conf
```

```ini
[bukubisnis]
user = bukubisnis
group = www-data
listen = /var/run/php/php8.3-fpm-bukubisnis.sock
listen.owner = bukubisnis
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10
pm.max_requests = 1000

; Performance tuning
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 30
php_admin_value[max_input_time] = 30
php_admin_value[post_max_size] = 10M
php_admin_value[upload_max_filesize] = 10M

; Security
php_admin_value[expose_php] = Off
php_admin_value[allow_url_fopen] = Off
php_admin_value[allow_url_include] = Off
php_admin_value[display_errors] = Off
php_admin_value[log_errors] = On
php_admin_value[error_log] = /var/log/php8.3-fpm-bukubisnis.log

; OPcache configuration
php_admin_value[opcache.enable] = 1
php_admin_value[opcache.memory_consumption] = 128
php_admin_value[opcache.interned_strings_buffer] = 8
php_admin_value[opcache.max_accelerated_files] = 4000
php_admin_value[opcache.revalidate_freq] = 60
php_admin_value[opcache.fast_shutdown] = 1
```

Update Nginx upstream:

```bash
sudo nano /etc/nginx/sites-available/bukubisnis-api
```

Change upstream to:

```nginx
upstream php-fpm {
    server unix:/var/run/php/php8.3-fpm-bukubisnis.sock;
}
```

```bash
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

## SSL/HTTPS Setup

### Using Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d api.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### Manual SSL Certificate

If using custom SSL certificates:

```bash
# Copy certificates to proper location
sudo cp your-certificate.crt /etc/ssl/certs/bukubisnis.crt
sudo cp your-private-key.key /etc/ssl/private/bukubisnis.key

# Set proper permissions
sudo chmod 644 /etc/ssl/certs/bukubisnis.crt
sudo chmod 600 /etc/ssl/private/bukubisnis.key
sudo chown root:root /etc/ssl/certs/bukubisnis.crt
sudo chown root:root /etc/ssl/private/bukubisnis.key
```

## Performance Optimization

### 1. OPcache Configuration

```bash
sudo nano /etc/php/8.3/fpm/conf.d/10-opcache.ini
```

```ini
; OPcache configuration for production
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.max_wasted_percentage=5
opcache.use_cwd=1
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### 2. System Optimization

```bash
# Increase file limits
sudo nano /etc/security/limits.conf
```

```
bukubisnis soft nofile 65536
bukubisnis hard nofile 65536
www-data soft nofile 65536
www-data hard nofile 65536
```

```bash
# Kernel optimization
sudo nano /etc/sysctl.d/99-bukubisnis.conf
```

```
# Network performance
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
net.ipv4.tcp_rmem = 4096 87380 16777216
net.ipv4.tcp_wmem = 4096 65536 16777216
net.ipv4.tcp_congestion_control = bbr

# File system
fs.file-max = 2097152
vm.swappiness = 10
```

```bash
sudo sysctl -p /etc/sysctl.d/99-bukubisnis.conf
```

### 3. Laravel Queue Worker

```bash
# Create systemd service for queue worker
sudo nano /etc/systemd/system/bukubisnis-worker.service
```

```ini
[Unit]
Description=BukuBisnis Queue Worker
After=redis.service mysql.service

[Service]
User=bukubisnis
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/bukubisnis/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=300
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable bukubisnis-worker
sudo systemctl start bukubisnis-worker
```

## Monitoring Setup

### 1. Application Monitoring

```bash
# Create monitoring script
sudo nano /usr/local/bin/monitor-bukubisnis.sh
```

```bash
#!/bin/bash

LOGFILE="/var/log/bukubisnis-monitor.log"
API_URL="https://api.yourdomain.com/api/health"
EMAIL="admin@yourdomain.com"

# Check API health
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" $API_URL)

if [ $HTTP_STATUS -ne 200 ]; then
    echo "$(date): API Health Check Failed - HTTP $HTTP_STATUS" >> $LOGFILE
    echo "BukuBisnis API is down - HTTP $HTTP_STATUS" | mail -s "API Alert" $EMAIL
fi

# Check disk space
DISK_USAGE=$(df /var/www/bukubisnis | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "$(date): High disk usage - $DISK_USAGE%" >> $LOGFILE
    echo "High disk usage on BukuBisnis server - $DISK_USAGE%" | mail -s "Disk Alert" $EMAIL
fi

# Check memory usage
MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.2f", $3/$2 * 100.0)}')
if (( $(echo "$MEMORY_USAGE > 90" | bc -l) )); then
    echo "$(date): High memory usage - $MEMORY_USAGE%" >> $LOGFILE
    echo "High memory usage on BukuBisnis server - $MEMORY_USAGE%" | mail -s "Memory Alert" $EMAIL
fi

echo "$(date): Monitoring check completed" >> $LOGFILE
```

```bash
sudo chmod +x /usr/local/bin/monitor-bukubisnis.sh

# Add to crontab for every 5 minutes
sudo crontab -e
# Add: */5 * * * * /usr/local/bin/monitor-bukubisnis.sh
```

### 2. Log Rotation

```bash
sudo nano /etc/logrotate.d/bukubisnis
```

```
/var/www/bukubisnis/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 644 bukubisnis www-data
    postrotate
        systemctl reload php8.3-fpm
    endscript
}

/var/log/nginx/bukubisnis-*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data adm
    postrotate
        systemctl reload nginx
    endscript
}
```

### 3. Performance Monitoring

```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Create performance monitoring script
sudo nano /usr/local/bin/perf-monitor.sh
```

```bash
#!/bin/bash

LOGFILE="/var/log/bukubisnis-performance.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

# CPU and Memory usage
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | awk -F'%' '{print $1}')
MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.2f", $3/$2 * 100.0)}')
LOAD_AVERAGE=$(uptime | awk -F'load average:' '{print $2}')

# Database connections
DB_CONNECTIONS=$(mysql -u bukubisnis -pyour_secure_password -e "SHOW STATUS LIKE 'Threads_connected';" | awk 'NR==2 {print $2}')

# Redis memory usage
REDIS_MEMORY=$(redis-cli -a your_secure_redis_password info memory | grep used_memory_human | cut -d: -f2 | tr -d '\r')

echo "$DATE - CPU: $CPU_USAGE%, Memory: $MEMORY_USAGE%, Load: $LOAD_AVERAGE, DB Connections: $DB_CONNECTIONS, Redis Memory: $REDIS_MEMORY" >> $LOGFILE
```

```bash
sudo chmod +x /usr/local/bin/perf-monitor.sh

# Add to crontab for every minute
sudo crontab -e
# Add: * * * * * /usr/local/bin/perf-monitor.sh
```

## Backup Strategy

### 1. Automated Backup Script

```bash
sudo nano /usr/local/bin/backup-bukubisnis-full.sh
```

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/bukubisnis"
S3_BUCKET="your-s3-bucket"  # Optional S3 backup
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u bukubisnis -pyour_secure_password bukubisnis_production | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Application files backup
tar -czf $BACKUP_DIR/app_$DATE.tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    -C /var/www bukubisnis

# Configuration backup
tar -czf $BACKUP_DIR/config_$DATE.tar.gz \
    /etc/nginx/sites-available/bukubisnis-api \
    /etc/php/8.3/fpm/pool.d/bukubisnis.conf \
    /var/www/bukubisnis/.env

# Optional: Upload to S3
if [ ! -z "$S3_BUCKET" ]; then
    aws s3 cp $BACKUP_DIR/db_$DATE.sql.gz s3://$S3_BUCKET/backups/
    aws s3 cp $BACKUP_DIR/app_$DATE.tar.gz s3://$S3_BUCKET/backups/
    aws s3 cp $BACKUP_DIR/config_$DATE.tar.gz s3://$S3_BUCKET/backups/
fi

# Cleanup old backups
find $BACKUP_DIR -name "*.gz" -mtime +$RETENTION_DAYS -delete

echo "$(date): Full backup completed successfully"
```

### 2. Restore Script

```bash
sudo nano /usr/local/bin/restore-bukubisnis.sh
```

```bash
#!/bin/bash

if [ $# -ne 1 ]; then
    echo "Usage: $0 <backup_date>"
    echo "Example: $0 20241201_140000"
    exit 1
fi

BACKUP_DATE=$1
BACKUP_DIR="/var/backups/bukubisnis"
APP_DIR="/var/www/bukubisnis"

echo "Starting restore for backup date: $BACKUP_DATE"

# Stop services
sudo systemctl stop nginx
sudo systemctl stop php8.3-fpm
sudo systemctl stop bukubisnis-worker

# Restore database
echo "Restoring database..."
gunzip < $BACKUP_DIR/db_$BACKUP_DATE.sql.gz | mysql -u bukubisnis -pyour_secure_password bukubisnis_production

# Backup current application (just in case)
tar -czf $BACKUP_DIR/current_app_$(date +%Y%m%d_%H%M%S).tar.gz -C /var/www bukubisnis

# Restore application
echo "Restoring application files..."
tar -xzf $BACKUP_DIR/app_$BACKUP_DATE.tar.gz -C /var/www/

# Set permissions
sudo chown -R bukubisnis:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache

# Clear caches
cd $APP_DIR
sudo -u bukubisnis php artisan config:clear
sudo -u bukubisnis php artisan cache:clear
sudo -u bukubisnis php artisan route:clear
sudo -u bukubisnis php artisan view:clear

# Rebuild caches
sudo -u bukubisnis php artisan config:cache
sudo -u bukubisnis php artisan route:cache
sudo -u bukubisnis php artisan view:cache

# Start services
sudo systemctl start php8.3-fpm
sudo systemctl start nginx
sudo systemctl start bukubisnis-worker

echo "Restore completed successfully"
```

```bash
sudo chmod +x /usr/local/bin/restore-bukubisnis.sh
```

## Maintenance

### 1. Update Script

```bash
sudo nano /usr/local/bin/update-bukubisnis.sh
```

```bash
#!/bin/bash

APP_DIR="/var/www/bukubisnis"
BACKUP_DIR="/var/backups/bukubisnis"
DATE=$(date +%Y%m%d_%H%M%S)

echo "Starting BukuBisnis update process..."

# Create backup before update
echo "Creating backup..."
/usr/local/bin/backup-bukubisnis-full.sh

# Enable maintenance mode
cd $APP_DIR
sudo -u bukubisnis php artisan down --message="System update in progress"

# Pull latest changes
echo "Pulling latest changes..."
sudo -u bukubisnis git fetch origin
sudo -u bukubisnis git reset --hard origin/main

# Update dependencies
echo "Updating dependencies..."
sudo -u bukubisnis composer install --no-dev --optimize-autoloader

# Run migrations
echo "Running migrations..."
sudo -u bukubisnis php artisan migrate --force

# Clear and rebuild caches
echo "Rebuilding caches..."
sudo -u bukubisnis php artisan config:clear
sudo -u bukubisnis php artisan cache:clear
sudo -u bukubisnis php artisan route:clear
sudo -u bukubisnis php artisan view:clear

sudo -u bukubisnis php artisan config:cache
sudo -u bukubisnis php artisan route:cache
sudo -u bukubisnis php artisan view:cache

# Restart services
echo "Restarting services..."
sudo systemctl restart php8.3-fpm
sudo systemctl restart bukubisnis-worker

# Disable maintenance mode
sudo -u bukubisnis php artisan up

echo "Update completed successfully!"
```

```bash
sudo chmod +x /usr/local/bin/update-bukubisnis.sh
```

### 2. Health Check Script

```bash
sudo nano /usr/local/bin/health-check-bukubisnis.sh
```

```bash
#!/bin/bash

API_URL="https://api.yourdomain.com/api/health"
EXPECTED_STATUS=200

echo "BukuBisnis Health Check Report"
echo "=============================="
echo "Date: $(date)"
echo ""

# API Health Check
echo "1. API Health Check"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" $API_URL)
RESPONSE_TIME=$(curl -s -o /dev/null -w "%{time_total}" $API_URL)

if [ $HTTP_STATUS -eq $EXPECTED_STATUS ]; then
    echo "   ✓ API is healthy (HTTP $HTTP_STATUS)"
    echo "   ✓ Response time: ${RESPONSE_TIME}s"
else
    echo "   ✗ API is unhealthy (HTTP $HTTP_STATUS)"
fi

# Database Check
echo ""
echo "2. Database Check"
DB_STATUS=$(mysql -u bukubisnis -pyour_secure_password bukubisnis_production -e "SELECT 1" 2>/dev/null && echo "OK" || echo "FAILED")
if [ "$DB_STATUS" = "OK" ]; then
    echo "   ✓ Database connection successful"

    # Check table count
    TABLE_COUNT=$(mysql -u bukubisnis -pyour_secure_password bukubisnis_production -e "SHOW TABLES" | wc -l)
    echo "   ✓ Tables found: $((TABLE_COUNT - 1))"
else
    echo "   ✗ Database connection failed"
fi

# Redis Check
echo ""
echo "3. Redis Check"
REDIS_STATUS=$(redis-cli -a your_secure_redis_password ping 2>/dev/null)
if [ "$REDIS_STATUS" = "PONG" ]; then
    echo "   ✓ Redis is responding"

    # Check memory usage
    REDIS_MEMORY=$(redis-cli -a your_secure_redis_password info memory | grep used_memory_human | cut -d: -f2 | tr -d '\r')
    echo "   ✓ Redis memory usage: $REDIS_MEMORY"
else
    echo "   ✗ Redis is not responding"
fi

# PHP-FPM Check
echo ""
echo "4. PHP-FPM Check"
if systemctl is-active --quiet php8.3-fpm; then
    echo "   ✓ PHP-FPM is running"

    # Check pool status
    POOL_STATUS=$(systemctl is-active php8.3-fpm)
    echo "   ✓ Pool status: $POOL_STATUS"
else
    echo "   ✗ PHP-FPM is not running"
fi

# Nginx Check
echo ""
echo "5. Nginx Check"
if systemctl is-active --quiet nginx; then
    echo "   ✓ Nginx is running"

    # Check configuration
    NGINX_CONFIG=$(nginx -t 2>&1 | grep "syntax is ok" | wc -l)
    if [ $NGINX_CONFIG -eq 1 ]; then
        echo "   ✓ Nginx configuration is valid"
    else
        echo "   ✗ Nginx configuration has errors"
    fi
else
    echo "   ✗ Nginx is not running"
fi

# Disk Space Check
echo ""
echo "6. Disk Space Check"
DISK_USAGE=$(df /var/www/bukubisnis | awk 'NR==2 {print $5}' | sed 's/%//')
echo "   ✓ Disk usage: $DISK_USAGE%"

if [ $DISK_USAGE -gt 80 ]; then
    echo "   ⚠ Warning: High disk usage"
elif [ $DISK_USAGE -gt 90 ]; then
    echo "   ✗ Critical: Very high disk usage"
fi

# Memory Usage Check
echo ""
echo "7. Memory Usage Check"
MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
echo "   ✓ Memory usage: $MEMORY_USAGE%"

if (( $(echo "$MEMORY_USAGE > 80" | bc -l) )); then
    echo "   ⚠ Warning: High memory usage"
elif (( $(echo "$MEMORY_USAGE > 90" | bc -l) )); then
    echo "   ✗ Critical: Very high memory usage"
fi

echo ""
echo "=============================="
echo "Health check completed"
```

```bash
sudo chmod +x /usr/local/bin/health-check-bukubisnis.sh
```

### 3. Regular Maintenance Tasks

Add to root crontab:

```bash
sudo crontab -e
```

```cron
# Daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-bukubisnis-full.sh >> /var/log/bukubisnis-backup.log 2>&1

# Weekly health check report (Sunday at 6 AM)
0 6 * * 0 /usr/local/bin/health-check-bukubisnis.sh | mail -s "Weekly Health Report" admin@yourdomain.com

# Monthly log cleanup (first day of month at 3 AM)
0 3 1 * * find /var/log -name "*.log" -mtime +30 -delete

# SSL certificate renewal check (daily at 4 AM)
0 4 * * * /usr/bin/certbot renew --quiet

# Performance monitoring (every minute)
* * * * * /usr/local/bin/perf-monitor.sh

# Application monitoring (every 5 minutes)
*/5 * * * * /usr/local/bin/monitor-bukubisnis.sh
```

## Security Checklist

### Server Security

-   [ ] SSH key-based authentication only
-   [ ] Firewall configured (UFW or iptables)
-   [ ] Fail2ban for brute force protection
-   [ ] Regular security updates
-   [ ] Non-root user for application
-   [ ] Proper file permissions

### Application Security

-   [ ] Environment variables secure
-   [ ] Database credentials secure
-   [ ] HTTPS enforced
-   [ ] Security headers implemented
-   [ ] Rate limiting configured
-   [ ] Input validation in place
-   [ ] CORS properly configured

### Monitoring Security

-   [ ] Log monitoring for suspicious activity
-   [ ] Failed login attempt monitoring
-   [ ] File integrity monitoring
-   [ ] Performance monitoring
-   [ ] Automated backup verification

## Final Steps

1. **Test the deployment**: Run the health check script
2. **Load test**: Use Apache Bench or similar tools
3. **Security scan**: Use tools like Nmap and OWASP ZAP
4. **Documentation**: Document any custom configurations
5. **Team training**: Ensure team knows backup/restore procedures

Your BukuBisnis API is now ready for production! 🚀

Remember to:

-   Monitor logs regularly
-   Keep systems updated
-   Test backups periodically
-   Review security regularly
-   Document any changes
