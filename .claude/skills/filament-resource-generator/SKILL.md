---
name: filament-resource-generator
description: Automated Filament resource generation with correct namespace imports (Schemas vs Forms), Vietnamese labels, standard structure, Observer patterns, ImagesRelationManager integration. USE WHEN user says 'tạo resource mới', 'create new resource', 'generate Filament resource', 'scaffold admin resource', or wants to add new entity to admin panel.
---

# Filament Resource Generator - Automated Workflow

## When to Activate This Skill

- User says "tạo resource mới cho Product"
- User says "create new resource for Category"
- User wants to "generate Filament resource"
- User wants to "scaffold admin panel for Model"
- Adding new entity to Filament admin

## Core Workflow

### Step 1: Understand Requirements

Ask user:
- **Model name**: Singular (Product, Category, Article)
- **Has images**: Gallery (many) or single image?
- **Has categories/tags**: Many-to-many relationships?
- **SEO fields**: Need slug, meta_title, meta_description?
- **Order column**: Need drag-drop reordering?

### Step 2: Generate Resource

Execute artisan command:
```bash
php artisan make:filament-resource [ModelName]
```

Example:
```bash
php artisan make:filament-resource Product --generate
```

**Output:**
```
app/Filament/Resources/Products/
├── ProductResource.php
├── Pages/
│   ├── ListProducts.php
│   ├── CreateProduct.php
│   └── EditProduct.php
```

### Step 3: Update Resource with Standards

Edit `app/Filament/Resources/Products/ProductResource.php`:

**Import correct namespaces:**
```php
<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;          // ← Layout
use Filament\Schemas\Components\Grid;          // ← Layout
use Filament\Schemas\Components\Section;       // ← Layout
use Filament\Forms\Components\TextInput;       // ← Form field
use Filament\Forms\Components\Select;          // ← Form field
use Filament\Forms\Components\Toggle;          // ← Form field
use Filament\Forms\Components\Textarea;        // ← Form field
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;                   // ← NOT Form!
```

**Vietnamese labels:**
```php
protected static ?string $navigationLabel = 'Sản phẩm';
protected static ?string $modelLabel = 'Sản phẩm';
protected static ?string $pluralModelLabel = 'Các sản phẩm';
protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
protected static ?int $navigationSort = 10;
```

**Navigation badge:**
```php
public static function getNavigationBadge(): ?string
{
    return (string) static::getModel()::where('active', true)->count();
}
```

### Step 4: Implement Form Schema

```php
public static function form(Schema $schema): Schema  // ← Schema not Form
{
    return $schema->schema([
        Tabs::make()->tabs([
            Tabs\Tab::make('Thông tin chính')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Tên sản phẩm')
                            ->required()
                            ->maxLength(255),
                        
                        Select::make('category_id')
                            ->label('Danh mục')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                    
                    Textarea::make('description')
                        ->label('Mô tả')
                        ->rows(5)
                        ->columnSpanFull(),
                    
                    Toggle::make('active')
                        ->label('Đang hiển thị')
                        ->default(true),
                ]),
        ])
        ->columnSpanFull(),
    ]);
}
```

**Note:** SEO fields (slug, meta_title, meta_description) → HIDDEN, Observer auto-generates

### Step 5: Implement Table

```php
public static function table(Table $table): Table
{
    return $table
        // Eager loading
        ->modifyQueryUsing(fn($query) => $query->with(['category']))
        
        // Reorderable if has order column
        ->defaultSort('order', 'asc')
        ->reorderable('order')
        
        ->columns([
            ImageColumn::make('cover_image.file_path')
                ->label('Ảnh')
                ->disk('public')
                ->width(60)
                ->height(60),
            
            TextColumn::make('name')
                ->label('Tên')
                ->searchable()
                ->sortable()
                ->limit(40),
            
            TextColumn::make('category.name')
                ->label('Danh mục')
                ->badge()
                ->sortable(),
            
            ToggleColumn::make('active')
                ->label('Hiển thị'),
            
            TextColumn::make('created_at')
                ->label('Tạo lúc')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        
        ->recordActions([
            EditAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
        ])
        
        ->bulkActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ])
        
        ->paginated([10, 25, 50, 100])
        ->defaultPaginationPageOption(25);
}
```

### Step 6: Add RelationManagers (if has images)

```php
public static function getRelations(): array
{
    return [
        ImagesRelationManager::class,
    ];
}
```

**Generate ImagesRelationManager:**
```bash
php artisan make:filament-relation-manager ProductResource images file_path
```

### Step 7: Create Model Observer

Create `app/Observers/ProductObserver.php`:

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
        if ($product->isDirty('name')) {
            $product->slug = $this->generateUniqueSlug($product->name, $product->id);
            
            if (empty($product->meta_title)) {
                $product->meta_title = $product->name;
            }
        }
    }
    
    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $count = 1;
        
        while (Product::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = Str::slug($name) . '-' . $count++;
        }
        
        return $slug;
    }
}
```

**Register Observer** in `app/Providers/AppServiceProvider.php`:
```php
use App\Models\Product;
use App\Observers\ProductObserver;

public function boot(): void
{
    Product::observe(ProductObserver::class);
}
```

### Step 8: Add to Navigation

Resource auto-added to Filament navigation. Adjust order if needed:

```php
protected static ?int $navigationSort = 10;
protected static ?string $navigationGroup = 'Quản lý sản phẩm';
```

### Step 9: Test Resource

1. Visit `/admin/products`
2. Test create/edit/delete
3. Test search/sort/filter
4. Test image upload (if ImagesRelationManager)
5. Verify Observer auto-generates SEO fields
6. Test reorderable (if has order column)

## Generated Files Checklist

- [ ] `ProductResource.php` with correct namespaces
- [ ] Vietnamese labels throughout
- [ ] Form with Tabs/Grid/Section (Schemas\Components)
- [ ] Form fields (Forms\Components)
- [ ] Table with eager loading
- [ ] Reorderable if order column
- [ ] Actions: Edit + Delete
- [ ] BulkActions: DeleteBulkAction
- [ ] ImagesRelationManager (if has images)
- [ ] ProductObserver for SEO fields
- [ ] Observer registered in AppServiceProvider
- [ ] Navigation badge showing count

## Common Patterns

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
Select::make('categories')
    ->label('Danh mục')
    ->multiple()
    ->relationship('categories', 'name')
    ->searchable()
    ->preload(),

// Table
TextColumn::make('categories.name')
    ->label('Danh mục')
    ->badge()
    ->separator(','),
```

### With Order Column (Reorderable)
```php
// Migration
$table->unsignedInteger('order')->default(0);
$table->index('order');

// Table
->defaultSort('order', 'asc')
->reorderable('order')

// Observer
public function creating(Model $model): void
{
    if ($model->order === null) {
        $model->order = (Model::max('order') ?? 0) + 1;
    }
}
```

## Key Principles

1. **Namespace correctness**: Schemas for layouts, Forms for fields
2. **Vietnamese first**: All labels tiếng Việt
3. **Observer patterns**: SEO fields auto-generated, hidden from form
4. **Eager loading**: Always modifyQueryUsing() for relations
5. **Standard actions**: Edit + Delete + BulkDelete
6. **Navigation badge**: Show active count
7. **Image management**: Use ImagesRelationManager for galleries

## Critical Success Factors

- ✅ Correct imports (Schemas vs Forms)
- ✅ Vietnamese labels (100%)
- ✅ Observer for SEO fields
- ✅ Eager loading for N+1 prevention
- ✅ Reorderable if order column
- ✅ ImagesRelationManager if has images

## Supplementary Resources

**Filament standards:**
```
read .claude/skills/filament-rules/SKILL.md
```

**Image integration:**
```
read .claude/skills/image-management/SKILL.md
```

**Troubleshooting:**
```
read .claude/skills/filament-form-debugger/SKILL.md
```

Follow this workflow → Clean, standardized Filament resources! 🏗️
