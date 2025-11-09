# Filament `allowHtml()` - Deep Dive & Advanced Patterns

> **🎯 Mục tiêu**: Hiểu sâu về kỹ thuật `.allowHtml()` để unlock toàn bộ tiềm năng của Filament Forms
> 
> **💡 Tư duy**: Từ "chọn trong dropdown" → "Rich interactive UI components" trong admin panel

---

## 🧠 Core Concept: HTML as Data Carrier

### Filament mặc định: Escape HTML
```php
Select::make('category')
    ->options([
        'tech' => '<b>Technology</b>',  // Display: &lt;b&gt;Technology&lt;/b&gt;
    ])
```

### Với `.allowHtml()`: Render HTML
```php
Select::make('category')
    ->options([
        'tech' => '<b>Technology</b>',  // Display: **Technology** (bold)
    ])
    ->allowHtml()
```

**Điểm khác biệt**: `allowHtml()` cho phép bạn **control presentation layer** ngay trong data layer.

---

## 📐 Architecture: Traits & Composition

### Source Code Structure
```
vendor/filament/forms/src/Components/Concerns/CanAllowHtml.php
```

```php
trait CanAllowHtml
{
    protected bool | Closure $isHtmlAllowed = false;

    public function allowHtml(bool | Closure $condition = true): static
    {
        $this->isHtmlAllowed = $condition;
        return $this;
    }

    public function isHtmlAllowed(): bool
    {
        return (bool) $this->evaluate($this->isHtmlAllowed);
    }
}
```

### Components sử dụng CanAllowHtml trait:
1. ✅ **Select** - Dropdown với single/multiple select
2. ✅ **CheckboxList** - Multiple checkboxes với grid layout
3. ✅ **MorphToSelect** - Polymorphic relationship selector
4. ❌ **Radio** - KHÔNG support (chưa có trait)

---

## 🔥 Key Insight: Closure Pattern

### Static boolean
```php
->allowHtml(true)   // Always allow
->allowHtml(false)  // Always escape
```

### Dynamic Closure - POWERFUL!
```php
// Conditional based on feature flag
->allowHtml(FeatureFlag::active('rich_dropdowns'))

// Conditional based on user permission
->allowHtml(fn () => auth()->user()->can('view_advanced_ui'))

// Conditional based on record state
->allowHtml(fn (Get $get) => $get('enable_rich_mode'))

// Conditional based on environment
->allowHtml(fn () => app()->environment('local', 'staging'))
```

**💡 Pattern**: Bạn có thể **dynamically enable/disable HTML rendering** based on:
- User roles/permissions
- Feature flags
- Environment
- Record data
- Session state
- A/B testing groups

---

## 🎨 Visual Capabilities Matrix

### What you can render in options:

| Element | Example | Use Case |
|---------|---------|----------|
| **Images** | `<img src="..." />` | Product thumbnails, User avatars |
| **Icons** | `<svg>...</svg>` | Status indicators, Type icons |
| **Badges** | `<span class="badge">New</span>` | Labels, Tags |
| **Multi-line** | `<div>Title<br><small>Subtitle</small></div>` | Rich descriptions |
| **Colors** | `<span style="color: red">Red</span>` | Color pickers, Status |
| **Layouts** | `<div class="flex">...</div>` | Complex structured data |
| **Emojis** | `<span>🔥 Hot</span>` | Visual emphasis |
| **Progress bars** | `<div class="progress">...</div>` | Completion status |

---

## 💎 Advanced Patterns

### Pattern 1: Rich Media Selector

**Image Gallery Selector**:
```php
protected static function getImageGalleryOptions(): array
{
    return Image::active()
        ->limit(100)
        ->get()
        ->mapWithKeys(function ($image) {
            $html = '
                <div style="display: grid; grid-template-columns: 80px 1fr; gap: 12px; padding: 8px;">
                    <img src="' . e($image->url) . '" 
                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" />
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <div style="font-weight: 600; color: #111827;">' . e($image->title) . '</div>
                        <div style="font-size: 0.875rem; color: #6b7280;">' . e($image->category) . '</div>
                        <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 4px;">
                            ' . $image->width . 'x' . $image->height . ' · ' . human_filesize($image->size) . '
                        </div>
                    </div>
                </div>
            ';
            
            return [$image->id => $html];
        })->toArray();
}

Select::make('featured_image_id')
    ->label('Featured Image')
    ->options(fn () => self::getImageGalleryOptions())
    ->allowHtml()
    ->searchable()
    ->preload()
```

**Kết quả**: Dropdown như một mini "file browser" với thumbnails và metadata!

---

### Pattern 2: Status with Visual Indicators

**Order Status Selector**:
```php
protected static function getOrderStatusOptions(): array
{
    return [
        'pending' => '
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #F59E0B;"></div>
                <span style="font-weight: 500;">⏳ Pending</span>
                <span style="font-size: 0.75rem; color: #6b7280; margin-left: auto;">Awaiting confirmation</span>
            </div>
        ',
        'processing' => '
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #3B82F6;"></div>
                <span style="font-weight: 500;">⚙️ Processing</span>
                <span style="font-size: 0.75rem; color: #6b7280; margin-left: auto;">Being prepared</span>
            </div>
        ',
        'shipped' => '
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #8B5CF6;"></div>
                <span style="font-weight: 500;">🚚 Shipped</span>
                <span style="font-size: 0.75rem; color: #6b7280; margin-left: auto;">On the way</span>
            </div>
        ',
        'delivered' => '
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></div>
                <span style="font-weight: 500;">✅ Delivered</span>
                <span style="font-size: 0.75rem; color: #6b7280; margin-left: auto;">Completed</span>
            </div>
        ',
        'cancelled' => '
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #EF4444;"></div>
                <span style="font-weight: 500;">❌ Cancelled</span>
                <span style="font-size: 0.75rem; color: #6b7280; margin-left: auto;">Order cancelled</span>
            </div>
        ',
    ];
}

Select::make('status')
    ->options(fn () => self::getOrderStatusOptions())
    ->allowHtml()
    ->native(false)
```

**Benefit**: Admin nhìn là hiểu ngay status, không cần nhớ màu sắc hay icon.

---

### Pattern 3: User/Team Selector with Avatars

```php
protected static function getUserOptionsWithAvatars(): array
{
    return User::with('team')
        ->whereActive(true)
        ->get()
        ->mapWithKeys(function ($user) {
            $avatarUrl = $user->avatar_url ?? 
                "https://ui-avatars.com/api/?name=" . urlencode($user->name) . 
                "&background=random&color=fff&size=128";
            
            $html = '
                <div style="display: flex; align-items: center; gap: 12px; padding: 4px 0;">
                    <img src="' . e($avatarUrl) . '" 
                         style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #e5e7eb;" />
                    <div>
                        <div style="font-weight: 500; color: #111827;">' . e($user->name) . '</div>
                        <div style="font-size: 0.75rem; color: #6b7280;">' . e($user->email) . '</div>
            ';
            
            if ($user->team) {
                $html .= '<div style="font-size: 0.75rem; color: #9ca3af; margin-top: 2px;">
                            👥 ' . e($user->team->name) . '
                          </div>';
            }
            
            $html .= '
                    </div>
                </div>
            ';
            
            return [$user->id => $html];
        })->toArray();
}

Select::make('assigned_to')
    ->label('Assign to')
    ->options(fn () => self::getUserOptionsWithAvatars())
    ->allowHtml()
    ->searchable()
```

---

### Pattern 4: Product Selector with Price & Stock

```php
protected static function getProductOptionsWithDetails(): array
{
    return Product::with('category')
        ->limit(200)
        ->get()
        ->mapWithKeys(function ($product) {
            $stockColor = $product->stock > 10 ? '#10B981' : ($product->stock > 0 ? '#F59E0B' : '#EF4444');
            $stockText = $product->stock > 10 ? 'In Stock' : ($product->stock > 0 ? 'Low Stock' : 'Out of Stock');
            
            $html = '
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="' . e($product->cover_image_url) . '" 
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;" />
                    <div style="flex: 1;">
                        <div style="font-weight: 500; color: #111827;">' . e($product->name) . '</div>
                        <div style="display: flex; gap: 12px; margin-top: 4px;">
                            <span style="font-size: 0.875rem; color: #6b7280;">
                                💰 ' . number_format($product->price) . ' ₫
                            </span>
                            <span style="font-size: 0.875rem; color: ' . $stockColor . '; font-weight: 500;">
                                📦 ' . $stockText . ' (' . $product->stock . ')
                            </span>
                        </div>
                    </div>
                </div>
            ';
            
            return [$product->id => $html];
        })->toArray();
}
```

---

### Pattern 5: Icon Font Selector (FontAwesome, Heroicons)

```php
protected static function getIconOptions(): array
{
    $icons = [
        'home' => ['icon' => 'heroicon-o-home', 'label' => 'Home', 'desc' => 'Homepage icon'],
        'user' => ['icon' => 'heroicon-o-user', 'label' => 'User', 'desc' => 'User profile'],
        'cog' => ['icon' => 'heroicon-o-cog', 'label' => 'Settings', 'desc' => 'Configuration'],
        'bell' => ['icon' => 'heroicon-o-bell', 'label' => 'Notifications', 'desc' => 'Alerts'],
        'chart' => ['icon' => 'heroicon-o-chart-bar', 'label' => 'Analytics', 'desc' => 'Reports'],
    ];
    
    return collect($icons)->mapWithKeys(function ($data, $key) {
        // Render SVG hoặc icon class
        $iconSvg = getSvgIcon($data['icon']); // Helper function
        
        $html = '
            <div style="display: flex; align-items: center; gap: 10px; padding: 6px 0;">
                <div style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; 
                            background: #f3f4f6; border-radius: 6px;">
                    ' . $iconSvg . '
                </div>
                <div>
                    <div style="font-weight: 500;">' . e($data['label']) . '</div>
                    <div style="font-size: 0.75rem; color: #6b7280;">' . e($data['desc']) . '</div>
                </div>
            </div>
        ';
        
        return [$key => $html];
    })->toArray();
}
```

---

### Pattern 6: Color Palette Selector

```php
protected static function getColorPaletteOptions(): array
{
    $colors = [
        'primary' => ['hex' => '#3B82F6', 'name' => 'Primary Blue', 'desc' => 'Main brand color'],
        'success' => ['hex' => '#10B981', 'name' => 'Success Green', 'desc' => 'Positive actions'],
        'warning' => ['hex' => '#F59E0B', 'name' => 'Warning Orange', 'desc' => 'Caution states'],
        'danger' => ['hex' => '#EF4444', 'name' => 'Danger Red', 'desc' => 'Error states'],
        'purple' => ['hex' => '#8B5CF6', 'name' => 'Purple', 'desc' => 'Special features'],
    ];
    
    return collect($colors)->mapWithKeys(function ($data, $key) {
        $html = '
            <div style="display: flex; align-items: center; gap: 10px; padding: 4px 0;">
                <div style="width: 40px; height: 40px; background: ' . $data['hex'] . '; 
                            border-radius: 6px; border: 2px solid #e5e7eb; 
                            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);"></div>
                <div>
                    <div style="font-weight: 500; color: #111827;">' . e($data['name']) . '</div>
                    <div style="font-size: 0.75rem; color: #6b7280;">' . $data['hex'] . ' · ' . e($data['desc']) . '</div>
                </div>
            </div>
        ';
        
        return [$key => $html];
    })->toArray();
}

Select::make('theme_color')
    ->label('Theme Color')
    ->options(fn () => self::getColorPaletteOptions())
    ->allowHtml()
    ->native(false)
```

---

## 🔒 Security: XSS Prevention Strategy

### ❌ DANGEROUS - Raw user input
```php
// NEVER DO THIS!
$html = '<div>' . $user->bio . '</div>';  // XSS vulnerability!
```

### ✅ SAFE - Escaped data
```php
$html = '<div>' . e($user->bio) . '</div>';  // Laravel's e() helper
```

### Defense in Depth Strategy:

```php
protected static function getSecureOptions(): array
{
    return Model::get()->mapWithKeys(function ($item) {
        // 1. Escape ALL user-provided data
        $safeName = e($item->name);
        $safeDesc = e($item->description);
        
        // 2. Use allowlist for attributes
        $allowedColors = ['red', 'blue', 'green'];
        $color = in_array($item->color, $allowedColors) ? $item->color : 'gray';
        
        // 3. Sanitize URLs
        $url = filter_var($item->url, FILTER_VALIDATE_URL) ? e($item->url) : '#';
        
        // 4. Build HTML with escaped data
        $html = '
            <div style="color: ' . $color . ';">
                <a href="' . $url . '">' . $safeName . '</a>
                <p>' . $safeDesc . '</p>
            </div>
        ';
        
        return [$item->id => $html];
    })->toArray();
}
```

### Content Security Policy Headers:
```php
// In middleware or controller
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:;");
```

---

## ⚡ Performance Optimization

### 1. Lazy Loading với Searchable

```php
Select::make('product_id')
    ->searchable()
    ->getSearchResultsUsing(function (string $search) {
        // Only load what's needed
        return Product::where('name', 'like', "%{$search}%")
            ->limit(25)  // Limit results
            ->get()
            ->mapWithKeys(fn ($p) => [
                $p->id => self::buildProductHtml($p)
            ])
            ->toArray();
    })
    ->allowHtml()
```

**Benefit**: Không load 10,000 products khi form mở, chỉ load khi search!

### 2. Cache Options

```php
protected static function getImageOptionsWithPreview(): array
{
    return Cache::remember('admin_image_options', 3600, function () {
        return Image::active()
            ->limit(200)
            ->get()
            ->mapWithKeys(/* build HTML */)
            ->toArray();
    });
}
```

### 3. Pagination Strategy

```php
// For very large datasets, use searchable WITHOUT preload
Select::make('user_id')
    ->searchable()
    ->preload(false)  // Don't load all on mount
    ->getSearchResultsUsing(function (string $search) {
        return User::search($search)  // Use Scout/Algolia
            ->take(30)
            ->get()
            ->mapWithKeys(/* ... */)
            ->toArray();
    })
    ->allowHtml()
```

---

## 🎯 CheckboxList with HTML - Grid Gallery Pattern

```php
CheckboxList::make('selected_images')
    ->label('Select Images')
    ->options(function () {
        return Image::limit(50)->get()->mapWithKeys(function ($img) {
            $html = '
                <div style="text-align: center;">
                    <img src="' . e($img->url) . '" 
                         style="width: 100px; height: 100px; object-fit: cover; 
                                border-radius: 8px; margin-bottom: 6px;
                                border: 2px solid #e5e7eb;" />
                    <div style="font-size: 0.75rem; font-weight: 500;">' . e($img->alt) . '</div>
                </div>
            ';
            return [$img->id => $html];
        })->toArray();
    })
    ->allowHtml()
    ->columns(4)  // 4 columns grid
    ->gridDirection(GridDirection::Column)
    ->bulkToggleable()
```

**Kết quả**: Gallery checkbox grid với thumbnails, như chọn ảnh trong Google Photos!

---

## 🧩 Combining with Other Filament Features

### 1. allowHtml + Live/Reactive

```php
Select::make('category_id')
    ->options(fn () => self::getCategoryOptions())
    ->allowHtml()
    ->live()  // Real-time updates
    ->afterStateUpdated(function ($state, callable $set) {
        $category = Category::find($state);
        $set('category_icon', $category->icon);
    })

TextInput::make('category_icon')
    ->label('Selected Icon')
    ->disabled()
```

### 2. allowHtml + Searchable + AJAX

```php
Select::make('city_id')
    ->searchable()
    ->getSearchResultsUsing(function (string $search) {
        // AJAX call to external API
        $cities = Http::get('https://api.example.com/cities', ['q' => $search])->json();
        
        return collect($cities)->mapWithKeys(function ($city) {
            $html = '
                <div style="display: flex; gap: 8px;">
                    <img src="' . e($city['flag_url']) . '" style="width: 24px; height: 16px;" />
                    <span>' . e($city['name']) . ', ' . e($city['country']) . '</span>
                </div>
            ';
            return [$city['id'] => $html];
        })->toArray();
    })
    ->allowHtml()
```

### 3. allowHtml + Conditional Display

```php
Select::make('payment_method')
    ->options(function (Get $get) {
        $total = $get('total_amount');
        
        $methods = [
            'cash' => '<div>💵 Cash <small>(Any amount)</small></div>',
            'card' => '<div>💳 Credit Card <small>(Min: 10,000₫)</small></div>',
            'bank' => '<div>🏦 Bank Transfer <small>(Min: 50,000₫)</small></div>',
        ];
        
        // Filter based on amount
        if ($total < 10000) {
            unset($methods['card']);
        }
        if ($total < 50000) {
            unset($methods['bank']);
        }
        
        return $methods;
    })
    ->allowHtml()
    ->live()  // Re-render when total changes
```

---

## 📊 Real-world Use Cases Summary

| Use Case | Components | Pattern |
|----------|------------|---------|
| **Image Library Picker** | Select | Thumbnail + Title + Dimensions |
| **Product Catalog** | Select/CheckboxList | Image + Name + Price + Stock |
| **User Assignment** | Select | Avatar + Name + Email + Role |
| **Category Tree** | Select | Icon + Name + Item Count |
| **Status Selector** | Select | Color dot + Label + Description |
| **Icon Picker** | Select/Radio | SVG Icon + Name + Usage hint |
| **Color Palette** | Select | Color swatch + Hex + Name |
| **File Browser** | CheckboxList | File icon + Name + Size + Date |
| **Team Member Picker** | CheckboxList | Avatar grid + Name |
| **Feature Flags** | CheckboxList | Badge + Title + Description |

---

## 🚀 Future Possibilities

### What you could build:

1. **Mini CMS Block Builder** - Chọn và preview blocks trước khi add
2. **Icon/Emoji Picker** - Visual selector thay vì text input
3. **Font Family Picker** - Preview font trong dropdown
4. **Template Selector** - Preview screenshots của templates
5. **Component Library Browser** - Chọn UI components với live preview
6. **Media Gallery Manager** - Multi-select với thumbnails
7. **User Role Picker** - Show permissions ngay trong options
8. **API Endpoint Selector** - Show method, path, description
9. **Database Connection Picker** - Show status indicator
10. **Workflow State Selector** - Visual flowchart trong dropdown

---

## 💡 Pro Tips

### Tip 1: Extract HTML builders
```php
class OptionBuilder
{
    public static function imageWithDetails(Image $image): string
    {
        return view('admin.partials.image-option', compact('image'))->render();
    }
}

// Usage
->options(fn () => Image::get()->mapWithKeys(fn ($img) => [
    $img->id => OptionBuilder::imageWithDetails($img)
]))
```

### Tip 2: Responsive sizing
```php
// Use viewport units for responsive
style="width: 5vw; max-width: 50px; min-width: 30px;"
```

### Tip 3: Dark mode support
```php
// Detect dark mode in Filament
$isDark = filament()->hasDarkMode() && filament()->hasDarkModeForced();

$bgColor = $isDark ? '#1f2937' : '#f9fafb';
$textColor = $isDark ? '#f9fafb' : '#111827';
```

### Tip 4: Accessibility
```php
// Add ARIA labels
<img src="..." alt="' . e($image->alt) . '" aria-label="' . e($image->alt) . '" />
```

### Tip 5: Loading states
```php
// Show skeleton during search
->getSearchResultsUsing(function (string $search) {
    if (strlen($search) < 2) {
        return [
            '_loading' => '<div style="color: #6b7280;">Type to search...</div>'
        ];
    }
    
    // ... actual search
})
```

---

## 🎓 Mental Model

**Think of `.allowHtml()` as:**

1. **Data Visualization Layer** - Transform boring text into rich UI
2. **In-Place Component Library** - Reusable HTML builders for options
3. **Progressive Enhancement** - Start simple, add richness where needed
4. **Performance Trade-off** - More visual = more HTML = consider caching

**Design Philosophy:**
- ✅ Use for **contextual information** (images, status, metadata)
- ✅ Use for **visual distinction** (colors, icons, badges)
- ❌ Don't use for **interactive elements** (buttons, forms)
- ❌ Don't use for **complex logic** (keep it presentational)

---

## 📚 Further Exploration

### Read these Filament source files:
1. `vendor/filament/forms/src/Components/Concerns/CanAllowHtml.php` - The trait
2. `vendor/filament/forms/src/Components/Select.php` - Implementation
3. `vendor/filament/forms/src/Components/CheckboxList.php` - Grid usage
4. `vendor/filament/forms/docs/03-select.md` - Official examples

### Related Filament features to explore:
- `->native(false)` - JavaScript select vs HTML5 native
- `->searchable()` - AJAX search capabilities
- `->getSearchResultsUsing()` - Custom search logic
- `->getOptionLabelUsing()` - Dynamic label generation
- `->relationship()` - Eloquent relationship options

---

## ✨ Conclusion

`.allowHtml()` không chỉ là một method - nó là **gateway to rich interactive admin UIs**.

**Key Takeaways:**
1. **HTML = Presentation Control** trong data layer
2. **Closure = Dynamic Behavior** based on context
3. **Security First** - Always escape user data
4. **Performance Matters** - Cache, limit, lazy load
5. **Reusability** - Extract helpers, build libraries

**Tư duy mới:**
- ❌ "Select là để chọn text"
- ✅ "Select là để render rich interactive components"

**This unlocks:**
- Gallery selectors
- Avatar pickers  
- Status dashboards
- Icon browsers
- Color palettes
- Product catalogs
- File managers
- ...và vô vàn possibilities khác!

Happy building! 🚀
