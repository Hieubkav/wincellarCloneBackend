## Vấn đề
Lỗi `foreach() argument must be of type array|object, null given` xảy ra khi submit form tạo Product mà chưa điền đủ dữ liệu. Lỗi xuất hiện trong quá trình Filament validation, TRƯỚC KHI validation custom chạy.

## Nguyên nhân
Trong `ProductResource.php`, `Select::make('categories')` đang dùng **CẢ HAI**:
- `->relationship('categories', 'name')` 
- `->options(function (callable $get) { ... })`

Khi có cả hai, Filament 4.x bị conflict và `getOptions()` trả về `null` thay vì array rỗng khi validation.

## Giải pháp

### 1. Fix Select::make('categories')
Bỏ `->options()` callback, dùng `->relationship()` kết hợp với query filter:

```php
Select::make('categories')
    ->label('Danh mục')
    ->relationship(
        name: 'categories',
        titleAttribute: 'name',
        modifyQueryUsing: fn (Builder $query, callable $get) => $query
            ->when($get('type_id'), fn ($q, $typeId) => $q->where(function ($sub) use ($typeId) {
                $sub->where('type_id', $typeId)->orWhereNull('type_id');
            }))
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('id')
    )
    ->searchable()
    ->multiple()
    ->preload()
    ->live()
    ->disabled(fn (callable $get) => !$get('type_id'))
    ->helperText('Chọn phân loại sp trước; danh mục sẽ lọc theo phân loại sp.')
```

### 2. Đảm bảo Dynamic Attribute Fields an toàn
Trong `getAttributeFields()`, wrap options trong null-safe check:

```php
$field = Select::make($fieldName)
    ->label($group->name)
    ->options($group->terms?->pluck('name', 'id') ?? [])  // null-safe
    ->searchable()
    ->preload()
    ->hidden(fn () => $group->terms?->isEmpty() ?? true);
```

## Files cần sửa
- `app/Filament/Resources/Products/ProductResource.php`

Confirm để tôi thực hiện?