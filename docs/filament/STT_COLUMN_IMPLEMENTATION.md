# STT Column Implementation

## Mục đích
Thêm cột STT (Số thứ tự) vào tất cả các table trong Filament Admin để dễ theo dõi.

## Cách implement

### 1. BaseResource
Thêm helper method `getRowNumberColumn()` trong `app/Filament/Resources/BaseResource.php`:

```php
public static function getRowNumberColumn(): TextColumn
{
    return TextColumn::make('row_number')
        ->label('STT')
        ->rowIndex()
        ->alignCenter()
        ->width(60);
}
```

**Giải thích:**
- `->rowIndex()`: Tự động tính STT theo pagination (page 1: 1,2,3... page 2: 26,27,28...)
- `->alignCenter()`: Căn giữa
- `->width(60)`: Độ rộng cột 60px

### 2. Usage trong Resources

#### Cách 1: Resource có table() method trực tiếp
**Example: ProductResource.php**
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            static::getRowNumberColumn(), // ← Thêm dòng này
            Tables\Columns\ImageColumn::make('product_image')
                ->label('Ảnh'),
            // ... các columns khác
        ]);
}
```

#### Cách 2: Resource dùng separate Table class
**Example: CatalogTermsTable.php**
```php
use App\Filament\Resources\BaseResource; // ← Import BaseResource

class CatalogTermsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                BaseResource::getRowNumberColumn(), // ← Thêm dòng này
                TextColumn::make('group.name')
                    ->label('Nhóm thuộc tính'),
                // ... các columns khác
            ]);
    }
}
```

## Danh sách Resources đã implement

### ✅ Resources với table() method trực tiếp (4)
1. **ProductResource** - Sản phẩm
2. **ArticleResource** - Bài viết
3. **SocialLinkResource** - Liên kết mạng xã hội
4. **TrackingEventResource** - Sự kiện tracking
5. **VisitorResource** - Khách truy cập

### ✅ Table Classes (9)
1. **ProductTypesTable** - Loại sản phẩm
2. **MenusTable** - Menu
3. **MenuBlocksTable** - Khối menu
4. **ImagesTable** - Hình ảnh
5. **ProductCategoriesTable** - Danh mục sản phẩm
6. **MenuBlockItemsTable** - Mục khối menu
7. **CatalogAttributeGroupsTable** - Nhóm thuộc tính
8. **HomeComponentsTable** - Thành phần trang chủ
9. **CatalogTermsTable** - Giá trị thuộc tính

### ⏭️ Không cần (1)
- **SettingResource** - Chỉ có form edit, không có table

## Tổng kết
- **Tổng số Resources**: 15
- **Đã implement**: 14
- **Không cần**: 1 (SettingResource)
- **Coverage**: 14/14 = 100% ✅

## Kết quả
Tất cả các trang danh sách trong admin giờ đã có cột STT ở đầu tiên, giúp:
- Dễ theo dõi vị trí trong danh sách
- Thuận tiện khi tham chiếu (ví dụ: "Xem dòng số 5")
- STT tự động tính theo pagination

---

**Implemented:** 2025-11-12  
**By:** AI Agent (Droid)
