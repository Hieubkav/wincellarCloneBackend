# Filament 4.x - Coding Rules & Standards

> **QUAN TRỌNG**: Đây là tài liệu rule chính thức cho Filament 4.x trong dự án này.
> Luôn tuân thủ các quy tắc dưới đây khi làm việc với Filament.

## 📚 Tài liệu tham khảo
- **Vendor code**: `vendor/filament/` - Đọc source code để hiểu sâu
- **Docs chính thức**: https://filamentphp.com/docs/4.x

---

## 🎨 UI/UX Standards

### Navigation Badge (Hiển thị số lượng)
- ✅ **Resource quan trọng** (Product, Order, User...) PHẢI có badge
- Hiển thị số lượng record active/total
  ```php
  public static function getNavigationBadge(): ?string
  {
      return (string) static::getModel()::where('active', true)->count();
  }
  
  public static function getNavigationBadgeColor(): ?string
  {
      return 'success'; // hoặc 'warning', 'danger'
  }
  ```

### Actions & Buttons
- ❌ **KHÔNG dùng ViewAction**: Chỉ dùng EditAction/DeleteAction
- ✅ **Mọi record đơn**: BẮT BUỘC có Edit + Delete
  ```php
  ->recordActions([
      EditAction::make()->iconButton(),
      DeleteAction::make()->iconButton(),
  ])
  ```
- ✅ **Mọi list**: BẮT BUỘC có Bulk Delete
  ```php
  ->bulkActions([
      BulkActionGroup::make([
          DeleteBulkAction::make(),
      ]),
  ])
  ```
- ✅ **Nút tạo mới**: Dùng `->label('Tạo')`
  ```php
  Actions\CreateAction::make()->label('Tạo')
  ```

### Table Columns
- ✅ **Mọi cột**: BẮT BUỘC có `->sortable()` (trừ image, badge nhiều giá trị)
  ```php
  TextColumn::make('name')
      ->searchable()
      ->sortable()  // ← BẮT BUỘC
  ```
- ✅ **Reorderable**: Nếu table có cột `order/position` → BẮT BUỘC drag-drop
  ```php
  ->defaultSort('order', 'asc')
  ->reorderable('order')
  ```
- **Badge**: Dùng cho categories, tags, status
  ```php
  TextColumn::make('status')->badge()
  ```
- **Toggleable**: Cho phép user ẩn/hiện cột không quan trọng
  ```php
  ->toggleable(isToggledHiddenByDefault: true)
  ```
- **Wrap**: Cho text dài có thể xuống dòng
  ```php
  ->wrap()
  ```
- **Image**: Thumbnail nhỏ (60x60px)
  ```php
  ImageColumn::make('image')
      ->disk('public')
      ->width(60)
      ->height(60)
  ```

---

## 🤖 Observer Auto-Generation Rules

### SEO & Meta Fields (Tự động sinh, ẨN khỏi form)
Các field sau **KHÔNG BAO GIỜ** cho user nhập tay:
- ✅ `slug`: Auto từ name/title
- ✅ `meta_title`: Auto từ name/title
- ✅ `meta_description`: Auto từ description (limit 155 chars)
- ✅ `alt` (image): Auto từ model name + order
- ✅ `order/position`: Auto increment

### Observer Pattern:
```php
class ProductObserver
{
    public function creating(Product $product): void
    {
        // Auto slug
        if (empty($product->slug)) {
            $product->slug = $this->generateUniqueSlug($product->name);
        }
        
        // Auto SEO
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }
        
        if (empty($product->meta_description)) {
            $product->meta_description = Str::limit($product->description, 155);
        }
    }
    
    public function updating(Product $product): void
    {
        // Update slug khi name thay đổi
        if ($product->isDirty('name')) {
            $product->slug = $this->generateUniqueSlug($product->name, $product->id);
            
            // Update SEO cũng theo
            if (empty($product->meta_title)) {
                $product->meta_title = $product->name;
            }
        }
    }
}
```

### Image Observer (BẮT BUỘC):
```php
class ImageObserver
{
    public function creating(Image $image): void
    {
        // Auto order
        if ($image->order === null) {
            $image->order = $this->getNextOrder($image);
        }
        
        // Auto alt text
        if (empty($image->alt)) {
            $product = $image->model;
            $image->alt = $image->order === 0 
                ? $product->name 
                : "{$product->name} hình {$image->order}";
        }
    }
    
    public function updating(Image $image): void
    {
        // Xóa file cũ khi upload mới
        if ($image->isDirty('file_path')) {
            Storage::disk('public')->delete($image->getOriginal('file_path'));
        }
    }
    
    public function deleted(Image $image): void
    {
        // Xóa file khi delete record
        Storage::disk('public')->delete($image->file_path);
    }
}
```

### ❌ KHÔNG để user nhập các field này:
- Form KHÔNG có `TextInput::make('slug')`
- Form KHÔNG có `TextInput::make('meta_title')`
- Form KHÔNG có `TextInput::make('meta_description')`
- Form KHÔNG có `TextInput::make('alt')` (trong Image)
- Form KHÔNG có `TextInput::make('order')` (dùng drag-drop)

---

## 📄 Resource Pages

### 1. List Page (ListRecords)

#### Required Components:
```php
public static function table(Table $table): Table
{
    return $table
        // EAGER LOADING - Tránh N+1
        ->modifyQueryUsing(fn (Builder $query) => $query->with(['relation1', 'relation2']))
        
        ->columns([
            // Ảnh (nếu có) - 60x60px
            ImageColumn::make('cover_image')
                ->disk('public')
                ->width(60)
                ->height(60),
            
            // Cột chính - searchable, sortable
            TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->limit(40),
            
            // Relationships - badge, toggleable
            TextColumn::make('category.name')
                ->badge()
                ->toggleable(),
            
            // Giá tiền
            TextColumn::make('price')
                ->money('VND')
                ->sortable(),
            
            // Timestamps - toggleable, hidden by default
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        
        // ACTIONS
        ->recordActions([
            EditAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
        ])
        
        // BULK ACTIONS
        ->bulkActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ])
        
        // PAGINATION
        ->paginated([5, 10, 25, 50, 100, 'all'])
        ->defaultPaginationPageOption(25);
}
```

#### Best Practices:
- ✅ Luôn có eager loading cho relationships
- ✅ Cột chính phải searchable + sortable
- ✅ Giá tiền dùng `->money('VND')`
- ✅ Timestamps mặc định ẩn
- ✅ Actions chỉ dùng iconButton

---

### 2. Create Page (CreateRecord)

#### Required Setup:
```php
class CreateResource extends CreateRecord
{
    protected static string $resource = ResourceClass::class;
    
    // Lưu data tạm (như images, pivot data)
    private array $temporaryData = [];
    
    // BEFORE CREATE - Xử lý data trước khi save
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Tách data không thuộc model chính
        $this->temporaryData = $data['custom_field'] ?? [];
        unset($data['custom_field']);
        
        return $data;
    }
    
    // AFTER CREATE - Xử lý relationships, files...
    protected function afterCreate(): void
    {
        $record = $this->record;
        
        // Lưu pivot relationships
        if (!empty($this->temporaryData)) {
            foreach ($this->temporaryData as $item) {
                $record->relations()->create($item);
            }
        }
        
        // Lưu images với order
        if (!empty($this->images)) {
            $order = 0;
            foreach ($this->images as $path) {
                Image::create([
                    'file_path' => $path,
                    'model_type' => get_class($record),
                    'model_id' => $record->id,
                    'order' => $order++,
                ]);
            }
        }
    }
}
```

#### Best Practices:
- ✅ Dùng `mutateFormDataBeforeCreate()` để xử lý data trước save
- ✅ Dùng `afterCreate()` để xử lý relationships
- ✅ Observer sẽ tự động handle slug, alt text...

---

### 3. Edit Page (EditRecord)

#### Required Setup:
```php
class EditResource extends EditRecord
{
    protected static string $resource = ResourceClass::class;
    
    // FILL FORM - Load data vào form
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load pivot data vào form
        $record = $this->record;
        $data['custom_field'] = $record->customRelations->pluck('id')->toArray();
        
        return $data;
    }
    
    // BEFORE SAVE - Xử lý data trước update
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Lọc bỏ fields không thuộc model
        unset($data['custom_field']);
        
        return $data;
    }
    
    // AFTER SAVE - Sync relationships
    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->data;
        
        // Sync many-to-many
        if (isset($data['categories'])) {
            $record->categories()->sync($data['categories']);
        }
    }
    
    // HEADER ACTIONS
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
```

#### Best Practices:
- ✅ Dùng `mutateFormDataBeforeFill()` để load pivot data
- ✅ Dùng `afterSave()` để sync relationships
- ✅ Header luôn có DeleteAction

---

### 4. Form Schema

#### Structure:
```php
public static function form(Schema $schema): Schema
{
    return $schema
        ->schema([
            Tabs::make()
                ->tabs([
                    // TAB 1: Thông tin chính
                    Tabs\Tab::make('Thông tin chính')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            
                            Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            
                            LexicalEditor::make('description')
                                ->label('Mô tả')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    
                    // TAB 2: Giá & Thông số
                    Tabs\Tab::make('Giá & Thông số')
                        ->schema([
                            TextInput::make('price')
                                ->numeric()
                                ->prefix('₫'),
                            
                            Toggle::make('active')
                                ->default(true),
                        ]),
                    
                    // TAB 3: Hình ảnh (chỉ Create)
                    Tabs\Tab::make('Hình ảnh')
                        ->schema([
                            FileUpload::make('images')
                                ->multiple()
                                ->reorderable()
                                ->imageEditor()
                                ->maxFiles(10)
                                ->saveUploadedFileUsing(fn($file) => /* WebP logic */)
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
}
```

#### Form Field Rules:
- ✅ **TextInput**: required + maxLength
- ✅ **Select**: searchable + preload cho relationships
- ✅ **LexicalEditor**: columnSpanFull
- ✅ **FileUpload**: Luôn convert WebP (xem Storage rules)
- ✅ **Toggle**: default value
- ❌ **NO helperText**: Trừ khi thực sự cần thiết

---

## 🔗 RelationManager

### Standard Structure:
```php
class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $title = 'Hình ảnh';
    
    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                FileUpload::make('file_path')
                    ->required(fn(string $operation) => $operation === 'create')
                    ->image()
                    ->disk('public')
                    ->directory('folder')
                    ->imageEditor()
                    ->saveUploadedFileUsing(fn($file) => /* WebP logic */),
                
                Toggle::make('active')
                    ->default(true),
            ]);
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_path')
                    ->disk('public')
                    ->width(80)
                    ->height(80),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            
            // ⚠️ BẮT BUỘC nếu có cột order/position
            ->defaultSort('order', 'asc')
            ->reorderable('order')  // Kéo thả để sắp xếp
            
            ->headerActions([
                CreateAction::make()->label('Tạo'),
            ])
            
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

### Best Practices:
- ✅ Form phải đơn giản, không quá nhiều field
- ✅ Luôn có bulkActions
- ✅ Nếu có `order` column → dùng `->reorderable('order')`
- ✅ Actions chỉ dùng iconButton

---

## 💾 Storage & File Management

### Upload Rules:
```php
FileUpload::make('icon_path')
    ->disk('public')                    // LUÔN dùng public disk
    ->directory('folder-name')          // Thư mục cụ thể
    ->imageEditor()                     // Editor tích hợp
    ->maxSize(10240)                    // Max 10MB
    ->saveUploadedFileUsing(function ($file) {
        $filename = uniqid('prefix_') . '.webp';
        $path = 'folder/' . $filename;
        
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());
        
        // Resize nếu cần
        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }
        
        // Convert WebP 85%
        $webp = $image->toWebp(quality: 85);
        Storage::disk('public')->put($path, $webp);
        
        return $path;
    })
```

### Observer - Auto Cleanup:
```php
class ImageObserver
{
    // Xóa file cũ khi update
    public function updating(Model $model): void
    {
        if ($model->isDirty('file_path')) {
            $old = $model->getOriginal('file_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        }
    }
    
    // Xóa file khi delete record
    public function deleted(Model $model): void
    {
        if ($model->file_path) {
            Storage::disk('public')->delete($model->file_path);
        }
    }
}
```

### Storage Rules:
- ✅ **Disk**: Luôn dùng `public`
- ✅ **Path**: Lưu relative path trong DB (VD: `products/image.webp`)
- ✅ **Format**: Auto convert WebP 85% quality
- ✅ **Observer**: BẮT BUỘC để auto-delete files
- ✅ **Symlink**: `php artisan storage:link`

---

## 📝 Rich Text Editor (Lexical)

### Model Setup:
```php
use App\Models\Concerns\HasRichEditorMedia;

class Product extends Model
{
    use HasRichEditorMedia;
    
    protected array $richEditorFields = ['description', 'content'];
}
```

### Form Field:
```php
LexicalEditor::make('description')
    ->label('Mô tả')
    ->columnSpanFull()
```

### How it works:
- Auto convert base64 images → files trong `storage/rich-editor-images/`
- Lưu relative paths (`/storage/...`)
- Track trong `rich_editor_media` table (polymorphic)
- Auto cleanup khi content thay đổi hoặc record deleted

---

## 🎯 Common Patterns

### Display Multiple Related Records as Badges:
```php
TextColumn::make('attributes')
    ->badge()
    ->getStateUsing(function ($record) {
        return $record->terms->groupBy('group.name')->map(function($terms, $group) {
            return "{$group}: " . $terms->pluck('name')->join(', ');
        })->values()->toArray();
    })
    ->wrap()
```

### Custom Query for Table:
```php
->modifyQueryUsing(fn (Builder $query) => 
    $query->with(['relation1', 'relation2'])
          ->where('status', 'active')
)
```

### Conditional Form Fields:
```php
TextInput::make('field')
    ->visible(fn (string $operation) => $operation === 'create')
    ->required(fn (Get $get) => $get('type') === 'special')
```

---

## ⚠️ Common Mistakes & Solutions

### ❌ Mistake: N+1 Query Problem
```php
// BAD
TextColumn::make('category.name')
```

### ✅ Solution: Eager Loading
```php
->modifyQueryUsing(fn ($query) => $query->with('category'))
TextColumn::make('category.name')
```

---

### ❌ Mistake: HTML không hiển thị
```php
// BAD
->formatStateUsing(fn($state) => "<strong>{$state}</strong>")
->html()
```

### ✅ Solution: Dùng built-in methods
```php
// Nếu cần list
->getStateUsing(fn($record) => ['Item 1', 'Item 2'])
->listWithLineBreaks()

// Nếu cần badge
->badge()
->getStateUsing(fn($record) => ['Tag1', 'Tag2'])
```

---

### ❌ Mistake: File không tự xóa
```php
// BAD - Không có Observer
```

### ✅ Solution: Luôn có Observer
```php
// Model Observer
protected static function booted()
{
    static::deleting(function ($model) {
        Storage::disk('public')->delete($model->file_path);
    });
}
```

---

## 📊 Performance Tips

1. **Eager Loading**: Luôn dùng `->with()` cho relationships
2. **Pagination**: Default 25, có options [5, 10, 25, 50, 100, 'all']
3. **Toggleable**: Ẩn các cột ít dùng by default
4. **Image Size**: Thumbnail table 60x60px, RelationManager 80x80px
5. **WebP**: Luôn convert để giảm dung lượng

---

## 🔍 Debug Tips

```php
// Log query để check N+1
->modifyQueryUsing(function($query) {
    \DB::listen(fn($q) => \Log::info($q->sql));
    return $query->with('relation');
})

// Dump data trong form
->afterStateUpdated(function($state) {
    dd($state);
})
```

---

## 📚 Checklist - Resource Mới

Khi tạo Resource mới, CHECK đầy đủ:

### UI/UX
- [ ] Navigation badge hiển thị số lượng (nếu resource quan trọng)
- [ ] Mọi cột có `->sortable()` (trừ image/badge)
- [ ] Nếu có `order` column → `->reorderable('order')`
- [ ] Actions: EditAction + DeleteAction (iconButton)
- [ ] BulkActions: DeleteBulkAction
- [ ] Nút tạo: `->label('Tạo')`

### Performance
- [ ] Eager loading: `->modifyQueryUsing(fn($q) => $q->with([...]))`
- [ ] Pagination: default 25
- [ ] Toggleable cho cột ít dùng

### Observer
- [ ] ImageObserver: auto alt, auto order, delete file
- [ ] ModelObserver: auto slug, auto SEO (nếu có)
- [ ] File cleanup khi update/delete

### Form
- [ ] ❌ KHÔNG có field: slug, meta_title, meta_description, alt, order
- [ ] ✅ FileUpload: WebP conversion, imageEditor
- [ ] ✅ LexicalEditor: columnSpanFull, HasRichEditorMedia trait
- [ ] ✅ Select: searchable, preload
- [ ] ✅ Tabs: chia nhóm logic

### RelationManager (nếu có)
- [ ] Reorderable nếu có order
- [ ] BulkActions: DeleteBulkAction
- [ ] Actions: EditAction + DeleteAction (iconButton)

---

## 🔄 Cải thiện FILAMENT_RULES.md

**Khi gặp lỗi/hiểu sai về Filament**:
1. ✅ Tìm hiểu nguyên nhân từ docs/source code
2. ✅ Test solution
3. ✅ **CẬP NHẬT** file này với:
   - Vấn đề gặp phải
   - Giải pháp đúng
   - Example code
   - Thêm vào "Common Mistakes"
4. ✅ Commit với message: `docs(filament): fix rule về [vấn đề]`

**File này là LIVING DOCUMENT** - luôn cập nhật khi học thêm!

---

## 🎯 Kết luận

**Nguyên tắc vàng**:
1. ✅ Đọc source code trong `vendor/filament/` khi không chắc
2. ✅ Luôn eager load relationships
3. ✅ Observer cho file management + SEO fields
4. ✅ WebP cho tất cả images
5. ✅ Reorderable cho mọi table có order
6. ✅ Sortable cho mọi cột
7. ✅ Bulk delete cho mọi list
8. ✅ Simple & Clean UI

**Khi thêm feature mới**:
1. Check examples trong existing resources
2. Tìm trong Filament docs official
3. Đọc source code vendor
4. Test performance (N+1 queries)
5. **CẬP NHẬT file này nếu phát hiện rule mới**
