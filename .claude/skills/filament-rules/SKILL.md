---
name: filament-rules
description: Filament 4.x coding standards for Laravel 12 project with custom Schema namespace (NOT Form), Vietnamese UI, Observer patterns, Image management with CheckboxList. USE WHEN creating Filament resources, fixing namespace errors (Class not found Tabs/Grid/Get), implementing forms, RelationManagers, Settings pages, or any Filament 4.x development task.
---

# Filament 4.x - Quick Reference Guide

## When to Activate This Skill

- Creating new Filament resource
- Fixing "Class not found" errors (Tabs, Grid, Get, etc.)
- Implementing forms with Schema
- Creating RelationManagers
- Setting up Settings pages
- Managing images with ImagesRelationManager
- Observer patterns (SEO, alt text, order)
- Any Filament 4.x development task

## 🚨 CRITICAL: Namespace Structure

**⚠️ Dự án này dùng `Schema` thay vì `Form`!**

### Layout Components → Schemas\Components
```php
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
```

### Form Fields → Forms\Components
```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
```

### Get Utility → Schemas\Components\Utilities
```php
use Filament\Schemas\Components\Utilities\Get;

// Usage in closures
->visible(fn (Get $get) => $get('type') === 'special')
->helperText(fn (Get $get) => "Selected: " . $get('name'))
```

### Actions → Filament\Actions
```php
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\CreateAction;
```

### Support Enums → Filament\Support\Enums
```php
use Filament\Support\Enums\GridDirection;
```

## Quick Checklist: New Resource

- [ ] **Vietnamese labels** (100% UI tiếng Việt)
- [ ] **Date format**: `->dateTime('d/m/Y H:i')`
- [ ] **All columns sortable**: `->sortable()`
- [ ] **Reorderable** nếu có `order` column: `->reorderable('order')`
- [ ] **Actions**: EditAction + DeleteAction (iconButton)
- [ ] **Bulk actions**: DeleteBulkAction
- [ ] **Eager loading**: `->modifyQueryUsing(fn($q) => $q->with(...))`
- [ ] **Observer** cho SEO fields (slug, meta_title, meta_description) - ẨN khỏi form
- [ ] **ImageObserver** cho auto alt/order/delete

## Common Patterns

### Resource Form Structure
```php
public function form(Schema $schema): Schema
{
    return $schema->schema([
        Tabs::make()->tabs([
            Tabs\Tab::make('Thông tin chính')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Tên')
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
                    ->columnSpanFull(),
            ]),
        ]),
    ]);
}
```

### Table with Reorderable
```php
public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn($q) => $q->with('relation'))
        ->defaultSort('order', 'asc')
        ->reorderable('order')  // ← Drag-drop ordering
        ->columns([
            TextColumn::make('name')
                ->label('Tên')
                ->searchable()
                ->sortable(),
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

### RelationManager Standard
```php
class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $title = 'Hình ảnh';
    
    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($q) => $q->with('relation'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                ImageColumn::make('file_path')
                    ->disk('public')
                    ->width(80)
                    ->height(80),
            ])
            ->headerActions([
                CreateAction::make()->label('Tạo'),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }
}
```

### Settings Page (Custom Page)
```php
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;  // ← Schema, NOT Form

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;  // ← Only this trait

    public ?array $data = [];

    public function form(Schema $schema): Schema  // ← Schema type
    {
        return $schema
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('site_name')
                        ->label('Tên website'),
                ]),
            ])
            ->statePath('data');  // ← Bind to $data
    }

    public function save(): void
    {
        $setting = Setting::first();
        $setting->update($this->form->getState());
    }
}
```

## Observer Auto-Generation

### SEO Fields (HIDDEN từ form)
```php
// ProductObserver.php
public function creating(Product $product): void
{
    if (empty($product->slug)) {
        $product->slug = $this->generateUniqueSlug($product->name);
    }
    
    if (empty($product->meta_title)) {
        $product->meta_title = $product->name;
    }
    
    if (empty($product->meta_description)) {
        $product->meta_description = Str::limit($product->description, 155);
    }
}
```

### Image Observer (Auto alt/order/delete)
```php
// ImageObserver.php
public function creating(Image $image): void
{
    // Auto order
    if ($image->order === null) {
        $image->order = $this->findNextAvailableOrder($image);
    }
    
    // Auto alt text
    if (empty($image->alt)) {
        $owner = $image->model;
        $image->alt = $image->order === 0 
            ? $owner->name 
            : "{$owner->name} hình {$image->order}";
    }
}

public function deleted(Image $image): void
{
    // Auto delete file
    Storage::disk('public')->delete($image->file_path);
}
```

## ❌ NEVER Use Alpine.js

**CRITICAL**: Filament đã có Alpine.js tích hợp, ĐỪNG viết custom Alpine code!

❌ **WRONG**:
```php
ViewField::make('images')
    ->view('filament.forms.custom-picker')  // có x-data, x-model
```

✅ **CORRECT**:
```php
CheckboxList::make('images')
    ->options($options)
    ->searchable()
    ->bulkToggleable()
    ->allowHtml()  // cho preview ảnh
```

## UI/UX Standards

### Vietnamese First
- ✅ Tất cả labels tiếng Việt
- ✅ Date format: `d/m/Y H:i` (31/12/2024 14:30)
- ✅ Navigation badge: Hiển thị số lượng records

### Navigation Badge
```php
public static function getNavigationBadge(): ?string
{
    return (string) static::getModel()::where('active', true)->count();
}
```

## File Upload with WebP Conversion

```php
FileUpload::make('file_path')
    ->disk('public')
    ->directory('products')
    ->imageEditor()
    ->saveUploadedFileUsing(function ($file) {
        $filename = uniqid('product_') . '.webp';
        $path = 'products/' . $filename;
        
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());
        
        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }
        
        $webp = $image->toWebp(quality: 85);
        Storage::disk('public')->put($path, $webp);
        
        return $path;
    })
```

## Common Mistakes & Solutions

### Mistake 1: Wrong Namespace for Tabs
❌ `use Filament\Forms\Components\Tabs;`
✅ `use Filament\Schemas\Components\Tabs;`

### Mistake 2: Wrong Namespace for Get
❌ `use Filament\Forms\Get;`
✅ `use Filament\Schemas\Components\Utilities\Get;`

### Mistake 3: Using HasFormActions in Page
❌ `use Filament\Pages\Concerns\HasFormActions;` (không tồn tại)
✅ Only `use InteractsWithForms;` + button trong view blade

### Mistake 4: Wrong Form Type in Settings Page
❌ `public function form(Form $form): Form`
✅ `public function form(Schema $schema): Schema`

### Mistake 5: Showing SEO Fields in Form
❌ Có `TextInput::make('slug')` trong form
✅ ẨN hoàn toàn, Observer tự động generate

## Key Principles

1. **Schema NOT Form**: Dự án dùng Schemas\Components cho layouts
2. **Vietnamese First**: 100% UI tiếng Việt
3. **Observer Pattern**: SEO fields + Image management tự động
4. **Eager Loading**: Luôn dùng modifyQueryUsing() cho relations
5. **Reorderable**: Nếu có order column → drag-drop
6. **No Alpine.js**: Dùng built-in Filament components
7. **WebP Conversion**: Tất cả uploads → WebP 85%

## Supplementary Resources

**Full comprehensive guide:**
```
read .claude/skills/filament-rules/CLAUDE.md
```

**Related skills:**
- **image-management**: `read .claude/skills/image-management/SKILL.md`
- **filament-resource-generator**: Tự động tạo resource
- **filament-form-debugger**: Fix lỗi Filament

## Quick Command Reference

```bash
# Create resource
php artisan make:filament-resource ResourceName

# Create relation manager
php artisan make:filament-relation-manager ResourceName relation

# Storage link
php artisan storage:link
```

## Critical Success Factors

1. ✅ **Namespaces đúng** → No "Class not found"
2. ✅ **Vietnamese labels** → UI professional
3. ✅ **Observer patterns** → Auto SEO/alt/order
4. ✅ **Eager loading** → No N+1 queries
5. ✅ **Native components** → No Alpine.js conflicts

Follow these rules → Clean, maintainable Filament code! 🚀
