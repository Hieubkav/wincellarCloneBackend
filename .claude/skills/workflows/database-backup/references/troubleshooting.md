## Troubleshooting

### Issue 1: Backup Command Not Found

**Solution:**
```bash
# Install Spatie backup
composer require spatie/laravel-backup

# Publish config
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

### Issue 2: mysqldump Not Found

**Solution (Windows):**
```bash
# Add MySQL bin to PATH
set PATH=%PATH%;C:\Program Files\MySQL\MySQL Server 8.0\bin
```

### Issue 3: Backup Fails (Permission)

**Solution:**
```bash
# Create directory
mkdir database/backups

# Set permissions (Linux/Mac)
chmod 775 database/backups
```

### Issue 4: Restore Fails (SQL Error)

**Solution:**
```bash
# Check MySQL version
mysql --version

# Import with verbose
mysql -u username -p database_name < backup.sql --verbose
```
