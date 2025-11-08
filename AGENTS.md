Trả lời bằng tiếng việt

Hiểu rõ filament 4x ở trong vendor\filament

Đừng để logic hoặc file quá 500 dòng hãy gọi các file để chia logic và kế thừa hợp lý

Hãy tham khảo E:\Laravel\Laravel12\wincellarClone\wincellarcloneBackend\PLAN.md để hiểu dự án này làm gì chức năng gì nha

## Filament Actions UI
- Tất cả các action (Edit, Delete, View...) trong table chỉ hiển thị icon, không hiển thị text
- Sử dụng `->iconButton()` cho tất cả recordActions hoặc getTableActions
- Ví dụ: `EditAction::make()->iconButton()`, `DeleteAction::make()->iconButton()`, `ViewAction::make()->iconButton()`
- **Nút tạo mới**: Tất cả CreateAction phải dùng `->label('Tạo')` để chỉ hiển thị "Tạo" thay vì "Tạo mới [Tên]"
  - Ví dụ trong ListRecords page: `Actions\CreateAction::make()->label('Tạo')`

## Image Upload & Optimization
- **Lưu trữ**: Sử dụng Laravel Storage với disk `public` để lưu ảnh
- **Tối ưu tự động**: 
  - Tất cả ảnh upload phải tự động convert sang WebP format
  - Resize ảnh về kích thước phù hợp (ví dụ: icon 200px width)
  - Quality WebP: 85%
  - Sử dụng Intervention Image package
- **Observer Pattern**: 
  - BẮT BUỘC tạo Observer cho mọi Model có upload ảnh
  - Observer phải xóa file ảnh cũ khi:
    - Record bị xóa (deleted event)
    - Ảnh được update thành ảnh mới (updating event, check isDirty)
    - Record bị force delete (forceDeleted event)
  - Đăng ký Observer trong AppServiceProvider::boot()
- **FileUpload Component**:
  - Dùng `->disk('public')` và `->directory('tên-thư-mục')`
  - Thêm `->imageEditor()` để cho phép crop/edit trước khi upload
  - Implement `->saveUploadedFileUsing()` để convert sang WebP
  - Thêm `->helperText()` thông báo ảnh sẽ tự động tối ưu

**Ví dụ implementation**:
```php
// Form
FileUpload::make('icon_path')
    ->image()
    ->disk('public')
    ->directory('icons')
    ->imageEditor()
    ->saveUploadedFileUsing(function ($file) {
        $filename = uniqid() . '.webp';
        $path = 'icons/' . $filename;
        
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());
        $image->scale(width: 200);
        $webp = $image->toWebp(quality: 85);
        
        Storage::disk('public')->put($path, $webp);
        return $path;
    });

// Observer
public function updating(Model $model): void {
    if ($model->isDirty('icon_path')) {
        $old = $model->getOriginal('icon_path');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
    }
}
```

## Rich Text Editor (Lexical Editor)
- **Khi nào dùng**: Tất cả các trường mô tả (description, content...) phải dùng Rich Editor
- **Package**: Sử dụng `malzariey/filament-lexical-editor`
- **Component**: `LexicalEditor::make('field_name')`
- **Storage Symlink**: BẮT BUỘC chạy `php artisan storage:link` sau khi setup project để ảnh hiển thị được
- **Tracking ảnh tự động**:
  - BẮT BUỘC: Model có Rich Editor phải dùng trait `HasRichEditorMedia`
  - Khai báo property `protected array $richEditorFields = ['description', 'content'];`
  - Trait tự động:
    - Convert base64 images thành files trong `storage/app/public/rich-editor-images/`
    - Lưu relative paths (`/storage/...`) thay vì absolute URLs để hoạt động trên mọi môi trường
    - Track ảnh trong bảng `rich_editor_media` với polymorphic relationship
    - Xóa ảnh khi nội dung thay đổi hoặc record bị xóa
  - Command hỗ trợ: `php artisan rich-editor:fix-absolute-urls` để convert absolute URLs sang relative paths

**Ví dụ implementation**:
```php
// Model
use App\Models\Concerns\HasRichEditorMedia;

class Product extends Model 
{
    use HasRichEditorMedia;
    
    protected array $richEditorFields = ['description'];
}

// Resource Form
use Malzariey\FilamentLexicalEditor\LexicalEditor;

LexicalEditor::make('description')
    ->label('Mô tả')
    ->columnSpanFull();
```

**Lưu ý khi deploy**:
- Chạy `php artisan storage:link` trên server production
- Đảm bảo thư mục `storage/app/public` có quyền write
- Ảnh sẽ được lưu tại `storage/app/public/rich-editor-images/`

## Database Schema Management
- **Luôn đồng bộ mermaid.rb**: Khi tạo/sửa migration, PHẢI cập nhật file `mermaid.rb` ngay lập tức
- mermaid.rb phải phản ánh chính xác cấu trúc database hiện tại
- Bao gồm: tên bảng, cột, kiểu dữ liệu, constraints, indexes, foreign keys
- Format giống Rails schema.rb để dễ đọc và track changes
