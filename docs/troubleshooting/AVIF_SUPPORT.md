# AVIF Image Support

## Tình trạng hiện tại

### GD Driver (đang dùng)
**Intervention Image với GD Driver** hỗ trợ:
- ✅ JPEG (.jpg, .jpeg)
- ✅ PNG (.png)
- ✅ GIF (.gif)
- ✅ WebP (.webp)
- ✅ BMP (.bmp)
- ❌ **AVIF (.avif)** - Không hỗ trợ

### Imagick Driver (cần cài đặt)
**Intervention Image với Imagick Driver** có thể hỗ trợ AVIF nếu:
- Imagick PHP extension được cài đặt
- ImageMagick compiled với libavif

## Giải pháp hiện tại

Code đã được thiết kế **flexible** với try-catch:

```php
protected function handleImageUpload(TemporaryUploadedFile $file): string
{
    try {
        // Decode bất kỳ format nào
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());
        
        // Convert sang WebP
        $webp = $image->toWebp(quality: 85);
        Storage::disk('public')->put($path, $webp);
        
        return $path;
    } catch (\Throwable $e) {
        // Nếu không decode được, thông báo cho user
        Notification::make()
            ->title('Không xử lý được ảnh này')
            ->body('File ảnh không được hỗ trợ hoặc bị lỗi. Vui lòng thử file ảnh khác.')
            ->danger()
            ->send();
        
        throw new \Exception('Unable to process image: ' . $e->getMessage());
    }
}
```

## Hành vi với AVIF

### Nếu user upload .avif:
1. GD Driver thử decode → **Fail**
2. Catch exception → Hiển thị: "Không xử lý được ảnh này"
3. User nhận được thông báo thân thiện
4. Website **không crash** ✅

### Workaround cho user:
Nếu có file AVIF:
1. Convert sang PNG/JPEG trước bằng:
   - Online tools: https://convertio.co/avif-jpg/
   - Photoshop/GIMP
   - Command line: `ffmpeg -i image.avif image.jpg`
2. Upload file đã convert

## Nâng cấp hỗ trợ AVIF (optional)

Nếu cần hỗ trợ AVIF native:

### Cách 1: Cài Imagick (Windows)
```bash
# Download Imagick DLL cho PHP 8.2
# https://windows.php.net/downloads/pecl/releases/imagick/

# Copy imagick.dll vào php/ext/
# Thêm vào php.ini:
extension=imagick

# Restart server
```

### Cách 2: Upgrade ImageMagick với libavif
```bash
# Linux
apt-get install libavif-dev
pecl install imagick
```

### Cách 3: Chuyển sang Imagick Driver
```php
// Trong ManagesImageUploads.php
use Intervention\Image\Drivers\Imagick\Driver;

protected function handleImageUpload(TemporaryUploadedFile $file): string
{
    $manager = new ImageManager(new Driver()); // Imagick driver
    // ...
}
```

## Quyết định

**Recommendation: Giữ nguyên GD Driver**

**Lý do:**
- ✅ GD có sẵn, không cần cài thêm
- ✅ Đủ cho 95% use case (JPEG, PNG, WebP)
- ✅ Try-catch đã handle AVIF gracefully
- ✅ User có thể convert AVIF → JPG/PNG dễ dàng
- ❌ AVIF chưa phổ biến lắm (còn mới)
- ❌ Cài Imagick phức tạp trên Windows

**Khi nào nên upgrade:**
- Khi AVIF trở nên phổ biến hơn
- Khi có nhiều user upload AVIF
- Khi deploy lên Linux server (dễ cài Imagick)

---

**Status:** GD Driver (no AVIF support) - OK với try-catch fallback  
**Updated:** 2025-11-12
