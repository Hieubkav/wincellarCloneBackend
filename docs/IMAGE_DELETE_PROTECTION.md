# Image Delete Protection

## Overview

Images that are actively used by Products, Articles, or other models cannot be deleted until all references are removed. This prevents accidental deletion of images that would break relationships.

## How It Works

### 1. Delete Validation

When you try to delete an image at `/admin/images`:
- System checks if image has `model_type` and `model_id` set
- If yes, deletion is blocked with notification
- Shows which model is using the image

```php
// ImagesTable.php - Delete action
->before(function (\Filament\Actions\DeleteAction $action, Image $record) {
    // Check if image is in use
    if ($record->model_type && $record->model_id) {
        $ownerType = class_basename($record->model_type);
        
        Notification::make()
            ->danger()
            ->title('Không thể xóa ảnh')
            ->body("Ảnh này đang được sử dụng bởi {$ownerType} #{$record->model_id}. Vui lòng gỡ liên kết trước khi xóa.")
            ->persistent()
            ->send();
        
        // Cancel deletion
        $action->cancel();
    }
})
```

### 2. Relationship Viewer

In the image detail page (`/admin/images/{id}/edit`), you can see:
- **Loại**: Type of owner (Product, Article, etc.)
- **ID**: Owner's ID
- **Tên**: Owner's name (clickable link)
- Direct link to edit the owner

```php
// ImageInfolist.php - Relationships section
Section::make('Relationships / Quan hệ')
    ->description('Ảnh này đang được sử dụng bởi:')
    ->icon('heroicon-o-link')
    ->iconColor(fn (Image $record) => $record->model_type && $record->model_id ? 'warning' : 'gray')
    ->schema([
        TextEntry::make('model_type')->label('Loại')->badge(),
        TextEntry::make('model_id')->label('ID'),
        TextEntry::make('model.name')
            ->label('Tên')
            ->url(fn (Image $record) => getOwnerUrl($record), shouldOpenInNewTab: true),
    ])
```

### 3. Detach Action

Before deleting an image, you can detach it from its owner:

1. Go to `/admin/images/{id}/edit`
2. In "Relationships / Quan hệ" section
3. Click "Gỡ liên kết" button
4. Confirm action
5. Image is now unlinked and can be deleted

```php
// ImageInfolist.php - Detach action
Action::make('detach')
    ->label('Gỡ liên kết')
    ->icon('heroicon-o-link-slash')
    ->color('danger')
    ->requiresConfirmation()
    ->modalHeading('Gỡ liên kết ảnh')
    ->modalDescription('Bạn có chắc muốn gỡ liên kết ảnh này? Ảnh sẽ không bị xóa, chỉ gỡ khỏi owner.')
    ->modalSubmitActionLabel('Gỡ liên kết')
    ->visible(fn (Image $record) => $record->model_type && $record->model_id)
    ->action(function (Image $record) {
        $record->model_type = null;
        $record->model_id = null;
        $record->save();
        
        Notification::make()
            ->success()
            ->title('Đã gỡ liên kết')
            ->body('Bây giờ bạn có thể xóa ảnh này.')
            ->send();
    })
```

## Workflow

### Safe Image Deletion

1. **Try to delete image** → Get warning if in use
2. **Click on owner link** → Opens owner's edit page in new tab
3. **Remove image from owner**:
   - Option A: Delete image from owner's images manager
   - Option B: Use "Gỡ liên kết" button in image detail
4. **Delete image** → Now allowed since no references exist

### Bulk Deletion

When bulk deleting images:
- System checks all selected images
- If any are in use, entire operation is cancelled
- Shows count of images that are in use
- Must detach all before bulk delete

## Examples

### Example 1: Product Image
```
Image ID: 123
Owner: Product #456 "Hennessy XO"
Status: ❌ Cannot delete
```

**Steps to delete:**
1. Go to `/admin/images/123/edit`
2. See: "Đang được sử dụng bởi Product #456"
3. Click "Hennessy XO" link → Opens product edit page
4. In product's image manager, delete this image
5. Go back to `/admin/images/123`
6. Now can delete ✅

### Example 2: Unused Image
```
Image ID: 789
Owner: None
Status: ✅ Can delete
```

**Steps to delete:**
1. Go to `/admin/images/789`
2. See: "Không có owner"
3. Click delete → Immediate deletion ✅

## Benefits

- ✅ **Prevents broken relationships** - No orphaned references
- ✅ **Clear warnings** - Know exactly what's blocking deletion
- ✅ **Easy navigation** - Direct links to owners
- ✅ **Flexible detachment** - Multiple ways to remove references
- ✅ **Bulk protection** - Safe bulk operations

## Technical Details

### Database Fields Checked
```php
// Image is "in use" if both are set:
$image->model_type // e.g., 'App\Models\Product'
$image->model_id   // e.g., 126
```

### Null Values = Unused
```php
// Image is "unused" if:
$image->model_type === null
$image->model_id === null
```

### Detach Effect
```php
// After detach:
$image->model_type = null; // Was: 'App\Models\Product'
$image->model_id = null;   // Was: 126
// Image still exists in database, just unlinked
```

## Related Files

- `app/Filament/Resources/Images/Tables/ImagesTable.php` - Delete validation
- `app/Filament/Resources/Images/Schemas/ImageInfolist.php` - Relationship viewer + Detach action
- `app/Models/Image.php` - Polymorphic relationship definition

## See Also

- [@/docs/IMAGE_MANAGEMENT.md](./IMAGE_MANAGEMENT.md) - Complete image system documentation
- [@/docs/filament/FILAMENT_RULES.md](./filament/FILAMENT_RULES.md) - Filament coding conventions
