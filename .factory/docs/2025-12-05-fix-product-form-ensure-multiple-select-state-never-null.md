## Vấn đề gốc
Filament Select.php line 606 gọi `foreach ($state as $value)` nhưng `$state` là `null` cho multiple Select. Điều này xảy ra vì:
1. Dynamic schema rebuild khi `type_id` thay đổi
2. `afterStateHydrated` chưa kịp chạy trước validation
3. State mặc định là `null` thay vì `[]`

## Giải pháp
Sử dụng `->dehydrateStateUsing()` để đảm bảo state luôn là array cho multiple Select, VÀ wrap options trong closure để lazy evaluate.

### Fix trong getAttributeFields():

```php
$field = Select::make($fieldName)
    ->label($group->name)
    ->options(fn () => $group->terms?->pluck('name', 'id')->toArray() ?? [])  // Wrap trong closure + toArray()
    ->searchable()
    ->preload()
    ->hidden(fn () => $group->terms?->isEmpty() ?? true);

if ($isMultiple) {
    $field->multiple()
        ->default([])
        ->dehydrateStateUsing(fn ($state) => $state ?? [])  // THÊM MỚI: đảm bảo không null
        ->afterStateHydrated(function ($state, callable $set) use ($fieldName) {
            $set($fieldName, $state ?? []);
        });
} else {
    // Single select cũng cần xử lý
    $field->default(null)
        ->nullable();
}
```

### Bỏ `->default(null)->nullable()` khỏi vị trí cũ
Vì nó được đặt TRƯỚC `if ($isMultiple)`, gây conflict khi multiple override.

## Files cần sửa
- `app/Filament/Resources/Products/ProductResource.php`

Confirm để tôi thực hiện?