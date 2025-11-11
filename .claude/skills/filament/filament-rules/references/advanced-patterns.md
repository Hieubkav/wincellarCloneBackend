# Filament Advanced Patterns

## Select with allowHtml()

**⚠️ CRITICAL: Tailwind classes NOT supported in allowHtml()!**

### Why This Happens

Filament's `allowHtml()` renders dynamic HTML content that bypasses Tailwind's compilation process. Tailwind classes only compiled at build time for static HTML.

### Correct Pattern

```php
Select::make('logo_image_id')
    ->label('Logo')
    ->options(
        Image::where('active', true)
            ->get()
            ->mapWithKeys(function ($image) {
                $url = \Storage::disk($image->disk ?? 'public')->url($image->file_path);
                $fileName = basename($image->file_path);
                
                // ✅ USE INLINE STYLES
                return [
                    $image->id => '<div style="display: flex; align-items: center; gap: 0.5rem;">
                        <img src="' . $url . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 0.25rem;" />
                        <span style="font-size: 0.875rem;">' . $fileName . '</span>
                    </div>'
                ];
            })
    )
    ->allowHtml()
    ->searchable();
```

### Common Mistakes

```php
// ❌ WRONG - Tailwind classes won't work
->options([
    1 => '<div class="flex items-center gap-2">
        <img src="..." class="w-10 h-10 object-cover rounded" />
    </div>'
])

// ✅ CORRECT - Use inline styles
->options([
    1 => '<div style="display: flex; align-items: center; gap: 0.5rem;">
        <img src="..." style="width: 40px; height: 40px; object-fit: cover; border-radius: 0.25rem;" />
    </div>'
])
```

### Tailwind to Inline CSS Conversion

| Tailwind | Inline CSS |
|----------|------------|
| `flex` | `display: flex;` |
| `items-center` | `align-items: center;` |
| `gap-2` | `gap: 0.5rem;` |
| `w-10 h-10` | `width: 40px; height: 40px;` |
| `object-cover` | `object-fit: cover;` |
| `rounded` | `border-radius: 0.25rem;` |
| `text-sm` | `font-size: 0.875rem;` |

## Observer Pattern Details

### SEO Fields Auto-Generation

```php
class ProductObserver
{
    public function creating(Product $product): void
    {
        // Slug
        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name);
        }
        
        // Meta title
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }
        
        // Meta description
        if (empty($product->meta_description)) {
            $product->meta_description = Str::limit(strip_tags($product->description), 160);
        }
    }
    
    public function updating(Product $product): void
    {
        // Update slug if name changed
        if ($product->isDirty('name') && empty($product->slug)) {
            $product->slug = Str::slug($product->name);
        }
    }
}
```

### Register Observers

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    Product::observe(ProductObserver::class);
    Category::observe(CategoryObserver::class);
    Article::observe(ArticleObserver::class);
    Image::observe(ImageObserver::class);
}
```

## Settings Pages Advanced

### Multiple Forms in One Page

```php
class GeneralSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;
    
    public ?array $siteData = [];
    public ?array $socialData = [];
    
    public function mount(): void
    {
        $this->siteForm->fill(/* load site settings */);
        $this->socialForm->fill(/* load social settings */);
    }
    
    protected function getForms(): array
    {
        return [
            'siteForm' => $this->makeForm()
                ->schema([
                    Section::make('Site Settings')->schema([
                        TextInput::make('site_name')->label('Tên website'),
                    ]),
                ])
                ->statePath('siteData'),
                
            'socialForm' => $this->makeForm()
                ->schema([
                    Section::make('Social Media')->schema([
                        TextInput::make('facebook_url')->label('Facebook URL'),
                    ]),
                ])
                ->statePath('socialData'),
        ];
    }
    
    public function saveSite(): void
    {
        $data = $this->siteForm->getState();
        // Save site settings
    }
    
    public function saveSocial(): void
    {
        $data = $this->socialForm->getState();
        // Save social settings
    }
}
```

### View with Multiple Forms

```blade
<div class="space-y-6">
    <form wire:submit="saveSite">
        {{ $this->siteForm }}
        <x-filament::button type="submit">Lưu cài đặt Site</x-filament::button>
    </form>
    
    <form wire:submit="saveSocial">
        {{ $this->socialForm }}
        <x-filament::button type="submit">Lưu cài đặt Social</x-filament::button>
    </form>
</div>
```

## Images Integration Advanced

### Multiple Image Types

```php
// Model
public function images(): MorphMany
{
    return $this->morphMany(Image::class, 'model');
}

public function coverImage(): MorphOne
{
    return $this->morphOne(Image::class, 'model')
        ->where('order', 0);
}

public function galleryImages(): MorphMany
{
    return $this->morphMany(Image::class, 'model')
        ->where('order', '>', 0)
        ->orderBy('order');
}

// Resource
public static function getRelations(): array
{
    return [
        ImagesRelationManager::class,
    ];
}

// Custom tab for cover image
Tabs\Tab::make('Ảnh đại diện')->schema([
    ViewField::make('cover_image')
        ->view('filament.forms.components.cover-image-picker')
        ->label(''),
])
```

## Reorderable Tables

```php
public static function table(Table $table): Table
{
    return $table
        ->reorderable('order')  // If 'order' column exists
        ->defaultSort('order')
        ->columns([
            TextColumn::make('order')
                ->label('Thứ tự')
                ->sortable(),
                
            TextColumn::make('name')
                ->label('Tên'),
        ]);
}
```

## Navigation Badges

```php
public static function getNavigationBadge(): ?string
{
    return static::getModel()::count();
}

public static function getNavigationBadgeColor(): string|array|null
{
    $count = static::getModel()::count();
    return $count > 10 ? 'danger' : 'primary';
}
```
