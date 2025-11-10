# Filament Resource Generator - Comprehensive Guide

Complete guide to generating standardized Filament resources with correct namespaces, Vietnamese labels, Observer patterns, and image management integration.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Step-by-Step Workflow](#step-by-step-workflow)
3. [Code Templates](#code-templates)
4. [Common Patterns](#common-patterns)
5. [Advanced Features](#advanced-features)
6. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Understanding the Project Structure

This project uses **custom Filament namespaces**:
- ❌ **NOT** `Filament\Forms\Form`
- ✅ **USE** `Filament\Schemas\Schema`

**Critical distinction:**
- **Layout components** → `Filament\Schemas\Components\*` (Tabs, Grid, Section, Fieldset)
- **Form fields** → `Filament\Forms\Components\*` (TextInput, Select, Toggle, etc.)
- **Get utility** → `Filament\Schemas\Components\Utilities\Get`

### Vietnamese UI Standard

All user-facing text must be in Vietnamese:
- Navigation labels
- Form field labels
- Table column headers
- Action labels
- Validation messages

---

## Step-by-Step Workflow

### Step 1: Gather Requirements

**Ask user these questions:**

1. **Model name** (singular): Product, Category, Article, etc.
2. **Has images?**
   - None
   - Single featured image
   - Gallery (multiple images)
3. **Has relationships?**
   - BelongsTo (category_id)
   - BelongsToMany (categories, tags)
   - HasMany (variants, sizes)
4. **SEO fields?** Usually yes (slug, meta_title, meta_description)
5. **Ordering?** Need drag-drop reordering? (requires `order` column)
6. **Soft deletes?** Usually yes
7. **Status toggle?** Usually `active` boolean

**Example conversation:**
```
User: "Tạo resource cho Product"

You ask:
- Có gallery ảnh không? → Yes, gallery
- Thuộc category không? → Yes, belongsTo Category
- Có tags không? → Yes, belongsToMany Tag
- Cần reorder không? → Yes
- Active toggle? → Yes
```

---

### Step 2: Generate Base Resource

```bash
php artisan make:filament-resource Product --generate
```

**Output structure:**
```
app/Filament/Resources/Products/
├── ProductResource.php
├── Pages/
│   ├── ListProducts.php
│   ├── CreateProduct.php
│   └── EditProduct.php
```

---

### Step 3: Complete Resource Implementation

#### 3.1 Imports Section

```php
<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products;
use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use Filament\Resources\Resource;

// Layout components (Schemas namespace)
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;

// Form fields (Forms namespace)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CheckboxList;

// Table
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

// Actions
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

// Schema
use Filament\Schemas\Schema;

// RelationManagers
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
```

#### 3.2 Resource Configuration

```php
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    
    // Vietnamese labels
    protected static ?string $navigationLabel = 'Sản phẩm';
    protected static ?string $modelLabel = 'Sản phẩm';
    protected static ?string $pluralModelLabel = 'Các sản phẩm';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationGroup = 'Quản lý sản phẩm';
    
    // Record title for breadcrumbs
    protected static ?string $recordTitleAttribute = 'name';
    
    // Navigation badge (shows count)
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('active', true)->count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::where('active', true)->count();
        return $count > 10 ? 'success' : 'warning';
    }
}
```

#### 3.3 Form Schema

```php
public static function form(Schema $schema): Schema
{
    return $schema->schema([
        Tabs::make('Tabs')->tabs([
            
            // Tab 1: Main information
            Tabs\Tab::make('Thông tin chính')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Tên sản phẩm')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->helperText('Tên hiển thị của sản phẩm'),
                        
                        Select::make('category_id')
                            ->label('Danh mục')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Tên danh mục')
                                    ->required(),
                            ]),
                    ]),
                    
                    Grid::make(2)->schema([
                        TextInput::make('price')
                            ->label('Giá')
                            ->numeric()
                            ->prefix('₫')
                            ->required()
                            ->minValue(0),
                        
                        TextInput::make('stock')
                            ->label('Số lượng')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),
                    
                    RichEditor::make('description')
                        ->label('Mô tả')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'bulletList',
                            'orderedList',
                            'link',
                        ])
                        ->columnSpanFull(),
                    
                    Toggle::make('active')
                        ->label('Đang hiển thị')
                        ->default(true)
                        ->helperText('Hiển thị sản phẩm trên trang chủ'),
                ]),
            
            // Tab 2: Relationships (if any)
            Tabs\Tab::make('Danh mục & Tags')
                ->schema([
                    CheckboxList::make('tags')
                        ->label('Tags')
                        ->relationship('tags', 'name')
                        ->searchable()
                        ->bulkToggleable()
                        ->columns(3),
                ])
                ->visible(fn() => class_exists(Tag::class)),
            
            // Tab 3: Advanced (if needed)
            Tabs\Tab::make('Nâng cao')
                ->schema([
                    DateTimePicker::make('published_at')
                        ->label('Xuất bản lúc')
                        ->default(now())
                        ->displayFormat('d/m/Y H:i'),
                    
                    // Note: SEO fields hidden, auto-generated by Observer
                ])
                ->badge(fn($record) => $record && $record->published_at ? '✓' : null),
                
        ])->columnSpanFull(),
    ]);
}
```

**Important notes:**
- ✅ `schema()` method receives `Schema` not `Form`
- ✅ SEO fields (slug, meta_title, meta_description) are **hidden** - Observer auto-generates
- ✅ Use `live(onBlur: true)` for fields that affect others
- ✅ Use `helperText()` for user guidance

#### 3.4 Table Configuration

```php
public static function table(Table $table): Table
{
    return $table
        // Eager loading to prevent N+1
        ->modifyQueryUsing(fn($query) => $query->with(['category', 'tags', 'coverImage']))
        
        // Default sorting
        ->defaultSort('order', 'asc')
        
        // Reorderable (if has order column)
        ->reorderable('order')
        
        ->columns([
            // Cover image
            ImageColumn::make('cover_image.file_path')
                ->label('Ảnh')
                ->disk('public')
                ->width(60)
                ->height(60)
                ->defaultImageUrl(url('/images/placeholder.png')),
            
            // Name (searchable, sortable)
            TextColumn::make('name')
                ->label('Tên')
                ->searchable()
                ->sortable()
                ->limit(40)
                ->tooltip(fn($record) => $record->name),
            
            // Category
            TextColumn::make('category.name')
                ->label('Danh mục')
                ->badge()
                ->color('info')
                ->sortable(),
            
            // Tags
            TextColumn::make('tags.name')
                ->label('Tags')
                ->badge()
                ->separator(',')
                ->limit(3)
                ->listWithLineBreaks()
                ->limitList(2)
                ->expandableLimitedList(),
            
            // Price
            TextColumn::make('price')
                ->label('Giá')
                ->money('VND')
                ->sortable(),
            
            // Stock
            TextColumn::make('stock')
                ->label('Kho')
                ->numeric()
                ->badge()
                ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),
            
            // Active toggle
            ToggleColumn::make('active')
                ->label('Hiển thị'),
            
            // Created at (hidden by default)
            TextColumn::make('created_at')
                ->label('Tạo lúc')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        
        // Filters
        ->filters([
            SelectFilter::make('category_id')
                ->label('Danh mục')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),
            
            TernaryFilter::make('active')
                ->label('Trạng thái')
                ->placeholder('Tất cả')
                ->trueLabel('Đang hiển thị')
                ->falseLabel('Đã ẩn'),
        ])
        
        // Record actions (inline)
        ->recordActions([
            ViewAction::make()->iconButton(),
            EditAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
        ])
        
        // Bulk actions
        ->bulkActions([
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->label('Xóa đã chọn')
                    ->modalHeading('Xóa sản phẩm')
                    ->modalDescription('Bạn có chắc muốn xóa các sản phẩm đã chọn?')
                    ->modalSubmitActionLabel('Xóa'),
            ]),
        ])
        
        // Pagination
        ->paginated([10, 25, 50, 100])
        ->defaultPaginationPageOption(25);
}
```

**Key features:**
- ✅ Eager loading via `modifyQueryUsing()`
- ✅ Reorderable if model has `order` column
- ✅ Badge colors based on status
- ✅ Toggleable columns
- ✅ Filters for common fields
- ✅ Inline actions with icons

---

### Step 4: Add Relation Managers

#### 4.1 Generate ImagesRelationManager

```bash
php artisan make:filament-relation-manager ProductResource images file_path
```

#### 4.2 Register in Resource

```php
public static function getRelations(): array
{
    return [
        ImagesRelationManager::class,
    ];
}
```

---

### Step 5: Create Model Observer

#### 5.1 Generate Observer

```bash
php artisan make:observer ProductObserver --model=Product
```

#### 5.2 Implement Observer Logic

```php
<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     */
    public function creating(Product $product): void
    {
        // Auto-generate slug
        if (empty($product->slug)) {
            $product->slug = $this->generateUniqueSlug($product->name);
        }
        
        // Auto-generate SEO fields
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }
        
        if (empty($product->meta_description) && !empty($product->description)) {
            $product->meta_description = Str::limit(strip_tags($product->description), 155);
        }
        
        // Auto-assign order
        if ($product->order === null) {
            $product->order = (Product::max('order') ?? 0) + 1;
        }
    }
    
    /**
     * Handle the Product "updating" event.
     */
    public function updating(Product $product): void
    {
        // Regenerate slug if name changed
        if ($product->isDirty('name')) {
            $product->slug = $this->generateUniqueSlug($product->name, $product->id);
            
            // Update meta_title if it was auto-generated
            if ($product->meta_title === $product->getOriginal('name')) {
                $product->meta_title = $product->name;
            }
        }
        
        // Update meta_description if description changed
        if ($product->isDirty('description') && empty($product->meta_description)) {
            $product->meta_description = Str::limit(strip_tags($product->description), 155);
        }
    }
    
    /**
     * Generate unique slug.
     */
    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;
        
        while (Product::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        
        return $slug;
    }
}
```

#### 5.3 Register Observer

In `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Observers\ProductObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
    }
}
```

---

## Code Templates

### Template 1: Simple Resource (No Images, No Relationships)

```php
// Model: Setting
// Fields: key, value, type, description
// No images, no relationships, no order

public static function form(Schema $schema): Schema
{
    return $schema->schema([
        Section::make('Cài đặt')->schema([
            TextInput::make('key')
                ->label('Khóa')
                ->required()
                ->unique(ignoreRecord: true),
            
            Select::make('type')
                ->label('Loại')
                ->options([
                    'text' => 'Văn bản',
                    'number' => 'Số',
                    'boolean' => 'Đúng/Sai',
                ])
                ->required(),
            
            Textarea::make('value')
                ->label('Giá trị')
                ->required(),
            
            Textarea::make('description')
                ->label('Mô tả')
                ->rows(3),
        ]),
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('key')->label('Khóa')->searchable(),
            TextColumn::make('type')->label('Loại')->badge(),
            TextColumn::make('value')->label('Giá trị')->limit(50),
        ])
        ->filters([])
        ->recordActions([
            EditAction::make()->iconButton(),
        ])
        ->bulkActions([]);
}
```

### Template 2: Resource with Gallery

```php
// Model: Article
// Has: gallery images (many)

// Model relationships
public function images(): MorphMany
{
    return $this->morphMany(Image::class, 'model')->orderBy('order');
}

public function coverImage(): MorphOne
{
    return $this->morphOne(Image::class, 'model')->where('order', 0);
}

// Resource
public static function getRelations(): array
{
    return [
        ImagesRelationManager::class,
    ];
}

// Table
ImageColumn::make('cover_image.file_path')
    ->label('Ảnh bìa')
    ->disk('public'),
```

### Template 3: Resource with Many-to-Many

```php
// Model: Product belongsToMany Tag

// Model
public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class);
}

// Form
CheckboxList::make('tags')
    ->label('Tags')
    ->relationship('tags', 'name')
    ->searchable()
    ->bulkToggleable()
    ->columns(3),

// Table
TextColumn::make('tags.name')
    ->label('Tags')
    ->badge()
    ->separator(','),
```

### Template 4: Resource with Ordering

```php
// Migration
$table->unsignedInteger('order')->default(0)->index();

// Observer
public function creating(Model $model): void
{
    if ($model->order === null) {
        $model->order = (Model::max('order') ?? 0) + 1;
    }
}

// Table
->defaultSort('order', 'asc')
->reorderable('order')
```

---

## Advanced Features

### Feature 1: Conditional Fields

```php
Select::make('type')
    ->label('Loại')
    ->options([
        'simple' => 'Đơn giản',
        'variable' => 'Biến thể',
    ])
    ->live(),

// Show only if type = variable
Repeater::make('variants')
    ->label('Biến thể')
    ->schema([
        TextInput::make('name')->label('Tên'),
        TextInput::make('price')->label('Giá')->numeric(),
    ])
    ->visible(fn(Get $get) => $get('type') === 'variable'),
```

### Feature 2: Computed Badges

```php
TextColumn::make('status')
    ->badge()
    ->getStateUsing(function($record) {
        if ($record->stock === 0) return 'Hết hàng';
        if ($record->stock < 10) return 'Sắp hết';
        return 'Còn hàng';
    })
    ->colors([
        'danger' => 'Hết hàng',
        'warning' => 'Sắp hết',
        'success' => 'Còn hàng',
    ]),
```

### Feature 3: Custom Actions

```php
->recordActions([
    Action::make('duplicate')
        ->label('Nhân bản')
        ->icon('heroicon-o-document-duplicate')
        ->action(function($record) {
            $replica = $record->replicate();
            $replica->name .= ' (Copy)';
            $replica->save();
        })
        ->requiresConfirmation(),
    
    EditAction::make()->iconButton(),
    DeleteAction::make()->iconButton(),
])
```

---

## Advanced Components Library

**All components verified from Filament 4.x source code.**

### Builder (Dynamic Content Blocks)

```php
use Filament\Forms\Components\Builder;

Builder::make('content')
    ->label('Nội dung động')
    ->blocks([
        Builder\Block::make('text')
            ->label('Văn bản')
            ->icon('heroicon-o-document-text')
            ->schema([
                RichEditor::make('content')
                    ->label('Nội dung')
                    ->required(),
            ]),
        
        Builder\Block::make('image')
            ->label('Hình ảnh')
            ->icon('heroicon-o-photo')
            ->schema([
                FileUpload::make('image')
                    ->label('Hình ảnh')
                    ->image()
                    ->required(),
                TextInput::make('caption')
                    ->label('Chú thích'),
            ]),
        
        Builder\Block::make('code')
            ->label('Code')
            ->icon('heroicon-o-code-bracket')
            ->schema([
                CodeEditor::make('code')
                    ->label('Mã nguồn')
                    ->language('php'),
            ]),
    ])
    ->collapsible()
    ->cloneable()
    ->blockNumbers(false)
    ->addActionLabel('Thêm khối'),
```

### Repeater with Relationship

```php
use Filament\Forms\Components\Repeater;

Repeater::make('variants')
    ->label('Biến thể sản phẩm')
    ->relationship('variants')
    ->schema([
        Grid::make(3)->schema([
            TextInput::make('sku')
                ->label('Mã SKU')
                ->required()
                ->unique(ignoreRecord: true),
            
            TextInput::make('price')
                ->label('Giá')
                ->numeric()
                ->prefix('₫')
                ->required(),
            
            TextInput::make('stock')
                ->label('Tồn kho')
                ->numeric()
                ->default(0),
        ]),
        
        Select::make('size')
            ->label('Kích thước')
            ->options(['S', 'M', 'L', 'XL'])
            ->required(),
        
        ColorPicker::make('color')
            ->label('Màu sắc'),
    ])
    ->orderable()
    ->collapsible()
    ->collapsed()
    ->itemLabel(fn($state) => $state['sku'] ?? 'Biến thể mới')
    ->addActionLabel('Thêm biến thể')
    ->defaultItems(0)
    ->reorderable()
    ->cloneable()
    ->grid(2),
```

### Wizard (Multi-step Form)

```php
use Filament\Schemas\Components\Wizard;

Wizard::make([
    Wizard\Step::make('Thông tin cơ bản')
        ->icon('heroicon-o-information-circle')
        ->description('Thông tin sản phẩm')
        ->schema([
            TextInput::make('name')->required(),
            Select::make('category_id')->required(),
            RichEditor::make('description'),
        ])
        ->columns(2),
    
    Wizard\Step::make('Giá & Kho')
        ->icon('heroicon-o-currency-dollar')
        ->schema([
            TextInput::make('price')->numeric()->required(),
            TextInput::make('stock')->numeric()->default(0),
            Toggle::make('active')->default(true),
        ])
        ->columns(2),
    
    Wizard\Step::make('Hình ảnh')
        ->icon('heroicon-o-photo')
        ->schema([
            FileUpload::make('images')
                ->image()
                ->multiple()
                ->maxFiles(5),
        ]),
])
    ->skippable()
    ->persistStepInQueryString()
    ->submitAction(view('filament.submit-button')),
```

### KeyValue (Dynamic Key-Value Pairs)

```php
use Filament\Forms\Components\KeyValue;

KeyValue::make('specifications')
    ->label('Thông số kỹ thuật')
    ->keyLabel('Tên thông số')
    ->valueLabel('Giá trị')
    ->addActionLabel('Thêm thông số')
    ->reorderable()
    ->default([
        'weight' => '1kg',
        'dimensions' => '10x10x10cm',
    ]),
```

### TagsInput

```php
use Filament\Forms\Components\TagsInput;

TagsInput::make('keywords')
    ->label('Từ khóa SEO')
    ->placeholder('Nhập từ khóa...')
    ->suggestions(['smartphone', 'điện thoại', 'công nghệ'])
    ->separator(',')
    ->splitKeys(['Tab', ','])
    ->helperText('Nhấn Tab hoặc dấu phẩy để thêm'),
```

### ToggleButtons (Visual Radio)

```php
use Filament\Forms\Components\ToggleButtons;

ToggleButtons::make('status')
    ->label('Trạng thái')
    ->options([
        'draft' => 'Nháp',
        'published' => 'Xuất bản',
        'archived' => 'Lưu trữ',
    ])
    ->icons([
        'draft' => 'heroicon-o-pencil',
        'published' => 'heroicon-o-check-circle',
        'archived' => 'heroicon-o-archive-box',
    ])
    ->colors([
        'draft' => 'warning',
        'published' => 'success',
        'archived' => 'gray',
    ])
    ->inline()
    ->grouped(),
```

### Slider

```php
use Filament\Forms\Components\Slider;

Slider::make('discount_percent')
    ->label('Giảm giá (%)')
    ->minValue(0)
    ->maxValue(100)
    ->step(5)
    ->suffix('%')
    ->marks([
        0 => '0%',
        25 => '25%',
        50 => '50%',
        75 => '75%',
        100 => '100%',
    ]),
```

### CodeEditor

```php
use Filament\Forms\Components\CodeEditor;

CodeEditor::make('custom_css')
    ->label('CSS tùy chỉnh')
    ->language('css')
    ->lineNumbers()
    ->minHeight('200px')
    ->maxHeight('400px'),
```

### MorphToSelect (Polymorphic)

```php
use Filament\Forms\Components\MorphToSelect;

MorphToSelect::make('commentable')
    ->label('Bình luận cho')
    ->types([
        MorphToSelect\Type::make(Product::class)
            ->titleAttribute('name')
            ->label('Sản phẩm'),
        MorphToSelect\Type::make(Article::class)
            ->titleAttribute('title')
            ->label('Bài viết'),
    ])
    ->searchable()
    ->preload(),
```

### Advanced Table Columns

#### TextColumn with Badge & Icon

```php
use Filament\Tables\Columns\TextColumn;

TextColumn::make('status')
    ->label('Trạng thái')
    ->badge()
    ->color(fn($state) => match($state) {
        'draft' => 'warning',
        'published' => 'success',
        'archived' => 'gray',
        default => 'info',
    })
    ->icon(fn($state) => match($state) {
        'draft' => 'heroicon-o-pencil',
        'published' => 'heroicon-o-check-circle',
        'archived' => 'heroicon-o-archive-box',
        default => 'heroicon-o-document',
    })
    ->iconPosition('after')
    ->formatStateUsing(fn($state) => ucfirst($state))
    ->description(fn($record) => 'Cập nhật: ' . $record->updated_at->diffForHumans())
    ->descriptionIcon('heroicon-o-clock')
    ->wrap()
    ->searchable()
    ->sortable(),
```

#### ImageColumn with Stacking

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('images.file_path')
    ->label('Hình ảnh')
    ->disk('public')
    ->stacked()
    ->circular()
    ->limit(3)
    ->limitedRemainingText()
    ->ring(2)
    ->overlap(4)
    ->tooltip(fn($record) => $record->images->count() . ' ảnh'),
```

#### SelectColumn (Inline Edit)

```php
use Filament\Tables\Columns\SelectColumn;

SelectColumn::make('status')
    ->label('Trạng thái')
    ->options([
        'draft' => 'Nháp',
        'published' => 'Xuất bản',
        'archived' => 'Lưu trữ',
    ])
    ->rules(['required'])
    ->selectablePlaceholder(false)
    ->beforeStateUpdated(function ($record, $state) {
        // Validation logic
        if ($state === 'published' && !$record->content) {
            throw new \Exception('Không thể xuất bản khi chưa có nội dung');
        }
    })
    ->afterStateUpdated(function ($record, $state) {
        // Notification
        Notification::make()
            ->title('Đã cập nhật trạng thái')
            ->success()
            ->send();
    }),
```

#### TextInputColumn (Inline Edit)

```php
use Filament\Tables\Columns\TextInputColumn;

TextInputColumn::make('stock')
    ->label('Tồn kho')
    ->type('number')
    ->rules(['numeric', 'min:0'])
    ->beforeStateUpdated(function ($record, $state) {
        // Log old value
        Log::info("Stock changed from {$record->stock} to {$state}");
    })
    ->afterStateUpdated(function ($record, $state) {
        // Update related records
        $record->updateStockStatus();
    }),
```

### Advanced Filters

#### QueryBuilder Filter

```php
use Filament\Tables\Filters\QueryBuilder;

QueryBuilder::make()
    ->constraints([
        QueryBuilder\Constraints\TextConstraint::make('name')
            ->label('Tên sản phẩm')
            ->icon('heroicon-o-document-text'),
        
        QueryBuilder\Constraints\NumberConstraint::make('price')
            ->label('Giá')
            ->icon('heroicon-o-currency-dollar'),
        
        QueryBuilder\Constraints\DateConstraint::make('created_at')
            ->label('Ngày tạo')
            ->icon('heroicon-o-calendar'),
        
        QueryBuilder\Constraints\BooleanConstraint::make('active')
            ->label('Đang hiển thị')
            ->icon('heroicon-o-eye'),
        
        QueryBuilder\Constraints\RelationshipConstraint::make('category')
            ->label('Danh mục')
            ->icon('heroicon-o-folder')
            ->multiple()
            ->relationship('category', 'name')
            ->selectable(),
    ]),
```

#### Custom Filter with Date Range

```php
use Filament\Tables\Filters\Filter;

Filter::make('created_at')
    ->form([
        DatePicker::make('created_from')
            ->label('Từ ngày')
            ->placeholder('Chọn ngày bắt đầu'),
        DatePicker::make('created_until')
            ->label('Đến ngày')
            ->placeholder('Chọn ngày kết thúc'),
    ])
    ->query(function (Builder $query, array $data): Builder {
        return $query
            ->when(
                $data['created_from'],
                fn($query, $date) => $query->whereDate('created_at', '>=', $date)
            )
            ->when(
                $data['created_until'],
                fn($query, $date) => $query->whereDate('created_at', '<=', $date)
            );
    })
    ->indicateUsing(function (array $data): array {
        $indicators = [];
        
        if ($data['created_from'] ?? null) {
            $indicators[] = Indicator::make('Từ ' . Carbon::parse($data['created_from'])->format('d/m/Y'))
                ->removeField('created_from');
        }
        
        if ($data['created_until'] ?? null) {
            $indicators[] = Indicator::make('Đến ' . Carbon::parse($data['created_until'])->format('d/m/Y'))
                ->removeField('created_until');
        }
        
        return $indicators;
    }),
```

### Bulk Actions

```php
use Filament\Actions\BulkAction;

BulkAction::make('updateStock')
    ->label('Cập nhật kho')
    ->icon('heroicon-o-arrow-path')
    ->form([
        TextInput::make('stock')
            ->label('Số lượng mới')
            ->numeric()
            ->required()
            ->minValue(0),
    ])
    ->action(function (Collection $records, array $data) {
        $records->each(fn($record) => $record->update(['stock' => $data['stock']]));
        
        Notification::make()
            ->title('Đã cập nhật ' . $records->count() . ' sản phẩm')
            ->success()
            ->send();
    })
    ->requiresConfirmation()
    ->deselectRecordsAfterCompletion(),

BulkAction::make('export')
    ->label('Xuất Excel')
    ->icon('heroicon-o-arrow-down-tray')
    ->action(function (Collection $records) {
        return Excel::download(new ProductsExport($records), 'products.xlsx');
    }),
```

---

## Troubleshooting

### Issue 1: Class not found Tabs/Grid

**Problem:**
```
Class "Filament\Forms\Components\Tabs" not found
```

**Solution:**
```php
// Wrong
use Filament\Forms\Components\Tabs;

// Correct
use Filament\Schemas\Components\Tabs;
```

### Issue 2: Observer not firing

**Problem:** SEO fields not auto-generated

**Check:**
1. Observer registered in AppServiceProvider?
2. Observer class namespace correct?
3. Model events firing? (check fillable, guarded)

### Issue 3: Reorderable not working

**Problem:** Drag-drop doesn't save order

**Check:**
1. Model has `order` column?
2. Table has `->reorderable('order')`?
3. Observer sets initial order?

### Issue 4: N+1 queries

**Problem:** Slow page load with many records

**Solution:**
```php
->modifyQueryUsing(fn($query) => $query->with(['category', 'tags', 'coverImage']))
```

---

For related skills:
- Filament standards: `read .claude/skills/filament-rules/SKILL.md`
- Image management: `read .claude/skills/image-management/SKILL.md`
- Form debugging: `read .claude/skills/filament-form-debugger/SKILL.md`
