# Cấu hình FRONTEND_URL

## Mục đích
Biến `FRONTEND_URL` dùng để tạo link từ backend admin (Filament) sang frontend, ví dụ nút "Xem trên Frontend" ở trang chi tiết sản phẩm.

## Cấu hình

### Local Development
Thêm vào file `.env`:
```env
FRONTEND_URL=http://localhost:3000
```

### Production
Thêm vào file `.env` trên server production:
```env
FRONTEND_URL=https://yourdomain.com
```

hoặc nếu frontend ở subdomain:
```env
FRONTEND_URL=https://shop.yourdomain.com
```

## Sử dụng

### Trong Filament Resource
```php
use App\Filament\Resources\Products\ProductResource;

// Trong header actions:
Actions\Action::make('view_frontend')
    ->label('Xem trên Frontend')
    ->icon('heroicon-o-eye')
    ->url(fn() => ProductResource::getFrontendUrl($this->record))
    ->openUrlInNewTab()
```

### Trong code PHP khác
```php
$frontendUrl = config('app.frontend_url');
// hoặc
$productUrl = config('app.frontend_url') . '/san-pham/' . $product->slug;
```

## Hiện trạng

Đã implement cho:
- ✅ **ProductResource** - Nút "Xem trên Frontend" ở trang Edit và View
- ✅ Config tự động detect local/production dựa vào `FRONTEND_URL` trong `.env`

## Mở rộng

Để thêm nút tương tự cho resource khác (Article, Category, v.v.), copy pattern sau:

```php
// Trong Resource class
public static function getFrontendUrl(YourModel $record): string
{
    $frontendBaseUrl = config('app.frontend_url', 'http://localhost:3000');
    return $frontendBaseUrl . '/your-path/' . $record->slug;
}

// Trong Edit/View page
protected function getHeaderActions(): array
{
    return [
        Actions\Action::make('view_frontend')
            ->label('Xem trên Frontend')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->url(fn() => YourResource::getFrontendUrl($this->record))
            ->openUrlInNewTab(),
        // ... other actions
    ];
}
```
