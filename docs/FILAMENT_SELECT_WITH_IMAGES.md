# Filament Select with Image Preview

> **Kỹ thuật**: Hiển thị hình ảnh preview trong dropdown Select component của Filament
> 
> **Level**: Intermediate  
> **Use Case**: Khi cần chọn hình ảnh, sản phẩm, hoặc bất kỳ record nào có ảnh đại diện

---

## 🎯 Vấn đề

Khi dùng `Select::make('image_id')` trong Filament, dropdown mặc định chỉ hiển thị text:

```
❌ BAD UX:
[  Select Image  ▼]
  ├─ product_1234567.webp
  ├─ banner_9876543.webp
  └─ hero_5432109.webp
```

Admin khó biết chọn ảnh nào vì chỉ thấy tên file, không thấy hình.

---

## ✅ Giải pháp: allowHtml()

Filament Select hỗ trợ render HTML trong options thông qua method `.allowHtml()`:

```
✅ GOOD UX:
[  Select Image  ▼]
  ├─ [🖼️ Thumbnail] Banner khuyến mãi (1920x1080)
  ├─ [🖼️ Thumbnail] Hero slide 1 (1600x900)
  └─ [🖼️ Thumbnail] Product photo (800x800)
```

---

## 📝 Implementation Steps

> **✅ Đã áp dụng trong project**: `HomeComponentForm.php`
> - `getImageOptionsWithPreview()` - Cho image selectors
> - `getProductOptionsWithPreview()` - Cho product selectors

### Step 1: Tạo helper method để format options

```php
class HomeComponentForm
{
    protected static function getImageOptionsWithPreview(): array
    {
        $images = Image::query()
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->limit(200)  // Giới hạn để tránh load quá nhiều
            ->get();

        return $images->mapWithKeys(function ($image) {
            $filename = basename($image->file_path);
            $imageUrl = $image->url ?? '/images/placeholder.png';
            
            // Build HTML string
            $html = '<div style="display: flex; align-items: center; gap: 8px;">';
            $html .= '<img src="' . e($imageUrl) . '" ';
            $html .= 'style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;" />';
            $html .= '<div style="display: flex; flex-direction: column;">';
            $html .= '<span style="font-weight: 500;">' . e($image->alt ?: $filename) . '</span>';
            
            // Optional: hiển thị dimensions
            if ($image->width && $image->height) {
                $html .= '<span style="font-size: 0.75rem; color: #6b7280;">';
                $html .= $image->width . 'x' . $image->height;
                $html .= '</span>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
            
            return [$image->id => $html];
        })->toArray();
    }
}
```

### Step 2: Sử dụng trong Select field

```php
Select::make('image_id')
    ->label('Hình ảnh')
    ->options(fn () => self::getImageOptionsWithPreview())
    ->allowHtml()       // ⭐ KEY: Cho phép render HTML
    ->searchable()      // Vẫn search được theo text
    ->required()
    ->preload()
```

---

## 🎨 HTML Structure Breakdown

```html
<!-- Container: flexbox horizontal -->
<div style="display: flex; align-items: center; gap: 8px;">
    
    <!-- Thumbnail image -->
    <img src="..."
         style="width: 50px; 
                height: 50px; 
                object-fit: cover;           /* Crop ảnh vừa khung */
                border-radius: 4px;          /* Bo góc */
                border: 1px solid #e5e7eb;"  /* Border nhẹ */
    />
    
    <!-- Text info: stacked vertically -->
    <div style="display: flex; flex-direction: column;">
        <!-- Primary text: alt hoặc filename -->
        <span style="font-weight: 500;">Banner khuyến mãi</span>
        
        <!-- Secondary text: dimensions -->
        <span style="font-size: 0.75rem; color: #6b7280;">
            1920x1080
        </span>
    </div>
</div>
```

---

## 🔒 Security: Escape user input

**QUAN TRỌNG**: Luôn dùng `e()` helper để escape HTML entities:

```php
// ✅ CORRECT - Escaped
$html .= '<span>' . e($image->alt) . '</span>';

// ❌ DANGEROUS - XSS vulnerability
$html .= '<span>' . $image->alt . '</span>';
```

**Lý do**: Nếu `alt` text chứa `<script>alert('xss')</script>`, không escape sẽ cho phép execute malicious code.

Laravel's `e()` helper converts:
- `<` → `&lt;`
- `>` → `&gt;`
- `"` → `&quot;`
- `'` → `&#039;`
- `&` → `&amp;`

---

## 🎯 Use Cases

### 1. Image Library Selector
```php
Select::make('cover_image_id')
    ->label('Ảnh bìa')
    ->options(fn () => self::getImageOptionsWithPreview())
    ->allowHtml()
    ->searchable()
```

### 2. Product Selector with Image ✅ (Đã áp dụng)
```php
// ✅ Real implementation in HomeComponentForm.php
protected static function getProductOptionsWithPreview(): array
{
    $products = Product::query()
        ->with('images')
        ->where('active', true)
        ->orderBy('created_at', 'desc')
        ->limit(200)
        ->get();

    return $products->mapWithKeys(function ($product) {
        $imageUrl = $product->cover_image_url ?? '/images/placeholder.png';
        
        $html = '<div style="display: flex; align-items: center; gap: 10px;">';
        $html .= '<img src="' . e($imageUrl) . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;" />';
        $html .= '<div style="display: flex; flex-direction: column; gap: 2px;">';
        $html .= '<span style="font-weight: 500; color: #111827;">' . e($product->name) . '</span>';
        
        $priceHtml = '<div style="display: flex; gap: 8px; align-items: center;">';
        $priceHtml .= '<span style="font-size: 0.875rem; color: #059669; font-weight: 600;">' . number_format($product->price) . ' ₫</span>';
        
        if ($product->original_price && $product->original_price > $product->price) {
            $priceHtml .= '<span style="font-size: 0.75rem; color: #9ca3af; text-decoration: line-through;">' . number_format($product->original_price) . ' ₫</span>';
        }
        $priceHtml .= '</div>';
        
        $html .= $priceHtml;
        $html .= '</div>';
        $html .= '</div>';
        
        return [$product->id => $html];
    })->toArray();
}

// Được sử dụng trong:
// - FavouriteProducts component
// - CollectionShowcase component
Select::make('product_id')
    ->options(fn () => self::getProductOptionsWithPreview())
    ->allowHtml()
    ->searchable()
```

**Features:**
- ✅ Product thumbnail 50x50px
- ✅ Product name
- ✅ Price hiển thị màu xanh (green)
- ✅ Original price gạch ngang nếu có sale
- ✅ Eager load images để tránh N+1

### 3. User Selector with Avatar
```php
protected static function getUserOptionsWithAvatar(): array
{
    return User::active()
        ->get()
        ->mapWithKeys(function ($user) {
            $avatarUrl = $user->avatar_url ?? "https://ui-avatars.com/api/?name=" . urlencode($user->name);
            
            $html = '<div style="display: flex; align-items: center; gap: 8px;">';
            $html .= '<img src="' . e($avatarUrl) . '" style="width: 32px; height: 32px; border-radius: 50%;" />';
            $html .= '<div>';
            $html .= '<div>' . e($user->name) . '</div>';
            $html .= '<div style="font-size: 0.75rem; color: #6b7280;">' . e($user->email) . '</div>';
            $html .= '</div>';
            $html .= '</div>';
            
            return [$user->id => $html];
        })->toArray();
}
```

### 4. Category with Icon
```php
protected static function getCategoryOptionsWithIcon(): array
{
    return Category::get()
        ->mapWithKeys(function ($category) {
            $html = '<div style="display: flex; align-items: center; gap: 8px;">';
            $html .= '<span style="font-size: 1.5rem;">' . e($category->icon_emoji) . '</span>';
            $html .= '<div>';
            $html .= '<div style="font-weight: 500;">' . e($category->name) . '</div>';
            $html .= '<div style="font-size: 0.75rem; color: #6b7280;">' . $category->products_count . ' sản phẩm</div>';
            $html .= '</div>';
            $html .= '</div>';
            
            return [$category->id => $html];
        })->toArray();
}
```

---

## ⚡ Performance Considerations

### 1. Limit số lượng records
```php
// ✅ GOOD - Giới hạn 200 records
->limit(200)

// ❌ BAD - Load hết database
Image::all()  // Có thể có 10,000+ records!
```

### 2. Eager loading relationships
```php
// ✅ GOOD - Eager load để tránh N+1
Product::with('coverImage')
    ->limit(100)
    ->get()

// ❌ BAD - N+1 query problem
Product::limit(100)
    ->get()
    ->each(fn($p) => $p->cover_image_url)  // N queries!
```

### 3. Cache nếu data ít thay đổi
```php
protected static function getImageOptionsWithPreview(): array
{
    return Cache::remember('image_options_preview', 3600, function () {
        return Image::query()
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get()
            ->mapWithKeys(/* ... */)
            ->toArray();
    });
}
```

### 4. Sử dụng `->preload()` thận trọng
```php
Select::make('image_id')
    ->options(fn () => self::getImageOptionsWithPreview())
    ->allowHtml()
    ->searchable()
    ->preload()  // ⚠️ Load tất cả options ngay khi form mở
                 // OK nếu < 200 items
                 // BAD nếu > 1000 items
```

**Alternatives khi có quá nhiều records**:
- Không dùng `->preload()`
- Dùng `->searchable()` với AJAX search
- Implement custom Livewire component với infinite scroll

---

## 🎨 Styling Tips

### Responsive thumbnail size
```php
// Mobile: 40px, Desktop: 50px
$html .= '<img src="..." style="
    width: 40px; 
    height: 40px;
    
    @media (min-width: 768px) {
        width: 50px;
        height: 50px;
    }
" />';
```

### Dark mode support
```php
// Use Tailwind utility classes thay vì inline styles
$html = '<div class="flex items-center gap-2">';
$html .= '<img src="..." class="w-12 h-12 object-cover rounded border border-gray-200 dark:border-gray-700" />';
$html .= '<div class="flex flex-col">';
$html .= '<span class="font-medium text-gray-900 dark:text-gray-100">' . e($image->alt) . '</span>';
$html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $image->width . 'x' . $image->height . '</span>';
$html .= '</div>';
$html .= '</div>';
```

**NOTE**: Filament 4.x có thể không parse Tailwind classes trong options HTML. Nếu không work, quay lại dùng inline styles.

### Add status badges
```php
if (!$image->active) {
    $html .= '<span style="
        font-size: 0.625rem;
        padding: 2px 6px;
        background: #FEE2E2;
        color: #991B1B;
        border-radius: 9999px;
        margin-left: 8px;
    ">Ẩn</span>';
}
```

---

## ❌ Common Mistakes

### 1. Quên `.allowHtml()`
```php
// ❌ WRONG - HTML sẽ hiển thị dạng text
Select::make('image_id')
    ->options(fn () => self::getImageOptionsWithPreview())
    // Missing ->allowHtml()

// Output: <div style="display: flex;">...</div>
```

### 2. Không escape user input
```php
// ❌ DANGEROUS - XSS vulnerability
$html .= '<span>' . $image->alt . '</span>';

// ✅ SAFE
$html .= '<span>' . e($image->alt) . '</span>';
```

### 3. Load quá nhiều records
```php
// ❌ BAD - Load 50,000 images, crash browser
Image::all()->mapWithKeys(/* ... */)

// ✅ GOOD - Giới hạn hợp lý
Image::limit(200)->get()->mapWithKeys(/* ... */)
```

### 4. N+1 query trong loop
```php
// ❌ BAD
Product::get()->mapWithKeys(function ($product) {
    $imageUrl = $product->coverImage->url;  // N queries!
})

// ✅ GOOD
Product::with('coverImage')->get()->mapWithKeys(function ($product) {
    $imageUrl = $product->coverImage?->url ?? '/placeholder.png';
})
```

### 5. Inline styles quá dài
```php
// ❌ BAD - Hard to maintain
$html .= '<div style="display:flex;align-items:center;gap:8px;padding:4px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">';

// ✅ GOOD - Extract to method hoặc constant
const PREVIEW_CONTAINER_STYLE = 'display: flex; align-items: center; gap: 8px;';
$html .= '<div style="' . self::PREVIEW_CONTAINER_STYLE . '">';
```

---

## 📊 Before/After Comparison

### Before: Text-only dropdown ❌
```php
Select::make('image_id')
    ->options(fn () => Image::pluck('file_path', 'id'))
    ->searchable()
```

**UX Issues:**
- Chỉ thấy tên file: `product_1234567890.webp`
- Không biết ảnh nào là ảnh nào
- Phải mở từng ảnh để xem
- Tốn thời gian, dễ chọn nhầm

### After: Image preview dropdown ✅
```php
Select::make('image_id')
    ->options(fn () => self::getImageOptionsWithPreview())
    ->allowHtml()
    ->searchable()
```

**UX Benefits:**
- ✅ Thấy thumbnail ngay trong dropdown
- ✅ Thấy tên file + dimensions
- ✅ Chọn đúng ảnh ngay từ lần đầu
- ✅ Tiết kiệm thời gian, ít lỗi hơn

---

## 🔗 Related Techniques

### 1. CheckboxList with images
```php
CheckboxList::make('selected_images')
    ->options(fn () => self::getImageOptionsWithPreview())
    ->allowHtml()
    ->columns(3)
    ->gridDirection(GridDirection::Column)
```

### 2. Radio buttons with images
```php
Radio::make('featured_image')
    ->options(fn () => self::getImageOptionsWithPreview())
    ->allowHtml()
```

### 3. Custom search callback
```php
Select::make('image_id')
    ->options(fn () => self::getImageOptionsWithPreview())
    ->allowHtml()
    ->searchable()
    ->getSearchResultsUsing(function (string $search) {
        return Image::where('alt', 'like', "%{$search}%")
            ->orWhere('file_path', 'like', "%{$search}%")
            ->limit(50)
            ->get()
            ->mapWithKeys(/* build HTML */)
            ->toArray();
    })
```

---

## 📚 References

- **Filament Select docs**: https://filamentphp.com/docs/4.x/forms/fields/select
- **Project usage**: `app/Filament/Resources/HomeComponents/Schemas/HomeComponentForm.php`
- **Related pattern**: `docs/COMPONENT_SETUP_GUIDE.md` - Dynamic Component Management
- **⭐ Deep dive**: `docs/FILAMENT_ALLOWHTML_DEEP_DIVE.md` - Advanced patterns & architecture
- **Security**: https://laravel.com/docs/blade#displaying-unescaped-data

---

## 🎓 Key Takeaways

1. **`.allowHtml()` unlocks rich UI** trong Select dropdowns
2. **Luôn escape user input** với `e()` helper
3. **Giới hạn số records** để tránh performance issues
4. **Eager load relationships** để tránh N+1 queries
5. **Inline styles work best** với Filament options HTML
6. **Pattern này reusable** cho Product, User, Category, v.v.

---

## 🚀 Next Steps

Thử apply pattern này cho:
- [ ] Product selector trong Orders
- [ ] User selector trong Comments/Reviews
- [ ] Category selector với icon emojis
- [ ] Media library với folder structure preview
- [ ] Color palette selector với color swatches

Happy coding! 🎨
