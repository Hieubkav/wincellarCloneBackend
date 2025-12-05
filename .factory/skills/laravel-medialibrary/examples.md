# Laravel Media Library - Examples

## E-Commerce Product Gallery

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    protected $fillable = ['name', 'description', 'price'];
    
    public function registerMediaCollections(): void
    {
        // Single thumbnail for product listings
        $this->addMediaCollection('thumbnail')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        
        // Main gallery images with responsive variants
        $this->addMediaCollection('gallery')
            ->onlyKeepLatest(10)
            ->withResponsiveImages();
        
        // High-res images for admin
        $this->addMediaCollection('originals');
    }
    
    public function registerMediaConversions(?Media $media = null): void
    {
        // Thumbnail for listings
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->performOnCollections('thumbnail', 'gallery');
        
        // Medium for product pages
        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->performOnCollections('gallery');
        
        // Large for lightbox view
        $this->addMediaConversion('large')
            ->width(1200)
            ->performOnCollections('gallery')
            ->withResponsiveImages();
        
        // WebP format for performance
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(85)
            ->performOnCollections('gallery');
    }
}

// Usage in controller
class ProductController extends Controller
{
    public function store(Request $request)
    {
        $product = Product::create($request->validated());
        
        // Upload thumbnail
        if ($request->hasFile('thumbnail')) {
            $product->addMedia($request->file('thumbnail'))
                ->toMediaCollection('thumbnail');
        }
        
        // Upload multiple gallery images
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $product->addMedia($image)
                    ->withCustomProperties(['order' => 0])
                    ->toMediaCollection('gallery');
            }
        }
        
        return redirect()->route('products.show', $product);
    }
    
    public function show(Product $product)
    {
        return view('products.show', [
            'product' => $product,
            'thumbnail' => $product->getFirstMediaUrl('thumbnail', 'thumb'),
            'gallery' => $product->getMedia('gallery'),
        ]);
    }
}

// Blade template
<div class="product-gallery">
    <!-- Thumbnail -->
    @if($product->hasMedia('thumbnail'))
        <div class="thumbnail">
            {{ $product->getFirstMedia('thumbnail')->img('thumb', ['class' => 'w-full']) }}
        </div>
    @endif
    
    <!-- Gallery -->
    <div class="gallery-grid">
        @foreach($product->getMedia('gallery') as $photo)
            <img src="{{ $photo->getUrl('medium') }}"
                 srcset="{{ $photo->getSrcset('large') }}"
                 alt="{{ $photo->name }}"
                 loading="lazy"
                 class="gallery-image">
        @endforeach
    </div>
</div>
```

## Blog Article with Multiple Media Types

```php
class Article extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    protected $fillable = ['title', 'content', 'slug'];
    
    public function registerMediaCollections(): void
    {
        // Featured image for article header
        $this->addMediaCollection('featured')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        
        // Images within article content
        $this->addMediaCollection('body_images')
            ->withResponsiveImages();
        
        // PDF documents for download
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf']);
        
        // Video embeds
        $this->addMediaCollection('videos')
            ->acceptsMimeTypes(['video/mp4', 'video/webm']);
    }
    
    public function registerMediaConversions(?Media $media = null): void
    {
        // Featured image conversions
        $this->addMediaConversion('featured_thumb')
            ->width(400)
            ->height(250)
            ->crop('crop-center')
            ->format('webp')
            ->performOnCollections('featured');
        
        $this->addMediaConversion('featured_large')
            ->width(1200)
            ->height(600)
            ->crop('crop-center')
            ->performOnCollections('featured')
            ->withResponsiveImages();
        
        // Body image conversions
        $this->addMediaConversion('body_medium')
            ->width(800)
            ->performOnCollections('body_images')
            ->withResponsiveImages();
        
        // PDF preview (first page)
        $this->addMediaConversion('pdf_preview')
            ->pdfPageNumber(1)
            ->width(600)
            ->height(850)
            ->performOnCollections('documents');
    }
    
    // Get featured image URL or fallback
    public function getFeaturedImageUrl($conversion = null)
    {
        if (!$this->hasMedia('featured')) {
            return '/images/default-featured.jpg';
        }
        
        return $conversion 
            ? $this->getFirstMediaUrl('featured', $conversion)
            : $this->getFirstMediaUrl('featured');
    }
}

// Controller example
class ArticleController extends Controller
{
    public function create()
    {
        return view('articles.create');
    }
    
    public function store(Request $request)
    {
        $article = Article::create($request->validated());
        
        // Upload featured image
        if ($request->hasFile('featured')) {
            $article->addMedia($request->file('featured'))
                ->withCustomProperties(['uploaded_by' => auth()->id()])
                ->toMediaCollection('featured');
        }
        
        // Upload body images
        if ($request->hasFile('body_images')) {
            foreach ($request->file('body_images') as $image) {
                $article->addMedia($image)
                    ->withCustomProperties([
                        'caption' => null,
                        'alt_text' => null,
                    ])
                    ->toMediaCollection('body_images');
            }
        }
        
        // Upload documents
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $doc) {
                $article->addMedia($doc)
                    ->withCustomProperties(['type' => 'attachment'])
                    ->toMediaCollection('documents');
            }
        }
        
        return redirect()->route('articles.show', $article);
    }
    
    public function show(Article $article)
    {
        return view('articles.show', ['article' => $article]);
    }
    
    public function downloadDocument(Article $article, Media $document)
    {
        return $document->toResponse(request());
    }
}

// Blade template
<article class="blog-post">
    <!-- Featured image -->
    @if($article->hasMedia('featured'))
        <div class="featured-image">
            {{ $article->getFirstMedia('featured')->img('featured_large') }}
        </div>
    @endif
    
    <!-- Article title and content -->
    <h1>{{ $article->title }}</h1>
    <div class="content">
        {!! $article->content !!}
    </div>
    
    <!-- Body images gallery -->
    @if($article->hasMedia('body_images'))
        <div class="body-images">
            @foreach($article->getMedia('body_images') as $image)
                <figure class="image-figure">
                    {{ $image->img('body_medium') }}
                    @if($caption = $image->getCustomProperty('caption'))
                        <figcaption>{{ $caption }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    @endif
    
    <!-- Documents download section -->
    @if($article->hasMedia('documents'))
        <div class="documents-section">
            <h2>Downloads</h2>
            <ul class="document-list">
                @foreach($article->getMedia('documents') as $doc)
                    <li>
                        <a href="{{ route('articles.download-document', [$article, $doc]) }}">
                            📄 {{ $doc->name }}
                            <small>({{ $doc->humanReadableSize }})</small>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</article>
```

## User Profile with Avatar

```php
class User extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useFallbackUrl('/images/default-avatar.png')
            ->useFallbackPath(public_path('images/default-avatar.png'));
        
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
    
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar_small')
            ->width(100)
            ->height(100)
            ->crop('crop-center')
            ->sharpen(10)
            ->performOnCollections('avatar');
        
        $this->addMediaConversion('avatar_large')
            ->width(300)
            ->height(300)
            ->crop('crop-center')
            ->performOnCollections('avatar');
        
        $this->addMediaConversion('cover')
            ->width(1200)
            ->height(300)
            ->crop('crop-center')
            ->performOnCollections('cover');
    }
    
    // Helper methods
    public function getAvatarUrl($size = 'small')
    {
        return $this->getFirstMediaUrl('avatar', 'avatar_' . $size)
            ?: $this->getFirstMedia('avatar')?->getUrl()
            ?: '/images/default-avatar.png';
    }
    
    public function getCoverUrl()
    {
        return $this->getFirstMediaUrl('cover', 'cover');
    }
}

// Update profile endpoint
class ProfileController extends Controller
{
    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:5120']);
        
        $user = auth()->user();
        
        // Replace existing avatar
        $user->clearMediaCollection('avatar');
        
        $user->addMedia($request->file('avatar'))
            ->withCustomProperties(['uploaded_at' => now()])
            ->toMediaCollection('avatar');
        
        return back()->with('success', 'Avatar updated');
    }
    
    public function updateCover(Request $request)
    {
        $request->validate(['cover' => 'required|image|max:10240']);
        
        $user = auth()->user();
        $user->clearMediaCollection('cover');
        
        $user->addMedia($request->file('cover'))
            ->toMediaCollection('cover');
        
        return back()->with('success', 'Cover updated');
    }
}

// Profile view
<div class="profile-header">
    <img src="{{ $user->getCoverUrl() }}" alt="Cover" class="cover-image">
    <div class="profile-info">
        <img src="{{ $user->getAvatarUrl('large') }}" alt="Avatar" class="avatar">
        <h1>{{ $user->name }}</h1>
    </div>
</div>
```

## Event with Photo Gallery Download

```php
class Event extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->withResponsiveImages();
    }
    
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->performOnCollections('photos');
        
        $this->addMediaConversion('preview')
            ->width(600)
            ->performOnCollections('photos');
        
        $this->addMediaConversion('full')
            ->width(2000)
            ->performOnCollections('photos')
            ->withResponsiveImages();
    }
}

// Controller with ZIP download
class EventController extends Controller
{
    public function downloadPhotos(Event $event)
    {
        if (!$event->hasMedia('photos')) {
            abort(404, 'No photos available');
        }
        
        return MediaStream::create($event->slug . '-photos.zip')
            ->addMedia($event->getMedia('photos'));
    }
}

// Route
Route::get('/events/{event}/photos/download', [EventController::class, 'downloadPhotos'])
    ->name('events.download-photos');
```

## Image Processing with Optimization

```php
class ImageOptimizationExample
{
    // In Model
    public function registerMediaConversions(?Media $media = null): void
    {
        // Highly optimized for web
        $this->addMediaConversion('optimized')
            ->width(1200)
            ->quality(75)
            ->format('webp')
            ->sharpen(10);
        
        // Mobile version
        $this->addMediaConversion('mobile')
            ->width(400)
            ->quality(70)
            ->format('webp');
        
        // Thumbnail with aggressive compression
        $this->addMediaConversion('thumbnail')
            ->width(150)
            ->height(150)
            ->crop('crop-center')
            ->quality(80)
            ->format('webp');
    }
}
```

## API Response with Media URLs

```php
class ProductController extends Controller
{
    public function show(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'thumbnail' => $product->getFirstMediaUrl('thumbnail', 'thumb'),
            'gallery' => $product->getMedia('gallery')->map(fn($media) => [
                'id' => $media->id,
                'url' => $media->getUrl('medium'),
                'srcset' => $media->getSrcset('large'),
                'name' => $media->name,
            ]),
            'images' => [
                'thumbnail' => $product->getFirstMediaUrl('thumbnail', 'thumb'),
                'medium' => $product->getFirstMediaUrl('gallery', 'medium'),
                'large' => $product->getFirstMediaUrl('gallery', 'large'),
            ],
        ]);
    }
}
```

## Advanced: Custom Media Organization

```php
class Document extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    protected $fillable = ['title', 'category', 'version'];
    
    public function registerMediaCollections(): void
    {
        // Organize by category
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf']);
    }
    
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->pdfPageNumber(1)
            ->width(600)
            ->height(800)
            ->performOnCollections('documents');
    }
    
    // Get media organized by version
    public function getMediaByVersion($version)
    {
        return $this->getMedia('documents', function ($media) use ($version) {
            return $media->getCustomProperty('version') === $version;
        });
    }
    
    // Get latest version
    public function getLatestMedia()
    {
        $media = $this->getMedia('documents');
        return $media->sortByDesc(fn($m) => $m->getCustomProperty('version'))->first();
    }
}

// Usage
$document = Document::find(1);
$document->addMedia($request->file('pdf'))
    ->withCustomProperties([
        'version' => 2,
        'changelog' => 'Updated sections 3 and 4',
        'reviewed_by' => auth()->id(),
    ])
    ->toMediaCollection('documents');

// Retrieve
$latestPdf = $document->getLatestMedia();
$version1Files = $document->getMediaByVersion(1);
```
