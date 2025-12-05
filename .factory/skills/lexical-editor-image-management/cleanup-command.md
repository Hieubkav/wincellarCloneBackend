# Image Cleanup Command Implementation

Create a console command to clean up unused images from storage and database.

## Full Command Class

```php
<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\ServicePost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;

class ImagesCleanUnused extends Command
{
    protected $signature = 'images:clean-unused {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Clean up unused images from storage';

    public function handle()
    {
        $this->info('🔍 Scanning for unused images...');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - Files will NOT be deleted');
        }

        // Get all used images
        $usedImages = $this->getUsedImages();
        $this->info("📊 Found {$usedImages['count']} images in use in database");

        // Get all files in storage
        $storageFiles = $this->getStorageFiles();
        $this->info("📦 Found {$storageFiles['count']} files in storage");

        // Find unused files
        $unusedFiles = array_diff($storageFiles['files'], $usedImages['files']);
        
        if (empty($unusedFiles)) {
            $this->info("✅ No unused images found!");
            return Command::SUCCESS;
        }

        $this->warn("🗑️  Found " . count($unusedFiles) . " unused files");
        
        // Show list of files to delete
        $this->showFilesList($unusedFiles, $storageFiles['sizes']);

        // Confirm deletion
        if ($dryRun || $this->confirm('Do you want to delete these files?')) {
            $this->deleteFiles($unusedFiles, $dryRun);
        }

        return Command::SUCCESS;
    }

    /**
     * Get all image references from database
     */
    private function getUsedImages(): array
    {
        $images = [];

        // Get images from posts table
        $posts = Post::whereNotNull('image')
            ->orWhereNotNull('pdf')
            ->orWhereNotNull('content')
            ->get();

        foreach ($posts as $post) {
            // Main image
            if ($post->image) {
                $images[] = $post->image;
            }

            // PDF
            if ($post->pdf) {
                $images[] = $post->pdf;
            }

            // Content images
            if ($post->content) {
                $contentImages = $this->extractImagesFromContent($post->content);
                $images = array_merge($images, $contentImages);
            }
        }

        // Get images from service_posts table
        $servicePosts = ServicePost::whereNotNull('image')
            ->orWhereNotNull('pdf')
            ->orWhereNotNull('content')
            ->get();

        foreach ($servicePosts as $servicePost) {
            if ($servicePost->image) {
                $images[] = $servicePost->image;
            }

            if ($servicePost->pdf) {
                $images[] = $servicePost->pdf;
            }

            if ($servicePost->content) {
                $contentImages = $this->extractImagesFromContent($servicePost->content);
                $images = array_merge($images, $contentImages);
            }
        }

        // Normalize paths
        $images = array_map(function ($image) {
            return $this->normalizePath($image);
        }, $images);

        return [
            'count' => count(array_unique($images)),
            'files' => array_unique($images),
        ];
    }

    /**
     * Get all files in uploads directory
     */
    private function getStorageFiles(): array
    {
        $files = [];
        $sizes = [];

        $uploadsPath = storage_path('app/public/uploads');

        if (!File::exists($uploadsPath)) {
            return ['count' => 0, 'files' => [], 'sizes' => []];
        }

        // Use File::allFiles to recursively get all files
        $allFiles = File::allFiles($uploadsPath);

        foreach ($allFiles as $file) {
            // Get relative path from storage/app/public
            $relativePath = 'uploads/' . str_replace(
                $uploadsPath . DIRECTORY_SEPARATOR,
                '',
                $file->getRealPath()
            );
            
            // Normalize to forward slashes
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $files[] = $relativePath;
            $sizes[$relativePath] = $file->getSize();
        }

        return [
            'count' => count($files),
            'files' => $files,
            'sizes' => $sizes,
        ];
    }

    /**
     * Extract image paths from HTML content
     */
    private function extractImagesFromContent(string $content): array
    {
        $images = [];

        // Find img src attributes
        preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $src) {
                $path = $this->getRelativePathFromUrl($src);
                if ($path) {
                    $images[] = $path;
                }
            }
        }

        // Find data-url attributes
        preg_match_all('/data-url=[\'"]([^\'"]+)[\'"]/', $content, $dataMatches);

        if (!empty($dataMatches[1])) {
            foreach ($dataMatches[1] as $src) {
                $path = $this->getRelativePathFromUrl($src);
                if ($path) {
                    $images[] = $path;
                }
            }
        }

        return array_unique($images);
    }

    /**
     * Convert URL to relative path
     */
    private function getRelativePathFromUrl(string $url): ?string
    {
        // Remove domain
        $url = str_replace(config('app.url'), '', $url);
        $url = str_replace(url('/'), '', $url);

        // Remove /storage/ prefix
        $url = preg_replace('/^\/storage\//', '', $url);
        $url = preg_replace('/^storage\//', '', $url);

        // Keep only uploads paths
        if (str_contains($url, 'uploads/')) {
            if (preg_match('/uploads\/.*/', $url, $matches)) {
                return $matches[0];
            }
        }

        return null;
    }

    /**
     * Normalize file paths
     */
    private function normalizePath(string $path): string
    {
        // Remove leading/trailing slashes
        $path = trim($path, '/');

        // Convert backslashes to forward slashes
        $path = str_replace('\\', '/', $path);

        // Remove /storage/ prefix if present
        $path = preg_replace('/^storage\//', '', $path);

        return $path;
    }

    /**
     * Display files list
     */
    private function showFilesList(array $files, array $sizes): void
    {
        $this->line("\n📋 Files to delete:");

        $totalSize = 0;
        foreach ($files as $file) {
            $size = $sizes[$file] ?? 0;
            $sizeFormatted = $this->formatBytes($size);
            $totalSize += $size;

            $this->line("  • {$file} ({$sizeFormatted})");
        }

        $this->info("\n💾 Total size: " . $this->formatBytes($totalSize));
        $this->line('');
    }

    /**
     * Delete unused files
     */
    private function deleteFiles(array $files, bool $dryRun): void
    {
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $deletedCount = 0;
        $failedCount = 0;
        $totalSize = 0;

        foreach ($files as $file) {
            try {
                $fullPath = 'uploads/' . ltrim($file, 'uploads/');

                if (!$dryRun) {
                    // Get file size before deletion
                    if (Storage::disk('public')->exists($fullPath)) {
                        $totalSize += Storage::disk('public')->size($fullPath);
                        Storage::disk('public')->delete($fullPath);
                    }
                } else {
                    // In dry-run, still calculate size
                    if (Storage::disk('public')->exists($fullPath)) {
                        $totalSize += Storage::disk('public')->size($fullPath);
                    }
                }

                $deletedCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("\n❌ Error deleting {$file}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        if ($dryRun) {
            $this->info("✅ DRY RUN COMPLETE - No files were actually deleted");
        } else {
            $this->info("✅ Successfully deleted {$deletedCount} files (" . $this->formatBytes($totalSize) . ")");
        }

        if ($failedCount > 0) {
            $this->warn("⚠️  Failed to delete {$failedCount} files");
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
```

## Installation Steps

### 1. Create Command File

```bash
php artisan make:command ImagesCleanUnused
```

Paste the code above into `app/Console/Commands/ImagesCleanUnused.php`.

### 2. Run the Command

#### Dry Run (see what would be deleted)

```bash
php artisan images:clean-unused --dry-run
```

Output:
```
🔍 Scanning for unused images...
⚠️  DRY RUN MODE - Files will NOT be deleted
📊 Found 45 images in use in database
📦 Found 78 files in storage
🗑️  Found 33 unused files

📋 Files to delete:
  • uploads/old-image-1.jpg (2.5 MB)
  • uploads/old-image-2.png (1.2 MB)
  • uploads/content/unused-1.jpg (500 KB)
  ...

💾 Total size: 15.5 MB
```

#### Actually Delete

```bash
php artisan images:clean-unused
```

Then confirm:
```
Do you want to delete these files? (yes/no): yes

[████████████████████████████] 100%

✅ Successfully deleted 33 files (15.5 MB)
```

## Schedule as Cron Job

### Register in Kernel

Edit `app/Console/Kernel.php`:

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Run every Sunday at 2 AM
        $schedule->command('images:clean-unused')
            ->weekly()
            ->sundays()
            ->at('02:00')
            ->emailOutputOnFailure('admin@example.com');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
```

### Verify Cron Job

```bash
# Test the scheduled command
php artisan schedule:work

# In another terminal
php artisan schedule:run
```

## Advanced Usage

### Custom Model Support

Extend the command to support custom models:

```php
private function getUsedImages(): array
{
    $images = [];
    
    // Get default models
    $images = array_merge($images, $this->getImagesFromModel(Post::class));
    $images = array_merge($images, $this->getImagesFromModel(ServicePost::class));
    
    // Add custom models
    // $images = array_merge($images, $this->getImagesFromModel(CustomModel::class));
    
    return ['count' => count(array_unique($images)), 'files' => array_unique($images)];
}

private function getImagesFromModel(string $modelClass): array
{
    $images = [];
    
    $records = $modelClass::all();
    foreach ($records as $record) {
        // Check image field
        if (isset($record->image) && $record->image) {
            $images[] = $record->image;
        }
        
        // Check content field
        if (isset($record->content) && $record->content) {
            $images = array_merge(
                $images,
                $this->extractImagesFromContent($record->content)
            );
        }
    }
    
    return $images;
}
```

### Export Report

Add option to export deletion report:

```php
protected $signature = 'images:clean-unused {--dry-run} {--report= : Export report to file}';

private function deleteFiles(array $files, bool $dryRun): void
{
    // ... existing code ...
    
    if ($this->option('report')) {
        $this->exportReport($files, $dryRun, $this->option('report'));
    }
}

private function exportReport(array $files, bool $dryRun, string $filename): void
{
    $content = "Image Cleanup Report\n";
    $content .= "Generated: " . now() . "\n";
    $content .= "Dry Run: " . ($dryRun ? 'Yes' : 'No') . "\n\n";
    $content .= "Files to delete:\n";
    
    foreach ($files as $file) {
        $content .= "- {$file}\n";
    }
    
    file_put_contents($filename, $content);
    $this->info("Report saved to: {$filename}");
}
```

### Filter by Directory

Add option to clean specific directory:

```php
protected $signature = 'images:clean-unused {--dry-run} {--path=uploads/content : Directory to scan}';

private function getStorageFiles(): array
{
    $files = [];
    $path = $this->option('path') ?? 'uploads';
    $uploadsPath = storage_path('app/public/' . $path);
    
    // ... rest of code ...
}
```

Usage:
```bash
# Clean only content images
php artisan images:clean-unused --path=uploads/content --dry-run
```

## Testing

### Unit Test

```php
<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagesCleanUnusedTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_identifies_unused_images()
    {
        Storage::fake('public');

        // Create post with image
        $post = Post::create([
            'name' => 'Test',
            'image' => 'used-image.jpg',
            'content' => '<img src="/storage/uploads/content/used.jpg">',
        ]);

        // Create unused files
        Storage::disk('public')->put('unused-image.jpg', 'content');
        Storage::disk('public')->put('uploads/content/unused.jpg', 'content');

        // Run command
        $this->artisan('images:clean-unused --dry-run')
            ->assertSuccessful()
            ->expectsOutput('Found 2 unused files');
    }

    public function test_command_deletes_unused_images()
    {
        Storage::fake('public');

        $post = Post::create(['name' => 'Test', 'image' => 'used.jpg']);
        Storage::disk('public')->put('used.jpg', 'content');
        Storage::disk('public')->put('unused.jpg', 'content');

        $this->artisan('images:clean-unused')
            ->expectsConfirmation('Do you want to delete these files?', 'yes');

        Storage::disk('public')->assertMissing('unused.jpg');
        Storage::disk('public')->assertExists('used.jpg');
    }
}
```

Run test:
```bash
php artisan test tests/Feature/ImagesCleanUnusedTest.php
```

## Troubleshooting

### Command Not Found

```bash
# Clear cache
php artisan optimize:clear

# List all commands
php artisan list
```

### Permission Denied

```bash
# Ensure proper permissions
chmod 755 storage/app/public/uploads
chmod 644 storage/app/public/uploads/*
```

### Files Not Deleting

```bash
# Check if files exist
ls -la storage/app/public/uploads

# Try deleting manually
rm -rf storage/app/public/uploads/filename.jpg

# Check logs
tail -f storage/logs/laravel.log
```

## Monitoring

### Create a Scheduled Report

```php
// In Kernel.php
$schedule->command('images:clean-unused --dry-run --report=cleanup-report.txt')
    ->weekly()
    ->mondays()
    ->at('09:00')
    ->emailOutputOnFailure('admin@example.com');
```

### Monitor with Slack Notification

```php
$schedule->command('images:clean-unused')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->then(function () {
        \Illuminate\Support\Facades\Notification::route('slack', config('services.slack.webhook'))
            ->notify(new CleanupCompleted());
    });
```

## Best Practices

1. **Always test with --dry-run first**
   ```bash
   php artisan images:clean-unused --dry-run
   ```

2. **Backup storage before cleanup**
   ```bash
   cp -r storage/app/public/uploads storage/app/public/uploads_backup_$(date +%Y%m%d)
   ```

3. **Schedule during low-traffic hours** (e.g., 2 AM on Sunday)

4. **Monitor logs after running**
   ```bash
   tail -f storage/logs/laravel.log | grep "images:clean"
   ```

5. **Document deletion in notes**
   - Why was cleanup needed
   - How much space was freed
   - Any issues encountered
