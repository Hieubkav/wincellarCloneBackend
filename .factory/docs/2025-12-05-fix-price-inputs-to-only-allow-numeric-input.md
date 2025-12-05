## Vấn đề
Ở form tạo/sửa product, 2 ô **Giá bán** (`price`) và **Giá gốc** (`original_price`) đang cho phép nhập chữ vì:
- `->type('text')` đặt HTML input type là text
- Mask tiền tệ `$money()` không chặn ký tự chữ khi gõ

## Giải pháp
Thêm `extraInputAttributes` với Alpine.js để chặn ký tự không phải số ngay khi gõ:

```php
TextInput::make('price')
    ->label('Giá bán')
    ->type('text')
    ->prefix('₫')
    ->extraInputAttributes([
        'inputmode' => 'numeric',
        'x-on:keypress' => 'if (!/[0-9]/.test($event.key)) $event.preventDefault()',
        'x-on:paste' => '$event.preventDefault(); let nums = $event.clipboardData.getData("text").replace(/[^0-9]/g, ""); document.execCommand("insertText", false, nums)',
    ])
    // ... giữ nguyên các config khác
```

## Chi tiết thay đổi
**File:** `app/Filament/Resources/Products/ProductResource.php`

Thêm `extraInputAttributes` vào cả 2 field:
1. `price` (Giá bán) - dòng ~89
2. `original_price` (Giá gốc) - dòng ~99

**Chức năng:**
- `inputmode="numeric"` - Mobile hiện bàn phím số
- `x-on:keypress` - Chặn nhập ký tự không phải số (0-9)
- `x-on:paste` - Khi paste, tự động lọc chỉ giữ số

## Lưu ý
- Giữ `->type('text')` vì cần cho mask format tiền tệ (dấu phẩy nghìn)
- Validation server-side vẫn giữ nguyên (`rules(['nullable', 'integer'])`)
- Không ảnh hưởng đến logic lưu data hiện tại