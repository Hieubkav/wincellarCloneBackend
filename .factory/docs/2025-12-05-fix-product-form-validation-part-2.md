## Vấn đề
Lỗi `foreach() argument must be of type array|object, null given` vẫn xảy ra dù đã fix trước đó. Nguyên nhân là các Select component thiếu default value handling.

## Nguyên nhân chi tiết
1. **Select::make('categories')**: Có `->multiple()` nhưng thiếu `->default([])` - khi validation chạy trên null state
2. **Single-select attribute fields**: Không có `->default(null)` hoặc `->nullable()` - Filament yêu cầu xử lý explicit

## Giải pháp

### 1. Thêm default([]) cho categories Select
```php
Select::make('categories')
    ->label('Danh mục')
    ->relationship(...)
    ->searchable()
    ->multiple()
    ->default([])  // THÊM MỚI
    ->preload()
    ->live()
    ...
```

### 2. Thêm default(null) và nullable() cho single-select attribute fields
```php
$field = Select::make($fieldName)
    ->label($group->name)
    ->options($group->terms?->pluck('name', 'id') ?? [])
    ->searchable()
    ->preload()
    ->default(null)   // THÊM MỚI
    ->nullable()      // THÊM MỚI
    ->hidden(fn () => $group->terms?->isEmpty() ?? true);
```

## Files cần sửa
- `app/Filament/Resources/Products/ProductResource.php` (2 chỗ)

Confirm để tôi thực hiện?