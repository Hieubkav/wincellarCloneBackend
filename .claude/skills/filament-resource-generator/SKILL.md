---
name: filament-resource-generator
description: Automated Filament resource generation with correct namespace imports (Schemas vs Forms), Vietnamese labels, standard structure, Observer patterns, ImagesRelationManager integration. USE WHEN user says 'tạo resource mới', 'create new resource', 'generate Filament resource', 'scaffold admin resource', or wants to add new entity to admin panel.
---

# Filament Resource Generator - Quick Workflow

Generate standardized Filament resources with correct namespaces, Vietnamese labels, and Observer patterns.

## When to Activate This Skill

- User says "tạo resource mới cho [Model]"
- User says "create new resource"
- User wants to "scaffold admin panel"
- Adding new entity to Filament admin

---

## Quick Workflow

### 1. Gather Requirements

Ask user:
- **Model name** (singular): Product, Category, Article
- **Has images?** Gallery or single featured image?
- **Relationships?** BelongsTo, BelongsToMany
- **Need ordering?** Drag-drop reordering (requires `order` column)
- **SEO fields?** Usually yes (slug, meta_title, meta_description)

### 2. Generate Resource

```bash
php artisan make:filament-resource Product --generate
```

**Creates:**
```
app/Filament/Resources/Products/
├── ProductResource.php
├── Pages/
│   ├── ListProducts.php
│   ├── CreateProduct.php
│   └── EditProduct.php
```

### 3. Update Resource

**Critical namespaces:**
```php
// Layout components → Schemas
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

// Form fields → Forms
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

// Schema (NOT Form!)
use Filament\Schemas\Schema;
```

**Vietnamese labels:**
```php
protected static ?string $navigationLabel = 'Sản phẩm';
protected static ?string $modelLabel = 'Sản phẩm';
protected static ?string $pluralModelLabel = 'Các sản phẩm';
protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
```

**Navigation badge:**
```php
public static function getNavigationBadge(): ?string
{
    return (string) static::getModel()::where('active', true)->count();
}
```

### 4. Implement Form

```php
public static function form(Schema $schema): Schema
{
    return $schema->schema([
        Tabs::make()->tabs([
            Tabs\Tab::make('Thông tin chính')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Tên sản phẩm')
                        ->required()
                        ->maxLength(255),
                    
                    Select::make('category_id')
                        ->label('Danh mục')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                ]),
                
                Toggle::make('active')
                    ->label('Đang hiển thị')
                    ->default(true),
            ]),
        ])->columnSpanFull(),
    ]);
}
```

**Note:** SEO fields (slug, meta_*) are HIDDEN - Observer auto-generates.

### 5. Implement Table

```php
public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn($query) => $query->with(['category']))
        ->defaultSort('order', 'asc')
        ->reorderable('order')  // If has order column
        ->columns([
            ImageColumn::make('cover_image.file_path')
                ->label('Ảnh')
                ->width(60),
            
            TextColumn::make('name')
                ->label('Tên')
                ->searchable()
                ->sortable(),
            
            ToggleColumn::make('active')
                ->label('Hiển thị'),
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
```

### 6. Add RelationManagers (if images)

```php
public static function getRelations(): array
{
    return [
        ImagesRelationManager::class,
    ];
}
```

Generate:
```bash
php artisan make:filament-relation-manager ProductResource images file_path
```

### 7. Create Observer

```php
<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    public function creating(Product $product): void
    {
        // Auto slug
        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name);
        }
        
        // Auto SEO
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }
        
        // Auto order
        if ($product->order === null) {
            $product->order = (Product::max('order') ?? 0) + 1;
        }
    }
    
    public function updating(Product $product): void
    {
        if ($product->isDirty('name')) {
            $product->slug = Str::slug($product->name);
        }
    }
}
```

**Register in AppServiceProvider:**
```php
use App\Models\Product;
use App\Observers\ProductObserver;

public function boot(): void
{
    Product::observe(ProductObserver::class);
}
```

---

## Quick Patterns

### With Images (Gallery)

```php
// Model
public function images(): MorphMany
{
    return $this->morphMany(Image::class, 'model');
}

// Resource
public static function getRelations(): array
{
    return [ImagesRelationManager::class];
}
```

### With Categories (Many-to-Many)

```php
// Form
CheckboxList::make('categories')
    ->relationship('categories', 'name')
    ->searchable()
    ->bulkToggleable(),

// Table
TextColumn::make('categories.name')
    ->badge()
    ->separator(','),
```

### With Ordering

```php
// Migration
$table->unsignedInteger('order')->default(0)->index();

// Table
->defaultSort('order', 'asc')
->reorderable('order')

// Observer auto-sets order in creating()
```

---

## Checklist

Before declaring resource complete:

- [ ] Correct namespaces (Schemas vs Forms)
- [ ] Vietnamese labels (100%)
- [ ] Form with Tabs/Grid structure
- [ ] Table with eager loading
- [ ] Reorderable if order column
- [ ] ImagesRelationManager if images
- [ ] Observer for SEO + order
- [ ] Observer registered in AppServiceProvider
- [ ] Navigation badge showing count
- [ ] Tested create/edit/delete

---

## Key Principles

1. **Namespace correctness**: `Schemas` for layouts, `Forms` for fields
2. **Vietnamese first**: All labels tiếng Việt
3. **Observer patterns**: SEO auto-generated, hidden from form
4. **Eager loading**: Always `modifyQueryUsing()` for relations
5. **Standard structure**: Tabs → Grid → Fields

---

## Comprehensive Guide

For detailed implementations, templates, and advanced features:

`read .claude/skills/filament-resource-generator/CLAUDE.md`

**Related skills:**
- Filament standards: `read .claude/skills/filament-rules/SKILL.md`
- Image integration: `read .claude/skills/image-management/SKILL.md`
- Troubleshooting: `read .claude/skills/filament-form-debugger/SKILL.md`
