# Image Management Guide for Filament 4.x + Laravel 12

> **Version:** 1.2.0  
> **Last Updated:** 2025-11-09  
> **Filament:** 4.0  
> **Laravel:** 12.x
> **Updated:** CheckboxList implementation (no custom ViewField)

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Image Model](#image-model)
5. [Admin Interface](#admin-interface)
6. [Usage Patterns](#usage-patterns)
7. [CheckboxList Image Picker (Recommended)](#checkboxlist-image-picker-recommended)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)

---

## Overview

This guide documents the **centralized image management system** for Filament 4.x projects. The system provides:

- ✅ **Single source of truth** for all images
- ✅ **Polymorphic relationships** (one `images` table for all entities)
- ✅ **CheckboxList picker**: Select existing images with preview + search (built-in Filament)
- ✅ **FileUpload field**: Upload new images with WebP auto-conversion
- ✅ **WebP conversion** with automatic optimization (85% quality)
- ✅ **Order management** with drag-and-drop reordering
- ✅ **Soft deletes** with reference cleanup via ImageObserver
- ✅ **No custom Alpine.js** (use native Filament components only)

### Why This Approach?

**❌ Avoid These Common Pitfalls:**
- Using Spatie Media Library (creates separate `media` table, conflicts with polymorphic design)
- Custom ViewField with Alpine.js (causes conflicts with Filament internals)
- Multiple image storage approaches across the project
- No reusability of uploaded images
- Inefficient storage management

**✅ This System Provides:**
- One `images` table for Products, Articles, Settings, etc.
- Easy image reuse across different entities with CheckboxList
- Consistent management interface using native Filament components
- Clean, maintainable codebase without custom JavaScript conflicts

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Filament Admin Panel                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐    │
│  │   Products   │  │   Articles   │  │   Settings   │    │
│  │   Resource   │  │   Resource   │  │   Resource   │    │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘    │
│         │                  │                  │            │
│         └──────────────────┼──────────────────┘            │
│                            │                               │
│                  ┌─────────▼──────────┐                    │
│                  │  ImagePicker Field │                    │
│                  │  ┌──────────────┐  │                    │
│                  │  │ Upload New   │  │                    │
│                  │  └──────────────┘  │                    │
│                  │  ┌──────────────┐  │                    │
│                  │  │Select Existing│ │                    │
│                  │  └──────────────┘  │                    │
│                  └─────────┬──────────┘                    │
│                            │                               │
└────────────────────────────┼───────────────────────────────┘
                             │
                  ┌──────────▼──────────┐
                  │   Images Table      │
                  │  (Polymorphic)      │
                  │                     │
                  │  - id               │
                  │  - file_path        │
                  │  - model_type       │
                  │  - model_id         │
                  │  - order            │
                  │  - active           │
                  └─────────────────────┘
```

---

## Database Schema

### Images Table

```sql
CREATE TABLE images (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    file_path VARCHAR(2048) NOT NULL,
    disk VARCHAR(191) NOT NULL DEFAULT 'public',
    alt VARCHAR(255) NULL,
    width INT UNSIGNED NULL,
    height INT UNSIGNED NULL,
    mime VARCHAR(191) NULL,
    model_type VARCHAR(191) NULL,    -- Polymorphic type
    model_id BIGINT UNSIGNED NULL,   -- Polymorphic ID
    `order` INT UNSIGNED DEFAULT 0,  -- Display order (0 = cover/primary)
    active BOOLEAN DEFAULT TRUE,
    extra_attributes JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,       -- Soft deletes
    
    INDEX idx_model (model_type, model_id),
    INDEX idx_order (`order`),
    INDEX idx_active (active),
    INDEX idx_deleted_at (deleted_at)
);
```

### Key Concepts

- **`order = 0`**: Primary/cover image
- **`order > 0`**: Additional images (gallery)
- **`model_type/model_id`**: Polymorphic relationship
- **`disk`**: Storage disk (public, s3, etc.)
- **`soft deletes`**: Safe deletion with reference cleanup
- **Order uniqueness**: Handled by ImageObserver (no database constraint since v1.2.0)
  - Previously had unique constraint `(model_type, model_id, order)` but caused issues with soft deletes
  - Now uses regular index + Observer to ensure uniqueness at application layer

---

## Image Model

### Location
```
app/Models/Image.php
```

### Key Features

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'file_path', 'disk', 'alt', 'width', 'height', 
        'mime', 'model_type', 'model_id', 'order', 
        'active', 'extra_attributes'
    ];

    protected function casts(): array
    {
        return [
            'width' => 'int',
            'height' => 'int',
            'order' => 'int',
            'active' => 'bool',
            'extra_attributes' => 'array',
        ];
    }

    // Polymorphic relationship
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    // URL accessor
    public function getUrlAttribute(): ?string
    {
        return $this->file_path 
            ? Storage::disk($this->disk)->url($this->file_path)
            : null;
    }
}
```

### Model Observers

**Location:** `app/Observers/ImageObserver.php`

**Automatic Behaviors:**
- Auto-assign `order` value on creation
- Reassign cover image if `order = 0` is set
- Cleanup references in `settings`, `social_links` tables on delete
- Delete physical file on force delete

---

## Admin Interface

### ImageResource

**Location:** `app/Filament/Resources/Images/ImageResource.php`

**Features:**
- Grid/list view of all images
- Filter by model_type, disk, active status
- Soft delete support
- Direct edit/delete actions
- Preview thumbnails

### Usage in Other Resources

#### Method 1: Relationship Manager (Multiple Images)

```php
// app/Filament/Resources/Products/ProductResource.php

public static function getRelations(): array
{
    return [
        ImagesRelationManager::class,
    ];
}
```

**Benefits:**
- Full CRUD on images
- Drag-and-drop reordering
- Separate tab in edit view

#### Method 2: Direct Field (Single Image)

```php
// In form schema
Select::make('logo_image_id')
    ->label('Logo')
    ->relationship('logoImage', 'file_path')
    ->getOptionLabelFromRecordUsing(fn ($record) => 
        basename($record->file_path)
    )
    ->searchable()
    ->preload();
```

**Benefits:**
- Simple, inline selection
- Good for single image fields

---

## Usage Patterns

### Pattern 1: Product with Gallery

```php
// Product Model
class Product extends Model
{
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'model')
            ->orderBy('order');
    }

    public function coverImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'model')
            ->where('order', 0);
    }
}

// ProductResource
public static function getRelations(): array
{
    return [
        ImagesRelationManager::class,
    ];
}
```

### Pattern 2: Article with Single Featured Image

```php
// Article Model
class Article extends Model
{
    public function featuredImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'model')
            ->where('order', 0);
    }
}

// ArticleResource form
Select::make('featured_image_id')
    ->relationship('featuredImage', 'file_path')
    ->searchable();
```

### Pattern 3: Settings with Logo/Favicon

```php
// Setting Model
class Setting extends Model
{
    public function logoImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'logo_image_id');
    }

    public function faviconImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'favicon_image_id');
    }
}

// SettingResource form
Select::make('logo_image_id')
    ->label('Logo')
    ->relationship('logoImage', 'file_path');

Select::make('favicon_image_id')
    ->label('Favicon')
    ->relationship('faviconImage', 'file_path');
```

---

## CheckboxList Image Picker (Recommended)

> **Status:** ✅ Implemented in v1.2.0 (Current)  
> **Approach:** Use native Filament CheckboxList component - NO custom ViewField or Alpine.js

### Why CheckboxList Instead of Custom Component?

**❌ Problems with Custom ViewField + Alpine.js:**
- Conflicts with Filament's internal Alpine.js components
- Difficult to debug and maintain
- Breaks when Filament updates
- Loading spinner appears forever (Livewire conflicts)
- CSS not applying correctly in modal

**✅ Benefits of Native CheckboxList:**
- Built-in Alpine.js bindings that work with Filament
- Built-in search functionality
- Built-in bulk toggle (select all / deselect all)
- Consistent UI with Filament design system
- No conflicts, no bugs
- Less code, easier to maintain

### Implementation

**In RelationManagers (ProductResource/ArticleResource):**

```php
// Required imports
use App\Models\Image;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;      // ✅ Form field
use Filament\Forms\Components\FileUpload;        // ✅ Form field
use Filament\Forms\Components\Toggle;            // ✅ Form field
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs;            // ✅ Layout component
use Filament\Schemas\Schema;                     // ✅ Schema class
use Filament\Support\Enums\GridDirection;        // ✅ Enum
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

Action::make('selectFromLibrary')
    ->label('Chọn từ thư viện')
    ->icon('heroicon-o-photo')
    ->color('gray')
    ->modalHeading('Chọn ảnh từ thư viện')
    ->modalDescription('Chọn ảnh có sẵn trong hệ thống để thêm vào sản phẩm')
    ->modalSubmitActionLabel('Thêm ảnh đã chọn')
    ->modalWidth('7xl')  // Wide modal for better UX
    ->form(function () {
        // Fetch available images
        $images = Image::query()
            ->where('active', true)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Build options with HTML preview
        $options = $images->mapWithKeys(function ($image) {
            $filename = basename($image->file_path);
            $imageUrl = $image->url ?? '/images/placeholder.png';
            
            // HTML label với thumbnail preview
            $html = '<div style="display: flex; align-items: center; gap: 8px;">';
            $html .= '<img src="' . e($imageUrl) . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" />';
            $html .= '<span>' . e($filename) . '</span>';
            $html .= '</div>';
            
            return [$image->id => $html];
        })->toArray();

        return [
            CheckboxList::make('image_ids')
                ->label('Chọn ảnh')
                ->options($options)
                ->columns(3)  // 3 columns for better layout
                ->gridDirection(GridDirection::Column)
                ->required()
                ->searchable()      // Built-in search by Filament
                ->bulkToggleable()  // Built-in select all/deselect all
                ->allowHtml(),      // Allow HTML in labels for preview
        ];
    })
    ->action(function (array $data, RelationManager $livewire): void {
        $product = $livewire->getOwnerRecord();
        $selectedImageIds = $data['image_ids'] ?? [];

        if (empty($selectedImageIds)) {
            return;
        }

        $maxOrder = $product->images()->max('order') ?? 0;

        foreach ($selectedImageIds as $index => $imageId) {
            $image = Image::find($imageId);
            if (!$image) {
                continue;
            }

            // Create a copy of the image for this product
            $product->images()->create([
                'file_path' => $image->file_path,
                'disk' => $image->disk,
                'alt' => $image->alt,
                'width' => $image->width,
                'height' => $image->height,
                'mime' => $image->mime,
                'order' => $maxOrder + $index + 1,
                'active' => true,
            ]);
        }

        Notification::make()
            ->title('Đã thêm ảnh từ thư viện')
            ->success()
            ->body('Đã thêm ' . count($selectedImageIds) . ' ảnh vào sản phẩm')
            ->send();
    });
```

### Built-in Features

CheckboxList provides everything you need out of the box:

- ✅ **Search**: Type to filter options (built-in Alpine.js)
- ✅ **Bulk Toggle**: "Select all" / "Deselect all" links
- ✅ **Multi-select**: Checkbox-based selection with wire:model
- ✅ **HTML Labels**: Use `allowHtml()` to show image previews
- ✅ **Columns Layout**: Responsive grid with `columns(3)`
- ✅ **Grid Direction**: Column or row layout
- ✅ **Dark Mode**: Automatic support
- ✅ **Validation**: Built-in required() and other rules

### How It Works

1. **User clicks "Chọn từ thư viện"** button
2. **Modal opens** with CheckboxList showing images
3. **Search** filters options by filename automatically
4. **Select images** by clicking checkboxes
5. **Bulk toggle** to select/deselect all at once
6. **Submit** creates copies of selected images for the entity

### Customization

```php
// Adjust modal width
->modalWidth('7xl')  // Options: sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl, 6xl, 7xl

// Adjust number of columns
->columns(3)  // 2-4 recommended

// Adjust image limit
->limit(100)  // in query

// Change grid direction
->gridDirection(GridDirection::Row)  // or Column

// Add descriptions to options
$html .= '<small style="color: gray;">' . $image->width . 'x' . $image->height . '</small>';
```

### Implementation Roadmap

1. **Phase 1** ✅ (Complete): RelationManager with FileUpload
2. **Phase 2** ✅ (Complete): CheckboxList with native Filament components
3. **Phase 3** (Future): Advanced features:
   - Lazy loading pagination for 1000+ images
   - Image cropping interface
   - Advanced filters (by dimensions, date, model type)
   - Bulk operations from ImageResource

---

## Delete Protection

### Overview

Images that are actively used cannot be deleted until all references are removed. See detailed documentation: [@/docs/IMAGE_DELETE_PROTECTION.md](./IMAGE_DELETE_PROTECTION.md)

### Quick Guide

**Problem:** Cannot delete image, get error message
```
❌ Không thể xóa ảnh
Ảnh này đang được sử dụng bởi Product #126
```

**Solution:**
1. Click on image detail page (`/admin/images/{id}/edit`)
2. See "Relationships / Quan hệ" section
3. Option A: Click on owner name link → Opens owner edit page → Remove image there
4. Option B: Click "Gỡ liên kết" button → Confirms detach → Now can delete

### Features

- ✅ **Delete validation** - Blocks deletion of images in use
- ✅ **Relationship viewer** - See owner type, ID, name with direct link
- ✅ **Detach action** - One-click reference removal
- ✅ **Bulk protection** - Validates all images before bulk delete
- ✅ **Clear notifications** - Know exactly what's blocking deletion

### Implementation

```php
// ImagesTable.php - Delete validation
\Filament\Actions\DeleteAction::make()
    ->before(function ($action, Image $record) {
        if ($record->model_type && $record->model_id) {
            Notification::make()
                ->danger()
                ->title('Không thể xóa ảnh')
                ->body("Đang được sử dụng bởi {$ownerType} #{$record->model_id}")
                ->send();
            $action->cancel();
        }
    })
```

```php
// ImageInfolist.php - Relationship section
Section::make('Relationships / Quan hệ')
    ->schema([
        TextEntry::make('model_type')->label('Loại')->badge(),
        TextEntry::make('model.name')
            ->label('Tên')
            ->url(fn ($record) => getOwnerUrl($record)),
        Actions::make([
            Action::make('detach')
                ->label('Gỡ liên kết')
                ->action(fn ($record) => $record->update([
                    'model_type' => null, 
                    'model_id' => null
                ])),
        ]),
    ])
```

---

## Best Practices

### ✅ DO

1. **Use polymorphic relationships** for entities with multiple images
   ```php
   $product->images()->create([...]);
   ```

2. **Use BelongsTo for single images** in fixed fields
   ```php
   $setting->logoImage()->associate($image);
   ```

3. **Set `order = 0` for primary/cover images**
   ```php
   $image->update(['order' => 0]); // Becomes cover
   ```

4. **Use WebP format** for web images (auto-conversion in place)
   ```php
   // Already implemented in ImagesRelationManager
   $webp = $image->toWebp(quality: 85);
   ```

5. **Leverage ImageObserver** for automatic cleanup
   ```php
   // Just delete - observer handles references
   $image->delete();
   ```

6. **Use eager loading** to avoid N+1 queries
   ```php
   Product::with('images', 'coverImage')->get();
   ```

7. **Use native Filament components** for image pickers
   ```php
   // ✅ Good - CheckboxList with allowHtml()
   CheckboxList::make('image_ids')
       ->options($options)
       ->searchable()
       ->bulkToggleable()
       ->allowHtml();
   ```

### ❌ DON'T

1. **Don't bypass the Image model**
   ```php
   // ❌ Bad
   Storage::put('products/image.jpg', $file);
   
   // ✅ Good
   $product->images()->create(['file_path' => ...]);
   ```

2. **Don't hardcode disk names**
   ```php
   // ❌ Bad
   Storage::disk('public')->...
   
   // ✅ Good
   Storage::disk($image->disk)->...
   ```

3. **Don't forget to set model relationship**
   ```php
   // ❌ Bad - orphaned image
   Image::create(['file_path' => ...]);
   
   // ✅ Good
   $product->images()->create(['file_path' => ...]);
   ```

4. **Don't manually manage order values**
   ```php
   // ❌ Bad - prone to errors
   $image->order = 5;
   
   // ✅ Good - use Filament reorderable
   // Handled automatically by ImagesRelationManager
   ```

5. **Don't create custom ViewField with Alpine.js**
   ```php
   // ❌ Bad - causes conflicts with Filament
   ViewField::make('images')
       ->view('filament.forms.custom-picker')  // has x-data, x-model
   
   // ✅ Good - use native CheckboxList
   CheckboxList::make('images')
       ->options($options)
       ->allowHtml()
   ```

---

## Troubleshooting

### Unique Constraint Violation when Adding Images from Library

**Problem:** Error when adding images from library:
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'App\Models\Product-126-2' 
for key 'images_unique_order_per_model'
```

**Cause:** The `images` table has a unique constraint on `(model_type, model_id, order)` to ensure no duplicate order values per model. The bug occurs in two scenarios:

**Scenario 1: Selecting from library**
1. Using `$index` from `foreach ($selectedImageIds as $index => $imageId)` 
2. Calculating `order` as `$maxOrder + $index + 1`
3. If some images are skipped (not found), the order values can collide
4. Max order queries can be stale in concurrent requests

**Scenario 2: Uploading new images**
1. CreateAction doesn't have order logic
2. If order is not set, it tries to insert with null or default value
3. Causes duplicate order values

**Scenario 3: Soft deleted images (Root Cause)**
1. Soft deleted images still counted in unique constraint
2. When trying to add new image with `order = 1`, it conflicts with soft deleted image that also has `order = 1`
3. MariaDB/MySQL doesn't support partial indexes (`WHERE deleted_at IS NULL`)

**Solution:** Find next available order that doesn't exist yet:

```php
// ❌ BAD - Using index can cause gaps and duplicate orders
$maxOrder = $product->images()->max('order') ?? 0;
foreach ($selectedImageIds as $index => $imageId) {
    $image = Image::find($imageId);
    if (!$image) continue;  // Skip causes order gap
    
    $product->images()->create([
        // ...
        'order' => $maxOrder + $index + 1,  // ← BUG: Can be duplicate
    ]);
}

// ❌ ALSO BAD - Incrementing maxOrder still can fail
$maxOrder = $product->images()->max('order') ?? -1;
foreach ($selectedImageIds as $imageId) {
    $maxOrder++;  // ← Can still collide if max() was stale
    $product->images()->create([
        'order' => $maxOrder,  // ← May duplicate
    ]);
}

// ✅ GOOD - Check existence before insert
foreach ($selectedImageIds as $imageId) {
    $image = Image::find($imageId);
    if (!$image) continue;
    
    // Find next available order (0, 1, 2, ...)
    $nextOrder = 0;
    while ($product->images()->where('order', $nextOrder)->exists()) {
        $nextOrder++;
    }
    
    $product->images()->create([
        // ...
        'order' => $nextOrder,  // ← Guaranteed unique
    ]);
}
```

**Why this approach works:**
- Checks database for existing order before each insert
- No reliance on stale max() queries
- Handles gaps in order sequence (if order 2 is deleted, it will reuse it)
- Always finds first available slot starting from 0

**Best Solution: ImageObserver**

Instead of handling order in each action separately, put the logic in `ImageObserver::creating()`:

```php
// app/Observers/ImageObserver.php
public function creating(Image $image): void
{
    // Auto-assign order if not set
    if ($image->order === null && $image->model_type && $image->model_id) {
        $image->order = $this->findNextAvailableOrder($image);
    }
}

private function findNextAvailableOrder(Image $image): int
{
    $nextOrder = 0;
    
    while (Image::query()
        ->where('model_type', $image->model_type)
        ->where('model_id', $image->model_id)
        ->where('order', $nextOrder)
        ->exists()
    ) {
        $nextOrder++;
    }
    
    return $nextOrder;
}
```

**Benefits:**
- ✅ Works for all image creation methods (CreateAction, selectFromLibrary, programmatic)
- ✅ Centralized logic in one place
- ✅ No need to manually handle order in actions
- ✅ Guaranteed unique order values

**Final Solution: Remove Unique Constraint**

Since MariaDB/MySQL doesn't support partial indexes with WHERE clause, the best solution is:

```sql
-- Migration: 2025_11_09_082445_modify_images_unique_constraint_exclude_soft_deleted.php

-- Drop unique constraint
ALTER TABLE images DROP INDEX images_unique_order_per_model;

-- Add regular index for query optimization
CREATE INDEX images_order_index ON images (model_type, model_id, `order`);
```

**Result:**
- ✅ No more unique constraint violations
- ✅ ImageObserver handles order uniqueness in application layer
- ✅ Regular index still optimizes queries
- ✅ Soft deleted images don't interfere with active images

**Fixed in:** v1.2.0
- ImageObserver: Auto-handles order for all image creation
- Migration: Removed unique constraint, added regular index
- ProductResource + ArticleResource: Updated with existence check logic

---

### Import Namespace Errors

**Problem:** Class not found errors:
```
Class "Filament\Forms\Components\Tabs" not found
Class "Filament\Schemas\Components\CheckboxList" not found
```

**Cause:** Dự án này customize Filament để dùng `Schema` thay vì `Form`, nên namespace structure khác với docs chính thức.

**Solution:** Phân biệt rõ Layout Components vs Form Field Components:

```php
// ✅ CORRECT IMPORTS for ImagesRelationManager
use App\Models\Image;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;      // ✅ Form field
use Filament\Forms\Components\FileUpload;        // ✅ Form field
use Filament\Forms\Components\Toggle;            // ✅ Form field
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs;            // ✅ Layout - NOT Forms\Components!
use Filament\Schemas\Schema;                     // ✅ Schema class
use Filament\Support\Enums\GridDirection;        // ✅ Enum
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
```

**Rule:**
- **Layout Components** → `Filament\Schemas\Components\*` (Tabs, Grid, Section)
- **Form Fields** → `Filament\Forms\Components\*` (TextInput, Select, Toggle, CheckboxList, FileUpload)
- **Enums** → `Filament\Support\Enums\*` (GridDirection)

**Common Mistakes:**
```php
// ❌ WRONG - Tabs is not in Forms\Components
use Filament\Forms\Components\Tabs;

// ❌ WRONG - CheckboxList is not in Schemas\Components
use Filament\Schemas\Components\CheckboxList;

// ✅ CORRECT
use Filament\Schemas\Components\Tabs;       // Layout
use Filament\Forms\Components\CheckboxList; // Form field
```

**See also:** `@/docs/filament/FILAMENT_RULES.md` - Section "Common Mistakes" for detailed namespace guide

---

### Images not displaying

**Problem:** Images show broken links

**Solutions:**
1. Check `APP_URL` in `.env`:
   ```env
   APP_URL=http://127.0.0.1:8000
   ```

2. Create symbolic link:
   ```bash
   php artisan storage:link
   ```

3. Verify disk configuration in `config/filesystems.php`:
   ```php
   'public' => [
       'driver' => 'local',
       'root' => storage_path('app/public'),
       'url' => env('APP_URL').'/storage',
       'visibility' => 'public',
   ],
   ```

### Cannot upload images

**Problem:** File upload fails silently

**Solutions:**
1. Check PHP upload limits in `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

2. Check Livewire config `config/livewire.php`:
   ```php
   'temporary_file_upload' => [
       'disk' => null,
       'rules' => 'file|max:10240', // 10MB
       'directory' => null,
   ],
   ```

3. Check storage permissions:
   ```bash
   chmod -R 775 storage/app/public
   ```

### Images deleted but files remain

**Problem:** Orphaned files in storage

**Solution:**
- Use `forceDelete()` to trigger file deletion:
  ```php
  $image->forceDelete(); // Deletes DB record + file
  ```

- Or implement scheduled cleanup:
  ```php
  // In app/Console/Kernel.php
  $schedule->command('images:cleanup-orphaned')->daily();
  ```

### Duplicate cover images

**Problem:** Multiple images with `order = 0`

**Solution:**
- The ImageObserver automatically prevents this
- If it occurs, run cleanup:
  ```php
  // Fix manually
  $product->images()->where('order', 0)->get()->each(function ($img, $idx) {
      if ($idx > 0) $img->update(['order' => $img->nextOrderValue()]);
  });
  ```

---

## Performance Optimization

### Caching Image URLs

```php
// In Image model
public function getUrlAttribute(): ?string
{
    return Cache::remember(
        "image.url.{$this->id}",
        now()->addDay(),
        fn () => Storage::disk($this->disk)->url($this->file_path)
    );
}
```

### Lazy Loading Images

```php
// In Blade templates
<img 
    src="{{ $image->url }}" 
    loading="lazy"
    alt="{{ $image->alt }}"
    width="{{ $image->width }}"
    height="{{ $image->height }}"
>
```

### Responsive Images (Future Enhancement)

```php
// Generate multiple sizes
public function generateResponsiveSizes(): void
{
    $manager = new ImageManager(new Driver());
    $image = $manager->read($this->getFullPath());
    
    foreach ([640, 768, 1024, 1280] as $width) {
        $resized = $image->scale(width: $width);
        $path = $this->getResponsivePath($width);
        Storage::disk($this->disk)->put($path, $resized->toWebp());
    }
}
```

---

## Migration Guide

### From Direct Storage to Image Model

**Before:**
```php
// Old code
$product->image_path = $request->file('image')->store('products');
$product->save();
```

**After:**
```php
// New code
$product->images()->create([
    'file_path' => $savedPath,
    'disk' => 'public',
    'order' => 0, // Cover image
]);
```

### From Spatie Media Library

**Before:**
```php
$product->addMediaFromRequest('image')
    ->toMediaCollection('products');
```

**After:**
```php
// Use ImagesRelationManager in Filament Resource
// Or programmatically:
$product->images()->create([
    'file_path' => $savedPath,
    'disk' => 'public',
]);
```

---

## Related Documentation

- [FILAMENT_RULES.md](../FILAMENT_RULES.md) - Filament coding standards
- [Intervention Image Documentation](https://image.intervention.io/)
- [Filament Forms Documentation](https://filamentphp.com/docs/4.x/forms)
- [Laravel File Storage](https://laravel.com/docs/12.x/filesystem)

---

## Changelog

### v1.2.0 (2025-11-09) - Current
- ✅ **Migrated to CheckboxList** - No more custom ViewField with Alpine.js
- ✅ **Native Filament component** - No conflicts, stable, maintainable
- ✅ **Built-in search** - Powered by Filament's Alpine.js
- ✅ **Built-in bulk toggle** - Select all / Deselect all
- ✅ **HTML labels** - Image preview with `allowHtml()`
- ✅ **3-column layout** - Better UX in 7xl modal
- ✅ **Dark mode** - Automatic support
- ✅ Updated ProductResource and ArticleResource
- ✅ Removed custom blade view (no longer needed)
- 🐛 **Fixed unique constraint violation** - Removed DB constraint, ImageObserver handles order uniqueness
- 🐛 **Fixed namespace imports** - Tabs from Schemas\Components, not Forms\Components
- 📝 **Migration**: `2025_11_09_082445` - Dropped unique constraint, added regular index
- 📝 **Updated documentation** - Complete troubleshooting guide with examples
- 🔒 **Delete protection** - Cannot delete images still in use
- 🔗 **Relationship viewer** - See where image is used with direct link
- ⚡ **Detach action** - Remove references before deletion

### v1.1.0 (2025-11-09) - Deprecated
- ❌ Custom ImagePickerGrid with Alpine.js (caused conflicts)
- ❌ ViewField approach (not recommended)
- Issues: Loading spinner forever, CSS not applying, Livewire conflicts

### v1.0.0 (2025-11-09)
- Initial documentation
- Image model with polymorphic relationships
- ImageResource for centralized management
- ImagesRelationManager for Products and Articles
- WebP auto-conversion
- Soft deletes with reference cleanup

### Planned for v1.3.0
- Lazy loading pagination (infinite scroll for 1000+ images)
- Advanced filters (by dimensions, date, model type)
- Bulk operations from ImageResource (delete, move, reassign)
- Image cropping interface with Intervention Image
- Thumbnail size customization per entity

---

**Questions or Issues?**  
Please refer to [FILAMENT_RULES.md](../FILAMENT_RULES.md) for general guidelines, or open an issue in the project repository.
