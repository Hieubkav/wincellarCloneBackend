---
name: filament-rules
description: Filament 4.x coding standards for Laravel 12 project with custom Schema namespace (NOT Form), Vietnamese UI, Observer patterns, Image management. USE WHEN creating Filament resources, fixing namespace errors, implementing forms, RelationManagers, Settings pages, or any Filament 4.x development.
---

# Filament 4.x Standards - Quick Reference

Filament 4.x coding standards for this Laravel 12 project with custom namespaces.

## When to Use

- Creating Filament resources
- Fixing namespace errors  
- Implementing forms/tables
- Creating RelationManagers
- Setting up Settings pages
- Image management
- Observer patterns

---

## Critical Namespaces

**⚠️ This project uses `Schema` NOT `Form`!**

```php
// Layout → Schemas
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

// Fields → Forms
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

// Utilities
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

// Actions
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
```

---

## Resource Structure

### Form Method

```php
public static function form(Schema $schema): Schema
{
    return $schema->schema([
        Tabs::make()->tabs([
            Tabs\Tab::make('Thông tin chính')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Tên')
                        ->required(),
                    
                    Select::make('category_id')
                        ->label('Danh mục')
                        ->relationship('category', 'name')
                        ->searchable(),
                ]),
            ]),
        ])->columnSpanFull(),
    ]);
}
```

### Table Method

```php
public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn($q) => $q->with(['category']))
        ->columns([
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
        ]);
}
```

---

## Standards Checklist

- [ ] Vietnamese labels (100%)
- [ ] Date format: `d/m/Y H:i`
- [ ] Eager loading with `modifyQueryUsing()`
- [ ] Sortable columns
- [ ] Reorderable if `order` column
- [ ] Observer for SEO (slug, meta_*)
- [ ] ImageObserver for images
- [ ] Navigation badge
- [ ] Icon button actions

---

## Observer Pattern

### SEO Fields (Hidden from Form)

```php
class ProductObserver
{
    public function creating(Product $product): void
    {
        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name);
        }
        
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }
    }
}
```

**Register:**
```php
// AppServiceProvider::boot()
Product::observe(ProductObserver::class);
```

---

## Vietnamese UI

```php
protected static ?string $navigationLabel = 'Sản phẩm';
protected static ?string $modelLabel = 'Sản phẩm';
protected static ?string $pluralModelLabel = 'Các sản phẩm';

// Form labels
TextInput::make('name')->label('Tên'),
Select::make('category_id')->label('Danh mục'),
Toggle::make('active')->label('Đang hiển thị'),

// Table columns
TextColumn::make('name')->label('Tên'),
TextColumn::make('category.name')->label('Danh mục'),

// Date format
->dateTime('d/m/Y H:i')
```

---

## Images Integration

```php
// Model relationships
public function images(): MorphMany
{
    return $this->morphMany(Image::class, 'model');
}

public function coverImage(): MorphOne
{
    return $this->morphOne(Image::class, 'model')
        ->where('order', 0);
}

// Resource
public static function getRelations(): array
{
    return [ImagesRelationManager::class];
}
```

---

## Settings Pages

```php
class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;  // Only this trait!
    
    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Cài đặt')->schema([
                TextInput::make('site_name')
                    ->label('Tên website'),
            ]),
        ]);
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        // Save logic
    }
}
```

**View:**
```blade
<form wire:submit="save">
    {{ $this->form }}
    <x-filament::button type="submit">Lưu</x-filament::button>
</form>
```

---

## Complete Guide

For comprehensive examples, advanced patterns, and detailed implementation:

`read .claude/skills/filament-rules/CLAUDE.md`

**Related:**
- Resource generator: `read .claude/skills/filament-resource-generator/SKILL.md`
- Form debugger: `read .claude/skills/filament-form-debugger/SKILL.md`
- Image management: `read .claude/skills/image-management/SKILL.md`
