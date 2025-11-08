Trả lời bằng tiếng việt

Hiểu rõ filament 4x ở trong vendor\filament

Đừng để logic hoặc file quá 500 dòng hãy gọi các file để chia logic và kế thừa hợp lý

Hãy tham khảo E:\Laravel\Laravel12\wincellarClone\wincellarcloneBackend\PLAN.md để hiểu dự án này làm gì chức năng gì nha

## Filament Actions UI
- **KHÔNG dùng ViewAction**: Chỉ dùng Edit/Delete, không có nút Xem (edit = xem)
- Actions trong table chỉ hiển thị icon: `EditAction::make()->iconButton()`, `DeleteAction::make()->iconButton()`
- **Nút tạo mới**: Dùng `->label('Tạo')` thay vì "Tạo mới [Tên]"
  - Ví dụ: `Actions\CreateAction::make()->label('Tạo')`

## Storage & File Management
- **Nguyên tắc**: Ảnh/file lưu trong storage, database chỉ lưu đường dẫn (relative path)
- **Symlink**: BẮT BUỘC `php artisan storage:link` để tạo `public/storage` -> `storage/app/public`
- **Relative Paths**: Luôn dùng `/storage/...` thay vì absolute URLs (tránh lỗi khi đổi domain)

## Image Upload & Optimization  
- **Disk**: `->disk('public')` + `->directory('folder-name')`
- **WebP**: Tự động convert sang WebP 85% quality, resize phù hợp
- **Observer**: BẮT BUỘC tạo Observer xóa file cũ khi update/delete (đăng ký trong AppServiceProvider)

```php
// FileUpload
FileUpload::make('icon_path')->disk('public')->directory('icons')->imageEditor()
    ->saveUploadedFileUsing(fn($file) => /* convert WebP logic */);

// Observer  
public function updating(Model $m): void {
    if ($m->isDirty('icon_path') && $old = $m->getOriginal('icon_path')) {
        Storage::disk('public')->delete($old);
    }
}
```

## Rich Text Editor (Lexical Editor)
- **Package**: `malzariey/filament-lexical-editor` cho description/content fields
- **HasRichEditorMedia Trait**: BẮT BUỘC để auto-handle images
  - Khai báo: `protected array $richEditorFields = ['description'];`
  - Tự động convert base64 → files trong `storage/rich-editor-images/`
  - Lưu relative paths (`/storage/...`) thay vì absolute URLs
  - Track trong `rich_editor_media` table (polymorphic)
  - Cleanup khi content thay đổi hoặc record deleted
- **Command**: `php artisan rich-editor:fix-absolute-urls` để fix URLs cũ

```php
// Model
use App\Models\Concerns\HasRichEditorMedia;
class Product extends Model { 
    use HasRichEditorMedia;
    protected array $richEditorFields = ['description'];
}

// Form
LexicalEditor::make('description')->label('Mô tả')->columnSpanFull();
```

## Database Schema Management
- **Luôn đồng bộ mermaid.rb**: Khi tạo/sửa migration, PHẢI cập nhật file `mermaid.rb` ngay lập tức
- mermaid.rb phải phản ánh chính xác cấu trúc database hiện tại
- Bao gồm: tên bảng, cột, kiểu dữ liệu, constraints, indexes, foreign keys
- Format giống Rails schema.rb để dễ đọc và track changes
