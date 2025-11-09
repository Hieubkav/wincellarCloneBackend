---
name: image-management
description: Centralized polymorphic image management system with CheckboxList picker (NO custom Alpine.js), WebP auto-conversion, order management (order=0 for cover), soft deletes with reference cleanup. USE WHEN adding images/gallery to models, implementing image upload, working with ImagesRelationManager, fixing image errors, or any image-related tasks in Laravel.
---

# Image Management - Quick Reference

## When to Activate This Skill

- Adding image gallery to Product/Article/Model
- Implementing single featured image
- Setting up logo/favicon in Settings
- Fixing image upload issues
- Troubleshooting "Unique constraint violation" on order
- Working with ImagesRelationManager
- Implementing image picker (CheckboxList)
- Any image-related development task

## 🎯 System Overview

**Centralized Polymorphic System:**
- ✅ Single `images` table for ALL entities
- ✅ Polymorphic relationships (morphMany/morphOne/belongsTo)
- ✅ Order management (0 = cover/primary image)
- ✅ Auto WebP conversion (85% quality)
- ✅ Soft deletes with reference cleanup via ImageObserver
- ✅ CheckboxList picker (native Filament, NO Alpine.js)

## Quick Patterns

### Pattern 1: Multiple Images (Gallery)

**Model:**
```php
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
```

**Resource:**
```php
public static function getRelations(): array
{
    return [
        ImagesRelationManager::class,  // Auto CRUD, reorder, WebP
    ];
}
```

### Pattern 2: Single Image (BelongsTo)

**Model:**
```php
class Setting extends Model
{
    public function logoImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'logo_image_id');
    }
}
```

**Resource Form:**
```php
Select::make('logo_image_id')
    ->label('Logo')
    ->relationship('logoImage', 'file_path')
    ->getOptionLabelFromRecordUsing(fn($record) => basename($record->file_path))
    ->searchable()
    ->preload();
```

### Pattern 3: CheckboxList Picker (v1.2.0)

**✅ Use native Filament CheckboxList - NO custom ViewField!**

```php
Action::make('selectFromLibrary')
    ->label('Chọn từ thư viện')
    ->modalWidth('7xl')
    ->form(function () {
        $images = Image::query()
            ->where('active', true)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $options = $images->mapWithKeys(function ($image) {
            $filename = basename($image->file_path);
            $imageUrl = $image->url ?? '/images/placeholder.png';
            
            // HTML label với preview
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
                ->columns(3)
                ->gridDirection(GridDirection::Column)
                ->required()
                ->searchable()      // Built-in search
                ->bulkToggleable()  // Select all/deselect all
                ->allowHtml(),      // HTML labels for preview
        ];
    })
    ->action(function (array $data, RelationManager $livewire): void {
        $owner = $livewire->getOwnerRecord();
        $selectedImageIds = $data['image_ids'] ?? [];

        foreach ($selectedImageIds as $imageId) {
            $image = Image::find($imageId);
            if (!$image) continue;

            // Copy image for this owner
            $owner->images()->create([
                'file_path' => $image->file_path,
                'disk' => $image->disk,
                'alt' => $image->alt,
                'width' => $image->width,
                'height' => $image->height,
                'mime' => $image->mime,
                // order auto-assigned by ImageObserver
                'active' => true,
            ]);
        }
    });
```

**Built-in Features:**
- ✅ Search (native Alpine.js)
- ✅ Bulk toggle (select all)
- ✅ Multi-select with checkboxes
- ✅ HTML labels for image preview
- ✅ Responsive columns
- ✅ Dark mode support

## Image Upload Standard

**All uploads MUST:**
1. Convert to WebP (quality: 85)
2. Resize if width > 1200px
3. Store in entity directory (`products/`, `articles/`)
4. Use unique filename: `uniqid('prefix_') . '.webp'`

```php
FileUpload::make('file_path')
    ->disk('public')
    ->directory('products')
    ->imageEditor()
    ->saveUploadedFileUsing(function ($file) {
        $filename = uniqid('product_') . '.webp';
        $path = 'products/' . $filename;
        
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());
        
        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }
        
        $webp = $image->toWebp(quality: 85);
        Storage::disk('public')->put($path, $webp);
        
        return $path;
    });
```

## Order Management

- **`order = 0`**: Cover/primary image (only one)
- **`order > 0`**: Gallery images (auto-incremented)
- **Reorderable**: Use `->reorderable('order')` in table
- **Auto-handled**: ImageObserver prevents conflicts

## ImageObserver Auto-Functions

### Auto Order Assignment
```php
public function creating(Image $image): void
{
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

### Auto Alt Text
```php
if (empty($image->alt)) {
    $owner = $image->model;
    $image->alt = $image->order === 0 
        ? $owner->name 
        : "{$owner->name} hình {$image->order}";
}
```

### Auto File Cleanup
```php
public function deleted(Image $image): void
{
    Storage::disk('public')->delete($image->file_path);
}
```

## Delete Protection (v1.2.0)

**Cannot delete images in use:**

```php
// Automatic validation in DeleteAction
if ($image->model_type && $image->model_id) {
    Notification::make()
        ->danger()
        ->title('Không thể xóa ảnh')
        ->body("Đang được sử dụng bởi {$ownerType} #{$image->model_id}")
        ->send();
    $action->cancel();
}
```

**Detach before delete:**
1. Go to `/admin/images/{id}/edit`
2. See "Relationships" section
3. Click "Gỡ liên kết" button
4. Now can delete

## Common Issues & Solutions

### Issue 1: Unique Constraint Violation

**Error:**
```
SQLSTATE[23000]: Duplicate entry 'App\Models\Product-126-2' 
for key 'images_unique_order_per_model'
```

**Solution (v1.2.0):**
- ✅ ImageObserver auto-handles order
- ✅ No unique constraint in DB (removed)
- ✅ Regular index for performance
- ✅ Just create images, Observer finds next available order

```php
// DON'T manually set order - Observer handles it
$owner->images()->create([
    'file_path' => $path,
    // order will be auto-assigned
]);
```

### Issue 2: Class Not Found (Namespace)

**Error:**
```
Class "Filament\Forms\Components\Tabs" not found
Class "Filament\Schemas\Components\CheckboxList" not found
```

**Solution:**
```php
// ✅ CORRECT imports
use Filament\Schemas\Components\Tabs;            // Layout
use Filament\Forms\Components\CheckboxList;      // Form field
use Filament\Support\Enums\GridDirection;        // Enum
```

### Issue 3: Images Not Displaying

**Solutions:**
1. Run: `php artisan storage:link`
2. Check `APP_URL` in `.env`
3. Verify `config/filesystems.php` disk config

## Key Principles

1. **Single source of truth**: One `images` table
2. **Polymorphic**: Works with ANY model
3. **Order = 0**: Cover/primary image
4. **Observer auto-magic**: alt, order, cleanup
5. **Native components**: CheckboxList, NO Alpine.js
6. **WebP always**: 85% quality, resize to 1200px
7. **Delete protection**: Can't delete images in use

## Critical Success Factors

- ✅ Use morphMany/morphOne/belongsTo correctly
- ✅ Let ImageObserver handle order (don't manually set)
- ✅ Use CheckboxList for picker (not custom ViewField)
- ✅ Always WebP conversion
- ✅ Detach before delete

## Supplementary Resources

**Full architecture guide:**
```
read .claude/skills/image-management/CLAUDE.md
```

**Related skills:**
- **filament-rules**: Namespace & form standards
- **filament-resource-generator**: Auto resource creation

## Quick Commands

```bash
# Storage link
php artisan storage:link

# Check migrations
php artisan migrate:status

# Create image resource
php artisan make:filament-resource Image
```

Follow these patterns → Clean polymorphic image system! 📸
