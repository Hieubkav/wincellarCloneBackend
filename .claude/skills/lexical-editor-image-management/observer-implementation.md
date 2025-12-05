# Observer Implementation Guide

Complete step-by-step implementation of the Observer pattern for Lexical Editor image management.

## Full Observer Class Template

```php
<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PostObserver
{
    /**
     * Handle the Post "creating" event.
     * Auto-generate slug from post name
     */
    public function creating(Post $post): void
    {
        if (empty($post->slug)) {
            $post->slug = \Str::slug($post->name);
        }
    }

    /**
     * Handle the Post "saving" event.
     * Convert base64 images in content to storage files
     */
    public function saving(Post $post): void
    {
        if ($post->content) {
            $post->content = $this->convertBase64ToStorage($post->content);
        }
    }

    /**
     * Handle the Post "updating" event.
     * Delete old files when new ones are uploaded
     */
    public function updating(Post $post): void
    {
        $oldPost = Post::find($post->id);
        
        if (!$oldPost) {
            return;
        }

        // Handle main image replacement
        if ($post->image !== $oldPost->image) {
            $this->deleteOldImage($oldPost->image);
            Log::info("Deleted old image for Post ID {$post->id}: {$oldPost->image}");
        }

        // Handle PDF replacement
        if ($post->pdf !== $oldPost->pdf) {
            $this->deleteOldImage($oldPost->pdf);
            Log::info("Deleted old PDF for Post ID {$post->id}: {$oldPost->pdf}");
        }

        // Handle content images
        $this->handleContentImages($oldPost->content, $post->content);
    }

    /**
     * Handle the Post "deleted" event.
     * Delete all associated files
     */
    public function deleted(Post $post): void
    {
        if ($post->image) {
            $this->deleteOldImage($post->image);
            Log::info("Deleted image for deleted Post ID {$post->id}: {$post->image}");
        }

        if ($post->pdf) {
            $this->deleteOldImage($post->pdf);
            Log::info("Deleted PDF for deleted Post ID {$post->id}: {$post->pdf}");
        }

        $this->deleteContentImages($post->content);
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        Log::info("Post ID {$post->id} has been restored");
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        $this->deleted($post);
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Delete old file from storage
     */
    private function deleteOldImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info("Successfully deleted file: {$path}");
            } else {
                Log::warning("File not found for deletion: {$path}");
            }
        } catch (\Exception $e) {
            Log::error("Error deleting file {$path}: " . $e->getMessage());
        }
    }

    /**
     * Handle content images on update
     * Compare old and new content to delete unused images
     */
    private function handleContentImages(?string $oldContent, ?string $newContent): void
    {
        if (!$oldContent) {
            return;
        }

        $oldImages = $this->extractImagesFromContent($oldContent);
        $newImages = $this->extractImagesFromContent($newContent ?? '');
        
        $imagesToDelete = array_diff($oldImages, $newImages);
        
        foreach ($imagesToDelete as $image) {
            // Only delete from editor content directory
            if (str_contains($image, 'uploads/content/')) {
                $this->deleteOldImage($image);
                Log::info("Deleted unused content image: {$image}");
            }
        }
    }

    /**
     * Delete all images in content when deleting post
     */
    private function deleteContentImages(?string $content): void
    {
        if (!$content) {
            return;
        }

        $images = $this->extractImagesFromContent($content);
        
        foreach ($images as $image) {
            if (str_contains($image, 'uploads/content/')) {
                $this->deleteOldImage($image);
                Log::info("Deleted content image: {$image}");
            }
        }
    }

    /**
     * Extract all image paths from HTML content
     * Supports both img src and data-url attributes
     */
    private function extractImagesFromContent(string $content): array
    {
        $images = [];
        
        // Find all img tags with src
        preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $src) {
                $path = $this->getRelativePathFromUrl($src);
                if ($path) {
                    $images[] = $path;
                }
            }
        }

        // Find images in data-url attributes (for some editors)
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
     * Convert full URL to relative storage path
     * Removes domain, /storage/ prefix, and other URL parts
     */
    private function getRelativePathFromUrl(string $url): ?string
    {
        // Remove domain
        $url = str_replace(config('app.url'), '', $url);
        $url = str_replace(url('/'), '', $url);
        
        // Remove /storage/ prefix
        $url = preg_replace('/^\/storage\//', '', $url);
        $url = preg_replace('/^storage\//', '', $url);
        
        // Only process uploads/ directory
        if (str_contains($url, 'uploads/')) {
            if (preg_match('/uploads\/.*/', $url, $matches)) {
                return $matches[0];
            }
        }
        
        return null;
    }

    /**
     * Convert all base64 images in content to storage files
     * Pattern: data:image/{type};base64,{data}
     */
    private function convertBase64ToStorage(string $content): string
    {
        preg_match_all(
            '/data:image\/(png|jpg|jpeg|gif|webp|svg\+xml);base64,([A-Za-z0-9+\/=]+)/i',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        
        if (empty($matches)) {
            return $content;
        }

        $convertedCount = 0;
        
        foreach ($matches as $match) {
            $fullBase64 = $match[0];
            $extension = $match[1];
            $base64Data = $match[2];
            
            // Handle special extensions
            if ($extension === 'svg+xml') {
                $extension = 'svg';
            }
            
            try {
                $filePath = $this->saveBase64AsFile($base64Data, $extension);
                $fileUrl = Storage::disk('public')->url($filePath);
                $content = str_replace($fullBase64, $fileUrl, $content);
                
                $convertedCount++;
                Log::info("Converted base64 image to storage: {$filePath}");
                
            } catch (\Exception $e) {
                Log::error("Failed to convert base64 image: " . $e->getMessage());
                continue;
            }
        }
        
        if ($convertedCount > 0) {
            Log::info("Successfully converted {$convertedCount} base64 images to storage");
        }
        
        return $content;
    }

    /**
     * Save base64 data as actual file in storage
     */
    private function saveBase64AsFile(string $base64Data, string $extension): string
    {
        $imageData = base64_decode($base64Data);
        
        if ($imageData === false) {
            throw new \Exception("Failed to decode base64 data");
        }
        
        // Create unique filename
        $filename = 'lexical-' . time() . '-' . uniqid() . '.' . $extension;
        $path = 'uploads/content/' . $filename;
        
        // Create directory if needed
        $directory = dirname($path);
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        // Save file
        $saved = Storage::disk('public')->put($path, $imageData);
        
        if (!$saved) {
            throw new \Exception("Failed to save file to storage");
        }
        
        return $path;
    }
}
```

## Step-by-Step Integration

### 1. Create Observer Class

```bash
php artisan make:observer PostObserver --model=Post
```

Copy the full observer code above into `app/Observers/PostObserver.php`.

### 2. Register Observer in EventServiceProvider

```php
// app/Providers/EventServiceProvider.php

namespace App\Providers;

use App\Models\Post;
use App\Observers\PostObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Post::observe(PostObserver::class);
    }
}
```

### 3. Register EventServiceProvider in config/app.php

```php
'providers' => [
    // ...
    App\Providers\EventServiceProvider::class,
],
```

### 4. Verify Installation

Test the observer:

```bash
php artisan tinker

# Create a test post with base64 image
$post = App\Models\Post::create([
    'name' => 'Test Post',
    'content' => '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA...">',
]);

# Check if image was converted
dd($post->content);
# Should show: <img src="/storage/uploads/content/lexical-xxx.png">

# Verify file exists
Storage::disk('public')->exists('uploads/content/lexical-xxx.png')
# Should return: true
```

## Customization Options

### Custom Storage Path

For different models, use different directories:

```php
// For ServicePost model
private function saveBase64AsFile(string $base64Data, string $extension): string
{
    // ... existing code ...
    $path = 'uploads/service-content/' . $filename; // Different path
    // ... rest of code ...
}
```

### File Size Validation

Add validation before saving:

```php
private function saveBase64AsFile(string $base64Data, string $extension): string
{
    $imageData = base64_decode($base64Data);
    
    if ($imageData === false) {
        throw new \Exception("Failed to decode base64 data");
    }
    
    // Add file size check (max 5MB)
    $maxSize = 5 * 1024 * 1024;
    if (strlen($imageData) > $maxSize) {
        throw new \Exception("Image exceeds maximum size of 5MB");
    }
    
    // ... rest of code ...
}
```

### Image Optimization

Resize images on the fly:

```php
use Intervention\Image\Facades\Image;

private function saveBase64AsFile(string $base64Data, string $extension): string
{
    $imageData = base64_decode($base64Data);
    
    if ($imageData === false) {
        throw new \Exception("Failed to decode base64 data");
    }
    
    // Optimize image
    $image = Image::make($imageData);
    
    // Resize if too large
    if ($image->width() > 1920) {
        $image->resize(1920, null, ['aspect_ratio' => true]);
    }
    
    // Compress
    $imageData = $image->encode('jpeg', 75)->__toString();
    
    // Save optimized image
    $filename = 'lexical-' . time() . '-' . uniqid() . '.jpg';
    $path = 'uploads/content/' . $filename;
    
    // ... rest of code ...
}
```

### Custom Filename Pattern

Use domain-specific naming:

```php
private function saveBase64AsFile(string $base64Data, string $extension): string
{
    // ... existing validation code ...
    
    // Custom filename with more context
    $context = now()->format('Y-m-d'); // YYYY-MM-DD
    $filename = "lexical-{$context}-" . uniqid() . ".{$extension}";
    
    // Group by date
    $path = 'uploads/content/' . now()->format('Y/m/d') . '/' . $filename;
    
    // ... rest of code ...
}
```

## Event Lifecycle Overview

```
┌─────────────────────────────────────────────────┐
│  1. CREATING Event (before insert)               │
│  - Auto-generate slug if empty                   │
│  - Set default values                            │
└─────────────────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────┐
│  2. SAVING Event (before insert/update)          │
│  - Convert base64 images to storage files        │
│  - Validate content                              │
│  - Process relationships                         │
└─────────────────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────┐
│  Database UPDATE/INSERT                          │
│  - Record saved to database                      │
└─────────────────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────┐
│  3. UPDATING Event (before update)               │
│  - Compare old vs new values                     │
│  - Delete replaced images                        │
│  - Delete unused content images                  │
└─────────────────────────────────────────────────┘
                      ▼
┌─────────────────────────────────────────────────┐
│  4. DELETED Event (after deletion)               │
│  - Delete all associated files                   │
│  - Clean up relationships                        │
│  - Log cleanup operations                        │
└─────────────────────────────────────────────────┘
```

## Testing the Observer

### Unit Test

```php
<?php

namespace Tests\Unit;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_base64_image_converts_to_file()
    {
        Storage::fake('public');
        
        $post = Post::create([
            'name' => 'Test Post',
            'content' => '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA..." alt="test">',
        ]);
        
        // Content should no longer have base64
        $this->assertStringNotContainsString('data:image', $post->content);
        
        // Content should have file URL
        $this->assertStringContainsString('/storage/uploads/content/', $post->content);
        
        // File should exist in storage
        Storage::disk('public')->assertExists('uploads/content/lexical-*');
    }

    public function test_old_image_deleted_on_update()
    {
        Storage::fake('public');
        
        $post = Post::create([
            'name' => 'Test',
            'image' => 'old-image.jpg',
        ]);
        
        Storage::disk('public')->put('old-image.jpg', 'dummy content');
        
        $post->update(['image' => 'new-image.jpg']);
        
        Storage::disk('public')->assertMissing('old-image.jpg');
    }

    public function test_unused_content_images_deleted_on_update()
    {
        Storage::fake('public');
        
        $post = Post::create([
            'name' => 'Test',
            'content' => '<img src="/storage/uploads/content/image1.jpg">
                          <img src="/storage/uploads/content/image2.jpg">',
        ]);
        
        Storage::disk('public')->put('uploads/content/image1.jpg', 'content');
        Storage::disk('public')->put('uploads/content/image2.jpg', 'content');
        
        $post->update([
            'content' => '<img src="/storage/uploads/content/image1.jpg">',
        ]);
        
        Storage::disk('public')->assertMissing('uploads/content/image2.jpg');
        Storage::disk('public')->assertExists('uploads/content/image1.jpg');
    }

    public function test_all_images_deleted_on_post_deletion()
    {
        Storage::fake('public');
        
        $post = Post::create([
            'name' => 'Test',
            'image' => 'main-image.jpg',
            'pdf' => 'document.pdf',
            'content' => '<img src="/storage/uploads/content/content-image.jpg">',
        ]);
        
        Storage::disk('public')->put('main-image.jpg', 'content');
        Storage::disk('public')->put('document.pdf', 'pdf content');
        Storage::disk('public')->put('uploads/content/content-image.jpg', 'content');
        
        $post->delete();
        
        Storage::disk('public')->assertMissing('main-image.jpg');
        Storage::disk('public')->assertMissing('document.pdf');
        Storage::disk('public')->assertMissing('uploads/content/content-image.jpg');
    }
}
```

### Run Tests

```bash
php artisan test tests/Unit/PostObserverTest.php
```

## Troubleshooting

### Observer Not Running

```bash
# Clear cache
php artisan optimize:clear

# Verify observer is registered
php artisan tinker
>>> App\Models\Post::getObservableEvents()
```

### Images Not Converting

```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
chmod -R 775 storage/app/public

# Test manually
php artisan tinker
>>> $post = new App\Models\Post(['content' => '<img src="data:image/png;base64,iVBORw0...">']);
>>> $post->save();
>>> dd($post->content);
```

### Database Issues

```bash
# Verify migration
php artisan migrate:status

# Verify columns exist
php artisan tinker
>>> App\Models\Post::find(1)->getAttributes()
```
