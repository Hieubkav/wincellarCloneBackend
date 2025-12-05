# Laravel Backup - Real-World Examples

## Example 1: Basic Production Setup

### Configuration

```php
// config/backup.php
return [
    'backup' => [
        'name' => 'my-app',
        'source' => [
            'files' => [
                'include' => [
                    base_path('app'),
                    base_path('config'),
                    base_path('database'),
                    base_path('resources'),
                    base_path('bootstrap'),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('logs'),
                    storage_path('framework'),
                ],
                'follow_links' => false,
            ],
            'databases' => ['mysql'],
        ],
        'destination' => [
            'disks' => ['s3'],
        ],
        'password' => env('BACKUP_PASSWORD', null),
        'encryption' => env('BACKUP_ENCRYPTION', 'default'),
    ],
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_year' => 7,
            'delete_oldest_backups_when_using_more_megabytes_than' => 10000,
        ],
    ],
    'notifications' => [
        'mail' => [
            'enabled' => true,
            'to' => env('BACKUP_NOTIFICATION_EMAIL'),
        ],
    ],
    'monitor_backups' => [
        [
            'name' => 'production',
            'disks' => ['s3'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 10000,
            ],
        ],
    ],
];
```

### Schedule (Console Kernel)

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Run backup at 2 AM daily
    $schedule->command('backup:run')
        ->dailyAt('02:00')
        ->onFailure(function () {
            Log::error('Backup failed');
        })
        ->onSuccess(function () {
            Log::info('Backup completed successfully');
        });
    
    // Clean old backups at 3 AM daily
    $schedule->command('backup:clean')
        ->dailyAt('03:00');
    
    // Monitor backup health at 4 AM daily
    $schedule->command('backup:monitor')
        ->dailyAt('04:00');
}
```

### Event Listeners

```php
// app/Listeners/NotifyBackupSuccess.php
use Spatie\Backup\Events\BackupWasSuccessful;

class NotifyBackupSuccess
{
    public function handle(BackupWasSuccessful $event)
    {
        Log::info('Backup successful', [
            'disk' => $event->backupDestination->disk(),
            'path' => $event->backupDestination->path(),
            'size' => $event->backupDestination->fileSize(),
        ]);
    }
}

// app/Listeners/NotifyBackupFailure.php
use Spatie\Backup\Events\BackupHasFailed;
use Illuminate\Support\Facades\Mail;

class NotifyBackupFailure
{
    public function handle(BackupHasFailed $event)
    {
        Log::error('Backup failed: ' . $event->exception->getMessage());
        
        Mail::send('emails.backup-failed', [
            'error' => $event->exception->getMessage(),
        ], function ($message) {
            $message->to('admin@example.com');
        });
    }
}
```

## Example 2: Multi-Disk Setup with Redundancy

### Configuration

```php
// config/backup.php
return [
    'backup' => [
        'name' => 'my-app',
        'source' => [
            'files' => [
                'include' => [
                    base_path('app'),
                    base_path('config'),
                    base_path('resources'),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],
            ],
            'databases' => ['mysql', 'pgsql'],
        ],
        'destination' => [
            'disks' => ['s3', 'local_backup', 'dropbox'],
        ],
        'password' => env('BACKUP_PASSWORD'),
        'encryption' => 'default',
    ],
    'cleanup' => [
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'delete_oldest_backups_when_using_more_megabytes_than' => 50000,
        ],
    ],
];

// config/filesystems.php
'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET'),
    ],
    'local_backup' => [
        'driver' => 'local',
        'root' => storage_path('backups'),
    ],
    'dropbox' => [
        'driver' => 'dropbox',
        'authorization_token' => env('DROPBOX_AUTH_TOKEN'),
    ],
],
```

### Scheduled Backups

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Full backup daily
    $schedule->command('backup:run')
        ->dailyAt('02:00')
        ->withoutOverlapping();
    
    // Database-only backup twice daily
    $schedule->command('backup:run --only-db')
        ->dailyAt('14:00')
        ->withoutOverlapping();
    
    // Weekly full backup with S3 and local
    $schedule->command('backup:run --disks=s3,local_backup')
        ->sundays()
        ->at('01:00');
}
```

## Example 3: Encrypted Backups with Custom Notifications

### Configuration

```php
// config/backup.php
return [
    'backup' => [
        'name' => 'secure-app',
        'source' => [
            'files' => [
                'include' => [
                    base_path('app'),
                    base_path('config'),
                    base_path('database'),
                    base_path('resources'),
                ],
            ],
            'databases' => ['mysql'],
        ],
        'destination' => [
            'disks' => ['s3'],
        ],
        'password' => env('BACKUP_PASSWORD'), // Must be strong and stored securely
        'encryption' => 'default', // AES-256
    ],
    'notifications' => [
        'mail' => [
            'enabled' => true,
            'to' => 'admin@example.com',
        ],
        'slack' => [
            'enabled' => true,
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
        ],
        'custom_notification_classes' => [
            \App\Notifications\BackupNotification::class,
        ],
    ],
    'notifiable' => \App\Notifications\CustomBackupNotifiable::class,
];
```

### Custom Notifiable

```php
// app/Notifications/CustomBackupNotifiable.php
use Spatie\Backup\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class CustomBackupNotifiable extends Notifiable
{
    public function toMail($event): Notification
    {
        return (new BackupMailNotification($event))
            ->to(config('backup.notifications.mail.to'));
    }

    public function toSlack($event)
    {
        return (new BackupSlackNotification($event))
            ->to(config('backup.notifications.slack.webhook_url'));
    }
}

// app/Notifications/BackupMailNotification.php
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BackupMailNotification extends Mailable
{
    use Queueable, SerializesModels;
    
    protected $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function build()
    {
        return $this->view('emails.backup-notification')
            ->with('event', $this->event)
            ->subject('Backup ' . class_basename($this->event) . ' for ' . config('app.name'));
    }
}
```

## Example 4: Monitoring Multiple Applications

### Configuration

```php
// config/backup.php
return [
    'monitor_backups' => [
        [
            'name' => 'production-app',
            'disks' => ['s3'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 10000,
            ],
        ],
        [
            'name' => 'staging-app',
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 2,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
        [
            'name' => 'backup-archive',
            'disks' => ['s3'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 50000,
            ],
        ],
    ],
];
```

### Monitor Event Handling

```php
// app/Listeners/NotifyUnhealthyBackup.php
use Spatie\Backup\Events\UnHealthyBackupWasFound;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotifyUnhealthyBackup
{
    public function handle(UnHealthyBackupWasFound $event)
    {
        Log::critical('Unhealthy backup detected', [
            'destination' => $event->backupDestination->backupName(),
            'disk' => $event->backupDestination->disk(),
        ]);

        Mail::send('emails.unhealthy-backup', [
            'destination' => $event->backupDestination,
        ], function ($message) {
            $message->to('alert@example.com')
                ->subject('CRITICAL: Unhealthy Backup Detected');
        });
    }
}

// app/Providers/EventServiceProvider.php
protected $listen = [
    \Spatie\Backup\Events\UnHealthyBackupWasFound::class => [
        \App\Listeners\NotifyUnhealthyBackup::class,
    ],
    \Spatie\Backup\Events\HealthyBackupWasFound::class => [
        \App\Listeners\LogHealthyBackup::class,
    ],
];
```

## Example 5: Distributed System with Isolated Mode

### Configuration

```php
// config/backup.php
return [
    'backup' => [
        'name' => 'distributed-app',
        'source' => [
            'files' => [
                'include' => [base_path('app'), base_path('config')],
            ],
            'databases' => ['mysql'],
        ],
        'destination' => [
            'disks' => ['s3', 'backup_vault'],
        ],
    ],
];

// config/cache.php - For distributed locking
'default' => env('CACHE_DRIVER', 'redis'),
```

### Scheduling with Isolated Mode

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Only one server executes this via atomic lock
    $schedule->command('backup:run --isolated')
        ->dailyAt('02:00')
        ->withoutOverlapping();
    
    $schedule->command('backup:clean --isolated')
        ->dailyAt('03:00')
        ->withoutOverlapping();
    
    $schedule->command('backup:monitor --isolated')
        ->dailyAt('04:00')
        ->withoutOverlapping();
}
```

## Example 6: Selective Database Backups

### Configuration for Multiple Databases

```php
// config/backup.php
return [
    'backup' => [
        'source' => [
            'databases' => [
                'mysql', // Production database
                'analytics', // Analytics database
                'cache_db', // Cache database (exclude)
            ],
        ],
    ],
    'dump' => [
        'mysql' => [
            'exclude_tables' => [
                'cache',
                'sessions',
                'failed_jobs',
            ],
            'exclude_table_data' => [
                'logs',
                'activity_log',
            ],
        ],
    ],
];
```

### Backup Script

```bash
#!/bin/bash
# Backup primary database only
php artisan backup:run --only-db

# Backup specific disk
php artisan backup:run --disks=s3

# Backup files only
php artisan backup:run --only-files
```

## Example 7: Custom Health Check

### Implementation

```php
// app/Backup/HealthChecks/DatabaseIntegrityCheck.php
use Spatie\Backup\Tasks\Monitor\HealthCheck;

class DatabaseIntegrityCheck extends HealthCheck
{
    public function checkHealth(): void
    {
        $result = DB::select('CHECK TABLE users');
        
        if ($result[0]->Msg_text !== 'OK') {
            $this->failed('Database integrity check failed');
        }
    }
}

// config/backup.php
'monitor_backups' => [
    [
        'name' => 'production',
        'disks' => ['s3'],
        'health_checks' => [
            \App\Backup\HealthChecks\DatabaseIntegrityCheck::class => null,
            \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
        ],
    ],
],
```

## Example 8: Cleanup Verification Script

### Manual Cleanup with Logging

```php
// app/Commands/CleanBackupsVerified.php
use Illuminate\Console\Command;
use Spatie\Backup\Tasks\Backup\BackupDestinationFactory;

class CleanBackupsVerified extends Command
{
    public function handle()
    {
        $destinations = BackupDestinationFactory::create();
        
        foreach ($destinations as $destination) {
            $this->info("Cleaning backups on disk: {$destination->disk()}");
            $this->info("Before cleanup: {$destination->fileSize()} MB");
            
            // Run cleanup
            Artisan::call('backup:clean', ['--disks' => $destination->disk()]);
            
            $this->info("After cleanup: {$destination->fileSize()} MB");
        }
    }
}
```

## Example 9: Backup Restoration

### Restoration Script

```php
// app/Commands/RestoreBackup.php
use Illuminate\Console\Command;
use ZipArchive;

class RestoreBackup extends Command
{
    protected $signature = 'backup:restore {backupFile} {--target=.}';

    public function handle()
    {
        $backupFile = $this->argument('backupFile');
        $targetDir = $this->argument('target');
        
        $zip = new ZipArchive();
        
        if ($zip->open($backupFile) === true) {
            $zip->extractTo($targetDir);
            $zip->close();
            $this->info("Backup restored successfully to: $targetDir");
        } else {
            $this->error("Failed to open backup file");
        }
    }
}
```

## Example 10: Slack Integration

### Setup

```php
// Install Slack notification channel
composer require laravel/slack-notification-channel

// config/backup.php
'notifications' => [
    'slack' => [
        'enabled' => true,
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
    ],
],

// .env
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

### Custom Slack Notification

```php
// app/Notifications/BackupSlackNotification.php
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

class BackupSlackNotification extends Notification
{
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->content('Backup completed successfully')
            ->attachment(function ($attachment) {
                $attachment
                    ->title('Backup Details')
                    ->fields([
                        'Application' => config('app.name'),
                        'Time' => now()->format('Y-m-d H:i:s'),
                        'Status' => 'Success',
                    ]);
            });
    }
}
```
