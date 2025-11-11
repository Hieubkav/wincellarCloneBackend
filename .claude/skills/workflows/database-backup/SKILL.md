---
name: database-backup
description: Safe database migration workflow with Spatie backup integration. Always backup before migration, update mermaid.rb schema, keep max 10 recent backups. USE WHEN creating migrations, running migrations, restoring database, managing schema changes, or any risky database operations.
---
## When to Activate This Skill

- User says "chạy migration"
- User says "create migration"
- User mentions "database backup"
- User wants to "restore database"
- Before ANY risky database operation
- Updating database schema

## 🚨 CRITICAL: ALWAYS Backup First!

**Before EVERY migration:**
```bash
php artisan backup:run --only-db
```

## Core Workflow

**Step 1: Backup Database

Execute backup command:
```bash
php artisan backup:run --only-db
```

**Output location:**
```
database/backups/Laravel/YYYY-MM-DD-HH-MM-SS.zip
```

**Naming convention:**
```
2025-11-09-21-30-00_add-images-table.zip
```

**Step 2: Run Migration

After backup success:
```bash
php artisan migrate
```

Or specific migration:
```bash
php artisan migrate --path=database/migrations/2025_11_09_create_images_table.php
```

**Step 3: Update Schema Documentation

Edit `mermaid.rb` to reflect changes:
```ruby
ActiveRecord::Schema[7.0].define(version: 2025_11_09_123456) do
  create_table "images", force: :cascade do |t|
    t.string "file_path", limit: 2048, null: false
    t.string "disk", limit: 191, default: "public"
    # ... all columns
  end
end
```

**Step 4: Verify Success

Check migration status:
```bash
php artisan migrate:status
```

## Backup Configuration

**Location:** `config/backup.php`

**Key settings:**
- **Max backups:** 10 (auto-delete oldest)
- **Disk:** local (`database/backups/`)
- **Only database:** Skip files for faster backup

**Spatie backup installed:**
```bash
composer require spatie/laravel-backup
```

## Restore Workflow

**If Migration Fails:

**Step 1: Rollback**
```bash
php artisan migrate:rollback
```

**Step 2: Restore from Backup**
```bash
# Extract .zip
unzip database/backups/Laravel/2025-11-09-21-30-00.zip

# Import .sql
mysql -u username -p database_name < Laravel/db-dumps/mysql-database_name.sql
```

**Test Restore:
```bash
# Check database connection
php artisan db:show

# Verify tables
php artisan tinker
>>> DB::table('users')->count();
```

## Common Commands

```bash
# Backup database only
php artisan backup:run --only-db

# Backup everything (db + files)
php artisan backup:run

# List backups
php artisan backup:list

# Clean old backups
php artisan backup:clean

# Check backup health
php artisan backup:monitor
```
