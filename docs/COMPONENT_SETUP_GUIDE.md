# Dynamic Component Management Guide

> **Reference**: Pattern này được documented trong `@/docs/FILAMENT_RULES.md` section "🧩 Dynamic Component Management Pattern"

## Tổng quan

Hệ thống này là một **reusable pattern** cho phép admin dễ dàng quản lý các khối giao diện động thông qua Filament Admin Panel, không cần viết JSON phức tạp.

**Use Case Example**: Home Components - quản lý các section trên trang chủ (Hero Carousel, Product Showcase, Footer...)

### Pattern này giải quyết vấn đề gì?
- ❌ Admin phải viết/edit JSON phức tạp
- ❌ Dễ sai format, thiếu required fields
- ❌ Khó maintain khi thêm component types mới
- ✅ Dropdown + Dynamic Form tự động
- ✅ Type-safe với Enum
- ✅ User-friendly interface

## Các loại Component

### 1. Hero Carousel - Banner chính
Slider banner lớn ở đầu trang
- **Cấu hình**: Danh sách slides với hình ảnh, link, alt text
- **Form fields**:
  - `slides[]`: Repeater
    - `image_id`: Select từ bảng Images
    - `href`: URL link
    - `alt`: Mô tả ảnh

### 2. Dual Banner - 2 banner ngang
Hai banner quảng cáo nằm ngang cạnh nhau
- **Cấu hình**: Đúng 2 banners
- **Form fields**:
  - `banners[]`: Repeater (min: 2, max: 2)
    - `image_id`: Select từ bảng Images
    - `href`: URL link
    - `alt`: Mô tả ảnh

### 3. Category Grid - Lưới danh mục
Lưới hiển thị các danh mục sản phẩm
- **Form fields**:
  - `categories[]`: Repeater
    - `term_id`: Select từ CatalogTerms
    - `image_id`: Select từ Images (optional)

### 4. Favourite Products - Sản phẩm yêu thích
Danh sách sản phẩm được yêu thích/nổi bật
- **Form fields**:
  - `title`: Tiêu đề
  - `subtitle`: Tiêu đề phụ
  - `products[]`: Select từ Products

### 5. Brand Showcase - Giới thiệu thương hiệu
Giới thiệu các thương hiệu đối tác (logo + link)
- **Form fields**:
  - `title`: Tiêu đề
  - `brands[]`: Repeater
    - `image_id`: Select từ bảng Images (logo thương hiệu)
    - `href`: URL link (optional)
    - `alt`: Tên thương hiệu

### 6. Collection Showcase - Bộ sưu tập sản phẩm
Bộ sưu tập sản phẩm theo chủ đề (Rượu Vang, Rượu Mạnh...)
- **Form fields**:
  - `title`: Tiêu đề (required)
  - `subtitle`: Tiêu đề phụ
  - `description`: Mô tả
  - `ctaLabel`: Text nút xem thêm
  - `ctaHref`: Link nút xem thêm
  - `tone`: Giao diện màu (wine/spirit/default)
  - `products[]`: Select từ Products

### 7. Editorial Spotlight - Bài viết nổi bật
Khu vực hiển thị các bài viết/blog nổi bật
- **Form fields**:
  - `label`: Nhãn
  - `title`: Tiêu đề
  - `description`: Mô tả
  - `articles[]`: Select từ Articles

### 8. Footer - Chân trang
Thông tin chân trang với links, thông tin liên hệ
- **Form fields**:
  - `company_name`: Tên công ty
  - `description`: Mô tả công ty
  - `email`: Email
  - `phone`: Số điện thoại
  - `address`: Địa chỉ
  - `social_links[]`: Repeater
    - `platform`: facebook/instagram/youtube/tiktok/zalo
    - `url`: URL link

## Cách sử dụng trong Admin Panel

1. Truy cập: `http://127.0.0.1:8000/admin/home-components`
2. Click "New" để tạo component mới
3. Chọn loại component từ dropdown
4. Form sẽ tự động hiển thị các fields phù hợp
5. Điền thông tin và Save
6. Sử dụng drag-and-drop để sắp xếp thứ tự hiển thị
7. Toggle switch để bật/tắt component

## API Endpoint

Frontend có thể fetch data từ:
```
GET /api/v1/home
```

Response trả về danh sách các components đã được transform, sắp xếp theo thứ tự và chỉ các component đang active.

## Lưu ý kỹ thuật

### Backend Structure
- **Enum**: `App\Enums\HomeComponentType`
- **Model**: `App\Models\HomeComponent`
- **Form Builder**: `App\Filament\Resources\HomeComponents\Schemas\HomeComponentForm`
- **Table**: `App\Filament\Resources\HomeComponents\Tables\HomeComponentsTable`
- **Transformers**: `App\Services\Api\V1\Home\Transformers\*Transformer`
- **Assembler**: `App\Services\Api\V1\Home\HomeComponentAssembler`

### Database Schema
```php
'type' => string(50)        // Component type (hero_carousel, dual_banner, etc.)
'config' => json            // Configuration data
'order' => integer          // Display order
'active' => boolean         // Visibility toggle
```

### Component Types (snake_case)
- hero_carousel
- dual_banner
- category_grid
- favourite_products
- brand_showcase
- collection_showcase
- editorial_spotlight
- footer

## Tính năng

✅ Dropdown chọn component type thay vì viết JSON
✅ Dynamic form builder theo từng loại component
✅ Select boxes cho Products, Articles, Images, Terms
✅ Repeater fields cho danh sách items
✅ Live validation
✅ Drag-and-drop reordering
✅ Toggle active/inactive trực tiếp trên table
✅ Visual feedback với icons và descriptions
✅ Safe delete với confirmation
✅ API transformer tự động xử lý data

## Frontend Integration (Sẽ làm sau)

Frontend sẽ fetch data từ API và render components tương ứng theo type nhận được từ backend.

---

## ⚠️ Common Pitfalls & Lessons Learned

### 1. Namespace Issues với Get utility

**Problem**: 
```php
// ❌ WRONG - TypeError
use Filament\Forms\Get;

Select::make('type')
    ->helperText(fn (Get $get) => self::getDescription($get('type')))
```

**Solution**:
```php
// ✅ CORRECT - Dự án này dùng Schema
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->helperText(fn (Get $get) => self::getDescription($get('type')))
```

**Lý do**: Dự án dùng `Schema` thay vì `Form`, nên namespace khác.

### 2. Column Not Found: Unknown column 'title' in 'images' table

**Problem**: 
```php
// ❌ WRONG - Column 'title' does not exist
->options(fn () => Image::pluck('title', 'id'))

// Error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'title' in 'field list'
```

**Solution**:
```php
// ✅ CORRECT - Use COALESCE to fallback from alt to file_path
->options(fn () => Image::query()
    ->selectRaw("id, COALESCE(NULLIF(alt, ''), file_path) as display_name")
    ->pluck('display_name', 'id')
)
```

**Lý do**: 
- Bảng `images` không có cột `title`, chỉ có `alt` và `file_path`
- `COALESCE(NULLIF(alt, ''), file_path)` = hiển thị `alt` nếu có, nếu không thì hiển thị `file_path`
- `NULLIF(alt, '')` = convert empty string thành NULL để COALESCE fallback về file_path

**Khi nào gặp**: Khi tạo Select field cho Image model trong bất kỳ form nào.

**Áp dụng cho bảng khác**:
Cùng pattern này áp dụng cho mọi bảng không có cột `title`. Ví dụ:
- `products` table: dùng `name` thay vì `title`
  ```php
  // ❌ WRONG
  ->options(fn () => Product::pluck('title', 'id'))
  
  // ✅ CORRECT
  ->options(fn () => Product::pluck('name', 'id'))
  ```
- `articles` table: dùng `title` (nếu có)
- `catalog_terms` table: dùng `name`

**Quy tắc chung**: Luôn kiểm tra migration trước khi viết query pluck!

### 3. Filter với Relationship khi column không tồn tại

**Problem**: 
```php
// ❌ WRONG - Column 'attribute_group_key' does not exist in catalog_terms
->options(fn () => CatalogTerm::where('attribute_group_key', 'brand')->pluck('name', 'id'))

// Error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'attribute_group_key' in 'where clause'
```

**Solution 1 - Use whereHas**:
```php
// ✅ CORRECT - Use whereHas to join with relationship
->options(fn () => CatalogTerm::whereHas('group', function ($query) {
    $query->where('code', 'brand');
})->pluck('name', 'id'))
```

**Solution 2 - Đơn giản hơn**: Đối với brand showcase, thay vì dùng CatalogTerm, chỉ cần chọn ảnh + link:
```php
// ✅ BEST - Đơn giản và linh hoạt hơn
Repeater::make('config.brands')
    ->schema([
        Select::make('image_id')
            ->label('Logo thương hiệu')
            ->options(fn () => Image::query()
                ->selectRaw("id, COALESCE(NULLIF(alt, ''), file_path) as display_name")
                ->pluck('display_name', 'id')
            ),
        TextInput::make('href')->label('Link')->url(),
        TextInput::make('alt')->label('Tên thương hiệu'),
    ])
```

**Lý do**: 
- Bảng `catalog_terms` có FK `group_id` đến bảng `catalog_attribute_groups`
- Bảng `catalog_attribute_groups` có cột `code` (không phải `attribute_group_key`)
- Cần dùng `whereHas()` để filter qua relationship thay vì trực tiếp where trên cột không tồn tại
- **Hoặc**: Với brand showcase, dùng ảnh trực tiếp đơn giản hơn là map qua catalog_terms

**Khi nào gặp**: Khi cần filter theo attribute từ bảng liên quan (relationship), hoặc cân nhắc thiết kế đơn giản hơn.

### 4. Validation cho nested config fields

**Problem**:
```php
// ❌ WRONG - Validation không work cho nested fields
TextInput::make('config.title')
    ->required()
```

**Solution**:
```php
// ✅ CORRECT - Dùng rules array
TextInput::make('config.title')
    ->rules(['required'])
```

### 5. Reset config khi change type

**Problem**:
```php
// ❌ BAD - Reset config làm mất data đang nhập
Select::make('type')
    ->afterStateUpdated(fn ($state, callable $set) => $set('config', null))
```

**Solution**:
```php
// ✅ GOOD - Không cần reset, Livewire tự handle
Select::make('type')
    ->live()
    // Không cần afterStateUpdated
```

**Lý do**: Livewire đã tự động re-render form khi type thay đổi, không cần manual reset.

### 6. Data mutation trong CreateRecord

**Best Practice**:
```php
class CreateHomeComponent extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure config array exists
        if (!isset($data['config'])) {
            $data['config'] = [];
        }
        
        return $data;
    }
}
```

**Lý do**: Đảm bảo config luôn là array, tránh null value gây lỗi.

---

## 🎯 Best Practices

### Enum Design
```php
enum ComponentType: string
{
    case TypeName = 'snake_case_value';
    
    // ✅ Always include these methods
    public function getLabel(): string { /* User-friendly name */ }
    public function getDescription(): string { /* Helper text */ }
    public function getIcon(): string { /* Heroicon name */ }
    
    // ✅ Static helper for dropdown
    public static function options(): array { /* ... */ }
}
```

### Form Field Organization
```php
// ✅ GOOD - Group related fields
Grid::make()
    ->columns(2)
    ->schema([
        TextInput::make('config.title'),
        TextInput::make('config.subtitle'),
        Textarea::make('config.description')->columnSpanFull(),
    ]),

Repeater::make('config.items')
    ->columnSpanFull()
```

### API Transformer Pattern
```php
// 1. Collect all IDs first (avoid N+1)
// 2. Bulk load resources
// 3. Transform with loaded data
// 4. Return null if required data missing

public function transform(HomeComponent $component, Resources $resources): ?array
{
    $config = $this->normalizeConfig($component);
    $items = $this->buildItems($config, $resources);
    
    // ✅ Return null if no valid items
    if (empty($items)) {
        return null;
    }
    
    return $resources->payload($component, ['items' => $items]);
}
```

---

## 📦 Reusability - Áp dụng pattern này cho các use case khác

Pattern này có thể dùng cho:
- **Page Builder**: Dynamic page sections
- **Email Templates**: Different email component types
- **Report Builder**: Dashboard widgets
- **Form Builder**: Dynamic form sections
- **Menu Builder**: Different menu block types (đã áp dụng trong MenuBlocks)

### Template để bắt đầu

1. **Tạo Enum**: Define component types
2. **Tạo Model**: JSON config storage
3. **Tạo Form**: Dynamic fields based on type
4. **Tạo Table**: Visual display with icons/badges
5. **Tạo Transformers**: API response format (nếu cần)

Xem code trong thư mục `app/Filament/Resources/HomeComponents/` làm reference!

---

## 📚 Further Reading

- Filament Forms: https://filamentphp.com/docs/4.x/forms
- Filament Tables: https://filamentphp.com/docs/4.x/tables
- PHP Enums: https://www.php.net/manual/en/language.enumerations.php
- **Project Rules**: `@/docs/FILAMENT_RULES.md`
