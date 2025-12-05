# Real-World Examples

Complete working examples from production implementations.

## Example 1: Blog Post Editor

### Scenario
Building a blog platform where authors write rich content with embedded images.

### Files Structure

```
app/
├── Models/
│   └── BlogPost.php
├── Observers/
│   └── BlogPostObserver.php
├── Filament/
│   └── Resources/
│       └── BlogPostResource.php
└── Console/
    └── Commands/
        └── ImagesCleanUnused.php

database/
└── migrations/
    └── 2025_01_20_create_blog_posts_table.php

resources/
├── views/
│   └── blog/
│       ├── index.blade.php
│       └── show.blade.php
└── css/
    └── lexical-content.css
```

### Database Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->text('content');
            $table->string('featured_image')->nullable();
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->integer('views')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
```

### Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'category_id',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image 
            ? Storage::disk('public')->url($this->featured_image) 
            : null;
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content));
        return ceil($words / 200); // Average 200 words per minute
    }
}
```

### Observer

```php
<?php

namespace App\Observers;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BlogPostObserver
{
    public function creating(BlogPost $post): void
    {
        if (empty($post->slug)) {
            $post->slug = \Str::slug($post->title);
        }
        
        // Set excerpt from content if not provided
        if (empty($post->excerpt) && $post->content) {
            $plainText = strip_tags($post->content);
            $post->excerpt = substr($plainText, 0, 200) . '...';
        }
    }

    public function saving(BlogPost $post): void
    {
        if ($post->content) {
            $post->content = $this->convertBase64ToStorage($post->content);
        }
    }

    public function updating(BlogPost $post): void
    {
        $old = BlogPost::find($post->id);
        
        if (!$old) return;

        if ($post->featured_image !== $old->featured_image) {
            $this->deleteOldImage($old->featured_image);
        }

        $this->handleContentImages($old->content, $post->content);
    }

    public function deleted(BlogPost $post): void
    {
        if ($post->featured_image) {
            $this->deleteOldImage($post->featured_image);
        }
        
        $this->deleteContentImages($post->content);
    }

    // ... (rest of private methods from observer-implementation.md)
    
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

        foreach ($matches as $match) {
            try {
                $extension = $match[1] === 'svg+xml' ? 'svg' : $match[1];
                $filePath = $this->saveBase64AsFile($match[2], $extension);
                $content = str_replace($match[0], Storage::disk('public')->url($filePath), $content);
            } catch (\Exception $e) {
                Log::error("Base64 conversion failed: " . $e->getMessage());
            }
        }
        
        return $content;
    }

    private function saveBase64AsFile(string $base64Data, string $extension): string
    {
        $imageData = base64_decode($base64Data);
        
        if ($imageData === false) {
            throw new \Exception("Invalid base64 data");
        }
        
        $filename = 'blog-lexical-' . time() . '-' . uniqid() . '.' . $extension;
        $path = 'uploads/blog/' . now()->format('Y/m/d') . '/' . $filename;
        
        $directory = dirname($path);
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        if (!Storage::disk('public')->put($path, $imageData)) {
            throw new \Exception("Failed to save file");
        }
        
        return $path;
    }

    private function deleteOldImage(?string $path): void
    {
        if (!$path) return;
        
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete image: " . $e->getMessage());
        }
    }

    private function handleContentImages(?string $oldContent, ?string $newContent): void
    {
        if (!$oldContent) return;

        $oldImages = $this->extractImagesFromContent($oldContent);
        $newImages = $this->extractImagesFromContent($newContent ?? '');
        
        foreach (array_diff($oldImages, $newImages) as $image) {
            $this->deleteOldImage($image);
        }
    }

    private function deleteContentImages(?string $content): void
    {
        if (!$content) return;

        foreach ($this->extractImagesFromContent($content) as $image) {
            $this->deleteOldImage($image);
        }
    }

    private function extractImagesFromContent(string $content): array
    {
        $images = [];
        
        preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $src) {
                $path = $this->getRelativePathFromUrl($src);
                if ($path) $images[] = $path;
            }
        }
        
        return array_unique($images);
    }

    private function getRelativePathFromUrl(string $url): ?string
    {
        $url = str_replace([config('app.url'), url('/')], '', $url);
        $url = preg_replace('/^\/storage\//', '', $url);
        
        if (str_contains($url, 'uploads/')) {
            if (preg_match('/uploads\/.*/', $url, $matches)) {
                return $matches[0];
            }
        }
        
        return null;
    }
}
```

### Filament Resource

```php
<?php

namespace App\Filament\Resources;

use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentLexicalEditor\FilamentLexicalEditor;
use Malzariey\FilamentLexicalEditor\Enums\ToolbarItem;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $navigationLabel = 'Blog Posts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Post Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('Excerpt')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->helperText('Leave empty to auto-generate from content'),
                    ])->columns(2),

                Forms\Components\Section::make('Content')
                    ->schema([
                        FilamentLexicalEditor::make('content')
                            ->label('Post Content')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Rich text editor with automatic image conversion')
                            ->enabledToolbars([
                                ToolbarItem::UNDO,
                                ToolbarItem::REDO,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::BOLD,
                                ToolbarItem::ITALIC,
                                ToolbarItem::UNDERLINE,
                                ToolbarItem::H1,
                                ToolbarItem::H2,
                                ToolbarItem::H3,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::BULLET,
                                ToolbarItem::NUMBERED,
                                ToolbarItem::QUOTE,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::IMAGE,
                                ToolbarItem::CLEAR,
                            ]),
                    ]),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->directory('uploads/blog')
                            ->nullable(),
                    ]),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Checkbox::make('is_published')
                            ->label('Published'),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Published At')
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name'),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
```

### Frontend Template

```blade
<!-- resources/views/blog/show.blade.php -->

@extends('layouts.app')

@section('meta')
    <meta name="description" content="{{ $post->excerpt }}">
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ $post->excerpt }}">
    @if($post->featured_image)
        <meta property="og:image" content="{{ $post->featured_image_url }}">
    @endif
@endsection

@section('content')
    <article class="blog-post">
        <header class="post-header">
            <div class="post-meta">
                <span class="category">{{ $post->category->name }}</span>
                <span class="author">By {{ $post->author->name }}</span>
                <time datetime="{{ $post->published_at->toIso8601String() }}">
                    {{ $post->published_at->format('M d, Y') }}
                </time>
                <span class="reading-time">{{ $post->reading_time }} min read</span>
            </div>

            <h1>{{ $post->title }}</h1>

            @if($post->featured_image)
                <figure class="featured-image">
                    <img 
                        src="{{ $post->featured_image_url }}" 
                        alt="{{ $post->title }}"
                        loading="lazy"
                    >
                </figure>
            @endif
        </header>

        <div class="post-content lexical-content">
            {!! $post->content !!}
        </div>

        <footer class="post-footer">
            <div class="post-nav">
                @if($previous = $post->where('published_at', '<', $post->published_at)->latest('published_at')->first())
                    <a href="{{ route('blog.show', $previous) }}" class="prev">
                        ← {{ $previous->title }}
                    </a>
                @endif

                @if($next = $post->where('published_at', '>', $post->published_at)->oldest('published_at')->first())
                    <a href="{{ route('blog.show', $next) }}" class="next">
                        {{ $next->title }} →
                    </a>
                @endif
            </div>
        </footer>
    </article>
@endsection
```

## Example 2: Service Description with Multiple Editors

### Scenario
Multiple models using Lexical Editor with different storage paths.

```php
// Models
class Service extends Model {}
class ServiceDescription extends Model {}

// Observers
class ServiceObserver { /* handles uploads/services/ */ }
class ServiceDescriptionObserver { /* handles uploads/service-descriptions/ */ }

// In each Observer
private function saveBase64AsFile(string $base64Data, string $extension): string
{
    // Different path for each model
    $path = 'uploads/services/' . $filename; // For Service
    // OR
    $path = 'uploads/service-descriptions/' . $filename; // For ServiceDescription
    
    // ... rest of code
}
```

## Example 3: News Article with Scheduled Cleanup

### Scenario
High-volume news site with automatic image cleanup.

```php
// config/image-cleanup.php
return [
    'enabled' => true,
    'schedule' => 'weekly', // daily, weekly, monthly
    'schedule_time' => '02:00', // 2 AM
    'retention_days' => 90,
    'excluded_paths' => [
        'uploads/static/',
        'uploads/core/',
    ],
];

// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('images:clean-unused')
        ->weekly()
        ->sundays()
        ->at('02:00')
        ->onSuccess(function () {
            // Send success notification
            Notification::route('slack', config('services.slack.webhook'))
                ->notify(new CleanupSuccessful());
        })
        ->onFailure(function () {
            // Send failure notification
            Notification::route('mail', 'admin@example.com')
                ->notify(new CleanupFailed());
        });
}
```

## Example 4: Multi-Language Content

### Scenario
Supporting multiple languages with separate image storage per language.

```php
// Model with translations
class Article extends Model
{
    use \Spatie\Translatable\HasTranslations;
    
    public $translatable = ['title', 'content', 'slug'];
}

// Observer handling multiple languages
public function convertBase64ToStorage(string $content, string $locale = 'en'): string
{
    // Path includes language code
    $this->currentLocale = $locale;
    return $this->_convertBase64ToStorage($content);
}

private function saveBase64AsFile(string $base64Data, string $extension): string
{
    $filename = "{$this->currentLocale}-lexical-" . time() . '-' . uniqid() . '.' . $extension;
    $path = 'uploads/articles/' . $this->currentLocale . '/' . $filename;
    // ... rest of code
}
```

## Example 5: Performance Optimization

### Image Resizing and Compression

```php
// Add to PostObserver
use Intervention\Image\Facades\Image;

private function saveBase64AsFile(string $base64Data, string $extension): string
{
    $imageData = base64_decode($base64Data);
    
    // Optimize
    $image = Image::make($imageData);
    
    // Resize large images
    if ($image->width() > 1200 || $image->height() > 900) {
        $image->fit(1200, 900, function ($constraint) {
            $constraint->aspectRatio();
        });
    }
    
    // Save as WebP with compression
    $filename = 'lexical-' . time() . '-' . uniqid() . '.webp';
    $path = 'uploads/content/' . $filename;
    
    $directory = dirname($path);
    if (!Storage::disk('public')->exists($directory)) {
        Storage::disk('public')->makeDirectory($directory);
    }
    
    Storage::disk('public')->put($path, $image->encode('webp', 75)->__toString());
    
    return $path;
}
```

## Example 6: Testing Suite

### Complete Test Cases

```php
<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogPostImageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_converts_base64_images_on_creation()
    {
        $post = BlogPost::create([
            'title' => 'Test',
            'content' => '<img src="data:image/png;base64,iVBORw0...">',
        ]);

        $this->assertStringNotContainsString('data:image', $post->content);
        $this->assertStringContainsString('/storage/uploads/', $post->content);
    }

    /** @test */
    public function it_deletes_old_featured_image_on_update()
    {
        $post = BlogPost::create(['title' => 'Test', 'featured_image' => 'old.jpg']);
        Storage::disk('public')->put('old.jpg', 'content');

        $post->update(['featured_image' => 'new.jpg']);

        Storage::disk('public')->assertMissing('old.jpg');
    }

    /** @test */
    public function it_cleans_unused_images_when_updating_content()
    {
        $post = BlogPost::create([
            'title' => 'Test',
            'content' => '<img src="/storage/uploads/blog/img1.jpg"><img src="/storage/uploads/blog/img2.jpg">',
        ]);

        Storage::disk('public')->put('uploads/blog/img1.jpg', 'content');
        Storage::disk('public')->put('uploads/blog/img2.jpg', 'content');

        $post->update(['content' => '<img src="/storage/uploads/blog/img1.jpg">']);

        Storage::disk('public')->assertMissing('uploads/blog/img2.jpg');
        Storage::disk('public')->assertExists('uploads/blog/img1.jpg');
    }

    /** @test */
    public function it_deletes_all_images_on_deletion()
    {
        $post = BlogPost::create([
            'title' => 'Test',
            'featured_image' => 'featured.jpg',
            'content' => '<img src="/storage/uploads/blog/content.jpg">',
        ]);

        Storage::disk('public')->put('featured.jpg', 'content');
        Storage::disk('public')->put('uploads/blog/content.jpg', 'content');

        $post->delete();

        Storage::disk('public')->assertMissing('featured.jpg');
        Storage::disk('public')->assertMissing('uploads/blog/content.jpg');
    }
}
```

## Example 7: API Endpoint for Image Upload

```php
// routes/api.php
Route::post('/posts/upload-image', ImageUploadController::class)->middleware('auth');

// app/Http/Controllers/ImageUploadController.php
class ImageUploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $file = $request->file('image');
        $filename = 'lexical-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/content', $filename, 'public');

        return response()->json([
            'success' => true,
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
```

These examples demonstrate real-world implementations and best practices for the Lexical Editor image management system.
