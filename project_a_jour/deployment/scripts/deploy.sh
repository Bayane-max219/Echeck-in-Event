#!/bin/bash

# Echeck-in Event Deployment Script
# Usage: ./deploy.sh [environment]

set -e

ENVIRONMENT=${1:-production}
PROJECT_DIR="/var/www/echeck-in-event"
BACKUP_DIR="/var/backups/echeck-in-event"
DATE=$(date +%Y%m%d_%H%M%S)

echo "🚀 Starting deployment for environment: $ENVIRONMENT"

# Create backup directory
mkdir -p $BACKUP_DIR

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# Function to backup database
backup_database() {
    log "📦 Creating database backup..."
    mysqldump -u root -p echeck_in > $BACKUP_DIR/database_backup_$DATE.sql
    log "✅ Database backup created: database_backup_$DATE.sql"
}

# Function to backup files
backup_files() {
    log "📦 Creating files backup..."
    tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz -C $PROJECT_DIR .
    log "✅ Files backup created: files_backup_$DATE.tar.gz"
}

# Function to update code
update_code() {
    log "📥 Updating code from repository..."
    cd $PROJECT_DIR
    git fetch origin
    git reset --hard origin/main
    log "✅ Code updated"
}

# Function to install dependencies
install_dependencies() {
    log "📦 Installing PHP dependencies..."
    cd $PROJECT_DIR/backend
    composer install --no-dev --optimize-autoloader
    log "✅ Dependencies installed"
}

# Function to run migrations
run_migrations() {
    log "🗄️ Running database migrations..."
    cd $PROJECT_DIR/backend
    php bin/console doctrine:migrations:migrate --no-interaction
    log "✅ Migrations completed"
}

# Function to clear cache
clear_cache() {
    log "🧹 Clearing application cache..."
    cd $PROJECT_DIR/backend
    php bin/console cache:clear --env=$ENVIRONMENT
    php bin/console cache:warmup --env=$ENVIRONMENT
    log "✅ Cache cleared"
}

# Function to set permissions
set_permissions() {
    log "🔐 Setting file permissions..."
    cd $PROJECT_DIR/backend
    chown -R www-data:www-data .
    chmod -R 755 .
    chmod -R 777 var/
    log "✅ Permissions set"
}

# Function to restart services
restart_services() {
    log "🔄 Restarting services..."
    systemctl restart php8.2-fpm
    systemctl restart nginx
    log "✅ Services restarted"
}

# Function to run tests
run_tests() {
    if [ "$ENVIRONMENT" != "production" ]; then
        log "🧪 Running tests..."
        cd $PROJECT_DIR/backend
        php bin/phpunit
        log "✅ Tests passed"
    fi
}

# Function to health check
health_check() {
    log "🏥 Performing health check..."
    
    # Check if web server responds
    if curl -f -s http://localhost/api/health > /dev/null; then
        log "✅ Web server is responding"
    else
        log "❌ Web server is not responding"
        exit 1
    fi
    
    # Check database connection
    cd $PROJECT_DIR/backend
    if php bin/console doctrine:query:sql "SELECT 1" > /dev/null; then
        log "✅ Database connection is working"
    else
        log "❌ Database connection failed"
        exit 1
    fi
}

# Function to send notification
send_notification() {
    local status=$1
    local message="Deployment $status for Echeck-in Event ($ENVIRONMENT)"
    
    # Send email notification (configure SMTP settings)
    # echo "$message" | mail -s "Deployment Notification" admin@example.com
    
    # Send Slack notification (configure webhook URL)
    # curl -X POST -H 'Content-type: application/json' \
    #   --data "{\"text\":\"$message\"}" \
    #   YOUR_SLACK_WEBHOOK_URL
    
    log "📧 Notification sent: $message"
}

# Main deployment process
main() {
    log "🎯 Starting deployment process..."
    
    # Pre-deployment checks
    if [ ! -d "$PROJECT_DIR" ]; then
        log "❌ Project directory not found: $PROJECT_DIR"
        exit 1
    fi
    
    # Create maintenance page
    log "🚧 Enabling maintenance mode..."
    touch $PROJECT_DIR/backend/public/maintenance.html
    
    # Backup before deployment
    backup_database
    backup_files
    
    # Deployment steps
    update_code
    install_dependencies
    run_migrations
    clear_cache
    set_permissions
    
    # Run tests (non-production only)
    run_tests
    
    # Restart services
    restart_services
    
    # Remove maintenance page
    log "✅ Disabling maintenance mode..."
    rm -f $PROJECT_DIR/backend/public/maintenance.html
    
    # Health check
    health_check
    
    # Clean old backups (keep last 10)
    log "🧹 Cleaning old backups..."
    cd $BACKUP_DIR
    ls -t database_backup_*.sql | tail -n +11 | xargs -r rm
    ls -t files_backup_*.tar.gz | tail -n +11 | xargs -r rm
    
    log "🎉 Deployment completed successfully!"
    send_notification "completed successfully"
}

# Error handling
trap 'log "❌ Deployment failed!"; send_notification "failed"; exit 1' ERR

# Run main function
main

log "✨ Deployment finished at $(date)"