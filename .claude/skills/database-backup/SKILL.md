---
name: database-backup
description: Safe database migration workflow with Spatie backup integration. Always backup before migration, update mermaid.rb schema, keep max 10 recent backups. USE WHEN creating migrations, running migrations, restoring database, managing schema changes, or any risky database operations.
---

# Database Backup - Safe Migration Workflow

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

### Step 1: Backup Database

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

### Step 2: Run Migration

After backup success:
```bash
php artisan migrate
```

Or specific migration:
```bash
php artisan migrate --path=database/migrations/2025_11_09_create_images_table.php
```

### Step 3: Update Schema Documentation

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

### Step 4: Verify Success

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

### If Migration Fails:

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

### Test Restore:
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

## Backup Naming Best Practice

Include migration description in filename:

```bash
# Before running migration
php artisan backup:run --only-db

# Manually rename (optional)
mv database/backups/Laravel/2025-11-09-21-30-00.zip \
   database/backups/Laravel/2025-11-09-21-30-00_add-images-table.zip
```

## Critical Rules

1. ⚠️ **NEVER** run migration without backup
2. ⚠️ **ALWAYS** update `mermaid.rb` after migration
3. ⚠️ **VERIFY** backup success before migration
4. ⚠️ **TEST** restore process periodically
5. ⚠️ **KEEP** max 10 backups (auto-cleanup)

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

## Key Principles

1. **Backup First**: ALWAYS before migrations
2. **Update Schema**: Keep mermaid.rb in sync
3. **Max 10 Backups**: Auto-cleanup oldest
4. **Test Restore**: Verify backups work
5. **Document Changes**: Migration description in filename

## Integration with Migration Workflow

**Standard process:**
```bash
# 1. Backup
php artisan backup:run --only-db

# 2. Create migration
php artisan make:migration add_images_table

# 3. Edit migration file
# ...

# 4. Run migration
php artisan migrate

# 5. Update schema doc
vim mermaid.rb

# 6. Commit
git add database/migrations/ mermaid.rb
git commit -m "feat(db): add images table"
```

## Supplementary Resources

**Full backup guide:**
```
read .claude/skills/database-backup/CLAUDE.md
```

**Related skills:**
- **filament-resource-generator**: Creates migrations
- **create-skill**: Document new workflows

## Quick Reference Card

| Command | Purpose |
|---------|---------|
| `backup:run --only-db` | Backup database |
| `migrate` | Run pending migrations |
| `migrate:rollback` | Undo last migration |
| `migrate:status` | Check migration status |
| `backup:list` | List all backups |
| `backup:clean` | Delete old backups |

**Remember: Backup → Migrate → Update Schema → Verify! 💾**
