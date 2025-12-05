# Laravel Media Library - Advanced Reference

## Installation

```bash
composer require spatie/laravel-medialibrary
php artisan migrate
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
```

## Adding Media - All Methods

### From Files

```php
// From uploaded file
$article->addMedia($request->file('image'))
    ->toMediaCollection('featured');

// From file path
$article->addMedia('/path/to/image.jpg')
    ->toMediaCollection('gallery');

// From request with custom properties
$article->addMedia($request->file('document'))
    ->withCustomProperties(['author' => 'John Doe', 'category' => 'reports'])
    ->toMediaCollection('documents');

// Preserve original file (copy instead of move)
$article->copyMedia('/path/to/important.pdf')
    ->toMediaCollection('archives');

// Specify disk
$article->addMedia($request->file('video'))
    ->toMediaCollection('videos', 's3');
```

### From URLs

```php
// With mime type validation
$article->addMediaFromUrl('https://example.com/photo.jpg', ['image/jpeg', 'image/png'])
    ->toMediaCollection('images');

// With custom name
$article->addMediaFromUrl('https://example.com/document.pdf')
    ->usingName('Annual Report')
    ->usingFileName('report-2024.pdf')
    ->withCustomProperties(['year' => 2024])
    ->toMediaCollection('reports');

// Without validation
$article->addMediaFromUrl('https://example.com/file.zip')
    ->toMediaCollection('downloads');
```

### From Base64

```php
// From base64 string
$base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
$article->addMediaFromBase64($base64Image, ['image/png', 'image/jpeg'])
    ->usingFileName('encoded-image.png')
    ->toMediaCollection('images');

// From data URI
$dataUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
$article->addMediaFromBase64($dataUri)
    ->usingFileName('data-uri-image.png')
    ->toMediaCollection('images');
```

### From Strings and Streams

```php
// From string
$article->addMediaFromString('This is the content of my text file.')
    ->usingFileName('notes.txt')
    ->usingName('Article Notes')
    ->toMediaCollection('documents');

// From stream
$stream = fopen('https://example.com/video.mp4', 'r');
$article->addMediaFromStream($stream)
    ->usingFileName('stream-video.mp4')
    ->toMediaCollection('videos');
```

## Retrieving Media

### Basic Retrieval

```php
// Get all media from collection
$mediaItems = $article->getMedia('gallery');

// Get first media
$firstImage = $article->getFirstMedia('images');

// Get last media
$lastImage = $article->getLastMedia('images');

// Check if model has media
if ($article->hasMedia('gallery')) {
    $images = $article->getMedia('gallery');
}
```

### Media Properties

```php
$media = $article->getFirstMedia('images');

// Get media type
$type = $media->type; // 'image', 'pdf', 'video', 'audio', 'other'

// Get extension
$extension = $media->extension; // 'jpg', 'png', 'pdf', etc.

// Get mime type
$mimeType = $media->mime_type; // 'image/jpeg', 'application/pdf', etc.

// Get file size
$size = $media->size; // in bytes
$humanSize = $media->humanReadableSize; // '2.5 MB'

// Get disk driver
$driver = $media->getDiskDriverName(); // 'local', 's3', etc.

// Get conversion names
$conversions = $media->getMediaConversionNames();
```

## URL Generation

### Get URLs

```php
$media = $article->getFirstMedia('images');

// Public URL (relative or full depending on disk)
$url = $media->getUrl();
$fullUrl = $media->getFullUrl();

// URL for conversion
$thumbUrl = $media->getUrl('thumb');
$largeUrl = $media->getUrl('large');

// Temporary URL (for private S3 files)
$temporaryUrl = $media->getTemporaryUrl(now()->addMinutes(30));
$conversionTempUrl = $media->getTemporaryUrl(now()->addHours(1), 'thumb');

// First/Last media URL
$firstUrl = $article->getFirstMediaUrl('images');
$firstThumbUrl = $article->getFirstMediaUrl('images', 'thumb');
$lastUrl = $article->getLastMediaUrl('gallery');
```

## Getting Paths

```php
$media = $article->getFirstMedia('documents');

// Full path
$path = $media->getPath();

// Path for conversion
$thumbPath = $media->getPath('thumb');

// Relative path
$relativePath = $media->getPathRelativeToRoot();

// First/Last media path
$firstPath = $article->getFirstMediaPath('images');
$lastPath = $article->getLastMediaPath('images', 'thumb');
```

## Conversions API

### Check and Manage Conversions

```php
$media = $article->getFirstMedia('images');

// Check if conversion exists
if ($media->hasGeneratedConversion('thumb')) {
    $thumbUrl = $media->getUrl('thumb');
}

// Get all conversion names
$conversions = $media->getMediaConversionNames();
// Returns: ['thumb', 'large', 'square']

// Mark conversion as generated
$media->markAsConversionGenerated('thumb');

// Mark as not generated
$media->markAsConversionNotGenerated('thumb');

// Get available URL from list
$url = $media->getAvailableUrl(['large', 'medium', 'thumb']);
// Returns URL of first available conversion

// Get all generated conversions
$generatedConversions = $media->getGeneratedConversions();
```

### Advanced Conversions

```php
// Video thumbnail (extract frame at 5 seconds)
$this->addMediaConversion('video-thumb')
    ->extractVideoFrameAtSecond(5)
    ->width(640)
    ->performOnCollections('videos');

// PDF preview (extract first page)
$this->addMediaConversion('pdf-preview')
    ->pdfPageNumber(1)
    ->width(600)
    ->performOnCollections('documents');

// Conditional conversion
$this->addMediaConversion('watermarked')
    ->width(1200)
    ->watermark(public_path('watermark.png'))
    ->when($media?->collection_name === 'public-gallery');

// Format conversion
$this->addMediaConversion('webp')
    ->format('webp')
    ->quality(85);

// Keep original format
$this->addMediaConversion('large')
    ->width(1920)
    ->keepOriginalImageFormat();
```

## Responsive Images

### Setup Responsive Images

```php
// In collection
public function registerMediaCollections(): void
{
    $this->addMediaCollection('images')
        ->withResponsiveImages();
}

// In conversion
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('hero')
        ->width(1920)
        ->withResponsiveImages();
}

// During upload
$article->addMedia($request->file('image'))
    ->withResponsiveImages()
    ->toMediaCollection('images');
```

### Get Responsive URLs

```php
$media = $article->getFirstMedia('images');

// Get srcset attribute
$srcset = $media->getSrcset();
$conversionSrcset = $media->getSrcset('hero');

// Check if has responsive images
if ($media->hasResponsiveImages()) {
    echo "Has responsive variants";
}

// In Blade
<img src="{{ $media->getUrl() }}"
     srcset="{{ $media->getSrcset() }}"
     alt="{{ $media->name }}">
```

## Batch Operations

### Multiple Files from Request

```php
// Multiple files by specific keys
$fileAdders = $article->addMultipleMediaFromRequest(['image1', 'image2', 'image3']);
foreach ($fileAdders as $fileAdder) {
    $fileAdder->toMediaCollection('gallery');
}

// All files from request
$allFileAdders = $article->addAllMediaFromRequest();
foreach ($allFileAdders as $key => $fileAdder) {
    $fileAdder->toMediaCollection('uploads');
}

// Single file from request by key
$article->addMediaFromRequest('featured_image')
    ->withCustomProperties(['featured' => true])
    ->toMediaCollection('images');
```

### Update Media

```php
// Update media metadata
$media = Media::find(1);
$media->name = 'Updated Name';
$media->setCustomProperty('status', 'reviewed');
$media->save();

// Update collection (reorder, rename)
$newMediaArray = [
    ['id' => 1, 'name' => 'First Image', 'custom_properties' => ['order' => 1]],
    ['id' => 2, 'name' => 'Second Image', 'custom_properties' => ['order' => 2]],
];
$article->updateMedia($newMediaArray, 'gallery');

// Clear collection
$article->clearMediaCollection('gallery');

// Clear except specific items
$keepMedia = $article->getFirstMedia('gallery');
$article->clearMediaCollectionExcept('gallery', $keepMedia);
```

### Move and Copy Media

```php
// Move media to another model
$product = Product::find(1);
$media = $article->getFirstMedia('images');
$newMedia = $media->move($product, 'photos');

// Copy media to another model
$copiedMedia = $media->copy($product, 'photos');

// Copy with custom name
$copiedMedia = $media->copy($product, 'photos', 'public', 'custom-name.jpg');

// Copy with callback for custom properties
$copiedMedia = $media->copy($product, 'photos', '', '', function ($fileAdder) {
    return $fileAdder->withCustomProperties(['copied_at' => now()]);
});
```

## Deleting Media

```php
// Delete media (removes file and database record)
$media = Media::find(1);
$media->delete();

// Delete model preserving media
$article = Article::find(1);
$article->deletePreservingMedia();

// Force delete with soft deletes
$article->forceDelete(); // deletes media automatically

// Clear all media before deleting model
$article->clearMediaCollection();
$article->delete();
```

## Streaming and Download

### Response Types

```php
// Direct download
Route::get('/media/{media}/download', function (Media $media) {
    return $media->toResponse(request());
});

// Inline display (preview)
Route::get('/media/{media}/view', function (Media $media) {
    return $media->toInlineResponse(request());
});

// Custom chunk size for large files
Route::get('/media/{media}/stream', function (Media $media) {
    return $media->setStreamChunkSize(2 * 1024 * 1024) // 2MB chunks
        ->toResponse(request());
});
```

## ZIP Downloads

### Create ZIP Archives

```php
use Spatie\MediaLibrary\Support\MediaStream;

// Single collection
Route::get('/article/{article}/photos.zip', function (Article $article) {
    return MediaStream::create('photos.zip')
        ->addMedia($article->getMedia('photos'));
});

// Multiple collections with folder structure
Route::get('/article/{article}/all-media.zip', function (Article $article) {
    // Mark collections for folder structure
    $article->getMedia('documents')->each(function ($media) {
        $media->setCustomProperty('zip_filename_prefix', 'documents/');
        $media->save();
    });
    
    $article->getMedia('images')->each(function ($media) {
        $media->setCustomProperty('zip_filename_prefix', 'images/');
        $media->save();
    });
    
    return MediaStream::create('article-media.zip')
        ->addMedia($article->getMedia('documents'))
        ->addMedia($article->getMedia('images'));
});

// With custom ZIP options
return MediaStream::create('archive.zip')
    ->useZipOptions(function (&$options) {
        $options['comment'] = 'Generated by Laravel Media Library';
    })
    ->addMedia($article->getMedia('files'));
```

## Email Attachments

```php
use Illuminate\Mail\Mailable;

class InvoiceMail extends Mailable
{
    public function __construct(public Media $invoice)
    {
    }
    
    public function build()
    {
        return $this->view('emails.invoice')
            ->attach($this->invoice->toMailAttachment());
    }
}

// With conversion
public function build()
{
    return $this->view('emails.report')
        ->attach($media->mailAttachment('pdf-preview'));
}

// Multiple attachments
class ReportMail extends Mailable
{
    public function __construct(public Article $article)
    {
    }
    
    public function build()
    {
        $mail = $this->view('emails.report');
        foreach ($this->article->getMedia('documents') as $media) {
            $mail->attach($media->toMailAttachment());
        }
        return $mail;
    }
}
```

## Rendering HTML

```php
$media = $article->getFirstMedia('images');

// Basic img tag
echo $media->img();
// Output: <img src="/storage/1/image.jpg" alt="image">

// With conversion
echo $media->img('thumb');
// Output: <img src="/storage/1/conversions/image-thumb.jpg" alt="image">

// With custom attributes
echo $media->img('thumb', ['class' => 'img-fluid rounded', 'id' => 'hero-image']);

// Using __invoke
echo $media('large', ['loading' => 'lazy']);

// In Blade (auto-escaped)
{!! $media->img('thumb', ['class' => 'thumbnail']) !!}

// Render as Htmlable
{{ $media }} {{-- Renders as img tag --}}
```

## Advanced Filtering

```php
// Filter by custom property
$featuredMedia = $article->getMedia('images', function ($media) {
    return $media->getCustomProperty('featured') === true;
});

// Filter by mime type
$pdfDocuments = $article->getMedia('documents', function ($media) {
    return $media->mime_type === 'application/pdf';
});

// Filter by size
$largeFiles = $article->getMedia('files', function ($media) {
    return $media->size > 1000000; // larger than 1MB
});

// Filter by date
$recentMedia = $article->getMedia('images', function ($media) {
    return $media->created_at->isAfter(now()->subDays(7));
});

// Complex filtering
$filteredMedia = $article->getMedia('gallery', function ($media) {
    return $media->mime_type === 'image/jpeg'
        && $media->size < 5000000
        && $media->hasCustomProperty('approved')
        && $media->getCustomProperty('approved') === true
        && $media->created_at->isAfter(now()->subMonths(3));
});
```

## Configuration

### config/media-library.php

```php
return [
    // Storage configuration
    'disk_name' => env('MEDIA_DISK', 'public'),
    'max_file_size' => 1024 * 1024 * 50, // 50MB
    
    // Queue configuration
    'queue_connection_name' => 'redis',
    'queue_name' => 'media-conversions',
    'queue_conversions_by_default' => true,
    
    // Customization
    'path_generator' => App\MediaLibrary\CustomPathGenerator::class,
    'url_generator' => App\MediaLibrary\CdnUrlGenerator::class,
    'file_namer' => App\MediaLibrary\CustomFileNamer::class,
    
    // Image processing
    'image_driver' => 'imagick', // gd or imagick
    'image_optimizers' => [
        Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '-m80',
            '--strip-all',
            '--all-progressive',
        ],
    ],
    
    // Temporary URLs
    'temporary_url_default_lifetime' => 60, // 1 hour
    
    // Storage disks configuration for remote files
    'remote' => [
        'extra_headers' => [
            'CacheControl' => 'max-age=31536000, public',
        ],
    ],
];
```

## Custom Path Generators

```php
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $media->model_type . '/' . $media->model_id . '/';
    }
    
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }
    
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}

// Register in config
'path_generator' => CustomPathGenerator::class,
```

## Custom URL Generators

```php
use Spatie\MediaLibrary\Support\UrlGenerator\BaseUrlGenerator;

class CustomUrlGenerator extends BaseUrlGenerator
{
    public function getUrl(): string
    {
        $path = $this->getPathRelativeToRoot();
        return 'https://cdn.example.com/' . $path;
    }
}

// Register in config
'url_generator' => CustomUrlGenerator::class,
```

## Custom File Namers

```php
use Spatie\MediaLibrary\Support\FileNamer\FileNamer;
use Spatie\MediaLibrary\Conversions\Conversion;

class CustomFileNamer extends FileNamer
{
    public function originalFileName(string $fileName): string
    {
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        return $name . '-' . time();
    }
    
    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        return $name . '-' . $conversion->getName();
    }
    
    public function responsiveFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }
}

// Register in config
'file_namer' => CustomFileNamer::class,
```

## Queuing Conversions

```php
// Queue configuration in config
'queue_conversions_by_default' => true,
'queue_name' => 'media-conversions',

// Non-queued conversion
$this->addMediaConversion('thumb')
    ->width(150)
    ->nonQueued();

// Queued conversion
$this->addMediaConversion('large')
    ->width(2000)
    ->queued();

// Custom queue during upload
$article->addMedia($request->file('image'))
    ->onQueue('image-processing')
    ->toMediaCollection('images');
```

## Regenerate Conversions Command

```bash
# All conversions for all models
php artisan media-library:regenerate

# For specific model
php artisan media-library:regenerate --model="App\Models\Article"

# For specific IDs
php artisan media-library:regenerate --model="App\Models\Article" --ids=1,2,3

# Only specific conversions
php artisan media-library:regenerate --only=thumb,large

# Force regeneration even if exists
php artisan media-library:regenerate --force
```
