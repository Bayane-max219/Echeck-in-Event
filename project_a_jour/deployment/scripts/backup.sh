#!/bin/bash

# Echeck-in Event Backup Script
# Usage: ./backup.sh

set -e

BACKUP_DIR="/var/backups/echeck-in-event"
PROJECT_DIR="/var/www/echeck-in-event"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

log "🔄 Starting backup process..."

# Database backup
log "📦 Backing up database..."
mysqldump -u root -p echeck_in | gzip > $BACKUP_DIR/database_$DATE.sql.gz
log "✅ Database backup completed: database_$DATE.sql.gz"

# Files backup
log "📦 Backing up files..."
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $PROJECT_DIR \
    --exclude='var/cache' \
    --exclude='var/log' \
    --exclude='node_modules' \
    --exclude='.git' \
    .
log "✅ Files backup completed: files_$DATE.tar.gz"

# Clean old backups
log "🧹 Cleaning old backups (older than $RETENTION_DAYS days)..."
find $BACKUP_DIR -name "database_*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "files_*.tar.gz" -mtime +$RETENTION_DAYS -delete
log "✅ Old backups cleaned"

# Upload to cloud storage (optional)
# aws s3 cp $BACKUP_DIR/database_$DATE.sql.gz s3://your-bucket/backups/
# aws s3 cp $BACKUP_DIR/files_$DATE.tar.gz s3://your-bucket/backups/

log "✨ Backup process completed successfully!"