# Image Upload Error Handling

## Vấn đề
Khi upload ảnh trong Filament, có thể gặp lỗi:
```
Intervention\Image\Exceptions\DecoderException - Unable to decode input
```

## Nguyên nhân
1. **File bị corrupt/hỏng** - File tải về không hoàn chỉnh hoặc bị lỗi
2. **Format không hỗ trợ** - File có extension .jpg nhưng không phải ảnh thật
3. **Metadata lỗi** - Ảnh có EXIF/metadata không hợp lệ
4. **File giả mạo** - File đổi extension thành .jpg nhưng thực chất là .txt, .pdf, v.v.

## Giải pháp đã implement

### 1. Validation trước khi decode
File: `app/Filament/Traits/ManagesImageUploads.php`

```php
protected function validateImageFile(TemporaryUploadedFile $file): void
{
    // Check 1: File tồn tại
    if (!file_exists($filePath)) {
        throw new \Exception('File không tồn tại');
    }

    // Check 2: File size hợp lệ (1KB - 10MB)
    $fileSize = filesize($filePath);
    if ($fileSize < 1024) {
        throw new \Exception('File quá nhỏ (< 1KB)');
    }
    if ($fileSize > 10 * 1024 * 1024) {
        throw new \Exception('File quá lớn (> 10MB)');
    }

    // Check 3: MIME type hợp lệ
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
    if (!in_array($mimeType, $allowedMimes)) {
        throw new \Exception('File không phải ảnh hợp lệ (MIME: ' . $mimeType . ')');
    }

    // Check 4: Có thể đọc được metadata ảnh
    $imageInfo = @getimagesize($filePath);
    if ($imageInfo === false) {
        throw new \Exception('Không thể đọc thông tin ảnh - file có thể bị hỏng');
    }

    // Check 5: Dimensions hợp lệ
    [$width, $height] = $imageInfo;
    if ($width < 10 || $height < 10) {
        throw new \Exception('Ảnh quá nhỏ (tối thiểu 10x10 pixels)');
    }
}
```

### 2. Try-Catch với thông báo thân thiện

```php
protected function handleImageUpload(TemporaryUploadedFile $file): string
{
    try {
        // Validate trước
        $this->validateImageFile($file);

        // Decode và convert
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());
        
        // ... resize và save
        
    } catch (\Intervention\Image\Exceptions\DecoderException $e) {
        // Lỗi decode - thông báo cho user
        \Filament\Notifications\Notification::make()
            ->title('Lỗi upload ảnh')
            ->body('File ảnh không hợp lệ hoặc bị hỏng. Vui lòng chọn file ảnh khác.')
            ->danger()
            ->send();
        
        throw new \Exception('Invalid image file: Unable to decode image');
    } catch (\Throwable $e) {
        // Lỗi khác
        \Filament\Notifications\Notification::make()
            ->title('Lỗi upload ảnh')
            ->body('Đã xảy ra lỗi khi xử lý ảnh: ' . $e->getMessage())
            ->danger()
            ->send();
        
        throw $e;
    }
}
```

## Lợi ích

### Trước khi fix:
❌ Website crash với 500 error  
❌ Stack trace hiển thị cho user  
❌ Không biết nguyên nhân cụ thể  

### Sau khi fix:
✅ Website không crash  
✅ Hiển thị thông báo lỗi rõ ràng  
✅ User biết cách khắc phục (chọn file khác)  
✅ Admin có log chi tiết để debug  

## Hướng dẫn cho User

Nếu gặp lỗi upload ảnh:

1. **Kiểm tra file ảnh:**
   - Thử mở ảnh bằng trình xem ảnh khác (Windows Photos, Paint)
   - Nếu không mở được → file bị hỏng

2. **Convert lại ảnh:**
   - Mở ảnh bằng Paint/Photoshop
   - Save as → chọn format khác (PNG hoặc JPEG)
   - Thử upload file mới

3. **Kiểm tra kích thước:**
   - File size: 1KB - 10MB
   - Dimensions: Tối thiểu 10x10 pixels

4. **Format được hỗ trợ:**
   - JPEG (.jpg, .jpeg)
   - PNG (.png)
   - GIF (.gif)
   - WebP (.webp)
   - BMP (.bmp)

## Logs & Monitoring

Lỗi upload ảnh được log tại:
- `storage/logs/laravel.log`
- Filament notifications (hiển thị cho admin)

Để xem chi tiết lỗi:
```bash
tail -f storage/logs/laravel.log | grep "Invalid image"
```

## Testing

Để test error handling:
1. Tạo file text, đổi extension thành .jpg
2. Upload file này
3. Kiểm tra notification hiển thị: "File ảnh không hợp lệ..."

---

**Updated:** 2025-11-12  
**Related:** ManagesImageUploads trait
