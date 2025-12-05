# Laravel Backup - Complete API Reference

## Configuration Structure

### Backup.php Configuration File

```php
return [
    // Backup configuration
    'backup' => [
        'name' => env('APP_NAME', 'laravel-app'),
        'source' => [
            'files' => [
                'include' => [],
                'exclude' => [],
                'follow_links' => false,
                'relative_path' => null,
            ],
            'databases' => ['mysql'],
        ],
        'destination' => [
            'disks' => ['local'],
        ],
        'temp_directory' => storage_path('temp'),
        'password' => env('BACKUP_PASSWORD'),
        'encryption' => 'default',
        'compression' => 'gzip',
    ],
    
    // Cleanup configuration
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_year' => 7,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
    
    // Notification configuration
    'notifications' => [
        'mail' => [
            'enabled' => true,
            'to' => env('BACKUP_NOTIFICATION_EMAIL'),
        ],
        'slack' => [
            'enabled' => false,
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
        ],
        'discord' => [
            'enabled' => false,
            'webhook_url' => env('DISCORD_WEBHOOK_URL'),
        ],
        'custom_notification_classes' => [],
    ],
    
    // Monitoring configuration
    'monitor_backups' => [
        [
            'name' => 'production',
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],
    
    // Notifiable class
    'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
];
```

## Database Dump Configuration

### MySQL Configuration

```php
'dump' => [
    'mysql' => [
        'dump_command_path' => '/usr/bin',
        'dump_binary_path' => null,
        'timeout' => 60,
        'exclude_tables' => [],
        'exclude_table_data' => [],
        'single_transaction' => true,
        'use_single_transaction' => true,
        'use_lock_tables' => true,
        'ignore_tables' => [],
        'no_data' => [],
        'add_routines' => false,
        'use_extended_insert' => true,
        'use_complete_insert' => false,
        'skip_comments' => false,
        'skip_lock_tables' => false,
        'use_ssl' => false,
        'skip_ssl_verification' => true,
        'extra_options' => '',
        'add_extra_options' => null,
    ],
],
```

### PostgreSQL Configuration

```php
'dump' => [
    'pgsql' => [
        'dump_command_path' => '/usr/bin',
        'dump_binary_path' => null,
        'timeout' => 60,
        'exclude_tables' => [],
        'exclude_schema' => [],
        'use_single_transaction' => true,
        'extra_options' => '',
        'add_extra_options' => null,
    ],
],
```

## Artisan Commands

### backup:run

Execute a backup of files and databases.

```bash
php artisan backup:run [options]

Options:
  --only-files           Backup files only
  --only-db              Backup database only
  --disks=DISKS          Comma-separated list of disks (default: all)
  --isolated             Run only on single server using atomic lock
```

### backup:clean

Clean old backups according to retention policy.

```bash
php artisan backup:clean [options]

Options:
  --disks=DISKS          Comma-separated list of disks
  --isolated             Run only on single server using atomic lock
```

### backup:monitor

Check health of configured backups.

```bash
php artisan backup:monitor [options]

Options:
  --isolated             Run only on single server using atomic lock
```

## Events

### BackupWasSuccessful

Fired when backup successfully completes.

```php
use Spatie\Backup\Events\BackupWasSuccessful;

Event::listen(BackupWasSuccessful::class, function (BackupWasSuccessful $event) {
    $backupDestination = $event->backupDestination;
    // $backupDestination->disk, ->path, ->fileSize, ->backupName
});
```

### BackupHasFailed

Fired when backup fails.

```php
use Spatie\Backup\Events\BackupHasFailed;

Event::listen(BackupHasFailed::class, function (BackupHasFailed $event) {
    $exception = $event->exception;
});
```

### BackupManifestWasCreated

Fired after backup manifest created.

```php
use Spatie\Backup\Events\BackupManifestWasCreated;

Event::listen(BackupManifestWasCreated::class, function (BackupManifestWasCreated $event) {
    $manifest = $event->manifest;
});
```

### CleanupWasSuccessful

Fired after cleanup completes.

```php
use Spatie\Backup\Events\CleanupWasSuccessful;
```

### CleanupHasFailed

Fired when cleanup fails.

```php
use Spatie\Backup\Events\CleanupHasFailed;
```

### HealthyBackupWasFound

Fired when monitored backup passes health checks.

```php
use Spatie\Backup\Events\HealthyBackupWasFound;

Event::listen(HealthyBackupWasFound::class, function (HealthyBackupWasFound $event) {
    $destination = $event->backupDestination;
});
```

### UnHealthyBackupWasFound

Fired when monitored backup fails health checks.

```php
use Spatie\Backup\Events\UnHealthyBackupWasFound;

Event::listen(UnHealthyBackupWasFound::class, function (UnHealthyBackupWasFound $event) {
    $destination = $event->backupDestination;
});
```

## Health Checks

### MaximumAgeInDays

Check that backup is not older than specified days.

```php
\Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1
```

### MaximumStorageInMegabytes

Check that backup storage doesn't exceed specified MB.

```php
\Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000
```

### Custom Health Check

Create custom health check by extending:

```php
use Spatie\Backup\Tasks\Monitor\HealthCheck;

class MyCustomHealthCheck extends HealthCheck
{
    public function checkHealth(): void
    {
        // Your validation logic
        if ($somethingWrong) {
            $this->failed('Error message');
        }
    }
}
```

Register in config:

```php
'health_checks' => [
    MyCustomHealthCheck::class => null,
]
```

## Custom Notifiable Class

### Extending Default Notifiable

```php
use Spatie\Backup\Notifications\Notifiable;

class CustomBackupNotifiable extends Notifiable
{
    public function toMail($event)
    {
        // Custom mail notification logic
    }

    public function toSlack($event)
    {
        // Custom slack notification logic
    }

    public function toDiscord($event)
    {
        // Custom discord notification logic
    }
}
```

Register in `config/backup.php`:

```php
'notifiable' => \App\Notifications\CustomBackupNotifiable::class,
```

## Storage Disks

### S3 Configuration

```php
// config/filesystems.php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => false,
],

// config/backup.php
'destination' => [
    'disks' => ['s3'],
]
```

### Multiple Disks

```php
'destination' => [
    'disks' => ['local', 's3', 'dropbox'],
]
```

### Extra Filesystem Options

```php
// config/filesystems.php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    // Extra options for flysystem driver
    'visibility' => 'private',
    'metadata' => [
        'CacheControl' => 'max-age=315360000',
    ],
]
```

## Extending Database Dumpers

```php
use Spatie\Backup\Tasks\Backup\DbDumperFactory;

DbDumperFactory::extend('custom_driver', function ($config) {
    return new CustomDbDumper($config);
});
```

## Backup Filename Format

Default format: `{applicationName}-{timestamp}.zip`

Example: `myapp-2024-12-03-020000.zip`

## Cleanup Strategies

### DefaultStrategy

Keeps backups based on:
- All backups for N days
- Daily backups for M days
- Weekly backups for W weeks
- Monthly backups for L months
- Yearly backups for Y years
- Storage size limit

### Custom Strategy

```php
use Spatie\Backup\Tasks\Cleanup\Strategy;

class CustomCleanupStrategy implements Strategy
{
    public function deleteOldBackups(Collection $backups): void
    {
        // Custom cleanup logic
    }
}
```

## Permissions & Security

### File Permissions
- Backup files are created with restrictive permissions
- Ensure storage directory is not web-accessible

### Encryption
- Use strong passwords
- Store password in .env file
- Supports AES-256 encryption

### Database Security
- Use separate database user with minimal privileges
- Consider read-only database accounts
- Use SSL for remote databases

## Error Handling

### Common Issues

**1. Database dump fails**
- Check dump binary path
- Verify database credentials
- Ensure sufficient permissions
- Check database connectivity

**2. Disk space insufficient**
- Monitor available storage
- Increase cleanup frequency
- Reduce backup retention
- Add secondary storage disk

**3. Notifications not sent**
- Verify notification channel configuration
- Check email/Slack credentials
- Ensure webhook URLs are correct
- Test notification manually

**4. Backup hangs**
- Increase timeout value
- Exclude large files
- Use `--only-files` or `--only-db`
- Check system resources

## Performance Optimization

### Large Database Backups
- Use `--only-db` flag
- Exclude unnecessary tables
- Run during low-traffic periods
- Use isolated mode on single server

### Large File Backups
- Exclude vendor and node_modules
- Use specific include paths
- Consider separate file backups
- Use compression

### Distributed Systems
- Always use `--isolated` flag
- Verify atomic lock mechanism
- Test failover scenarios
- Monitor lock state
