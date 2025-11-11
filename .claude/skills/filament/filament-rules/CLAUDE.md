# Filament 4.x Standards - Comprehensive Guide

Complete Filament 4.x coding standards for Laravel 12 project with custom Schema namespace, Vietnamese UI, and Observer patterns.

**All component references and patterns are based on actual Filament 4.x source code analysis.**

---

## Table of Contents

1. [Complete Components Reference](#complete-components-reference)
2. [Namespace Architecture](#namespace-architecture)
3. [Advanced Form Patterns](#advanced-form-patterns)
4. [Advanced Table Patterns](#advanced-table-patterns)
5. [Performance Optimization](#performance-optimization)
6. [Concerns & Traits Reference](#concerns--traits-reference)

---

## Complete Components Reference

### Schema Components (Layout - from packages/schemas/src/Components/)

```php
use Filament\Schemas\Components\{
    Actions,           // Action groups in schema
    EmbeddedSchema,    // Nested schema
    EmbeddedTable,     // Embedded table
    EmptyState,        // Empty state placeholder
    Fieldset,          // Fieldset with legend
    Flex,              // Flex container
    Form,              // Embedded form
    FusedGroup,        // Fused group of fields
    Grid,              // Grid layout
    Group,             // Simple group
    Html,              // Raw HTML
    Icon,              // Icon display
    Image,             // Image display
    Livewire,          // Livewire component
    RenderHook,        // Render hook
    Section,           // Section with heading
    Tabs,              // Tabs container
    Text,              // Text display
    UnorderedList,     // Unordered list
    View,              // Custom view
    Wizard,            // Multi-step wizard
};

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard\Step;
```

### Form Components (Fields - from packages/forms/src/Components/)

```php
use Filament\Forms\Components\{
    // Text Inputs
    TextInput,         // Basic text input
    Textarea,          // Multiline text
    MarkdownEditor,    // Markdown editor
    RichEditor,        // WYSIWYG editor
    CodeEditor,        // Code editor with syntax highlighting
    
    // Selection
    Select,            // Single/multiple select
    MultiSelect,       // Multiple select (shorthand)
    CheckboxList,      // List of checkboxes
    Radio,             // Radio buttons
    TagsInput,         // Tags input
    ToggleButtons,     // Toggle button group
    
    // Boolean
    Checkbox,          // Single checkbox
    Toggle,            // Toggle switch
    
    // Date & Time
    DatePicker,        // Date picker
    DateTimePicker,    // Date and time picker
    TimePicker,        // Time picker
    
    // File Upload
    FileUpload,        // File upload (single/multiple)
    
    // Advanced
    Builder,           // Dynamic blocks builder
    Repeater,          // Repeatable fields
    RelationshipRepeater, // Repeater for relationships
    KeyValue,          // Key-value pairs
    ColorPicker,       // Color picker
    Slider,            // Range slider
    OneTimeCodeInput,  // OTP input
    
    // Special
    Hidden,            // Hidden field
    Placeholder,       // Read-only placeholder
    MorphToSelect,     // Polymorphic relationship
    TableSelect,       // Select from table
    ModalTableSelect,  // Select with modal
    LivewireField,     // Custom Livewire field
    ViewField,         // Custom view field
};
```

### Table Columns (from packages/tables/src/Columns/)

```php
use Filament\Tables\Columns\{
    TextColumn,        // Text display
    ImageColumn,       // Image display
    IconColumn,        // Icon display
    ColorColumn,       // Color swatch
    BadgeColumn,       // Badge display (deprecated - use TextColumn::badge())
    BooleanColumn,     // Boolean icons (deprecated - use IconColumn)
    TagsColumn,        // Tags display
    
    // Editable
    CheckboxColumn,    // Inline checkbox
    ToggleColumn,      // Inline toggle
    SelectColumn,      // Inline select
    TextInputColumn,   // Inline text input
    
    // Layout
    Layout\Grid,       // Grid layout
    Layout\Panel,      // Panel layout
    Layout\Split,      // Split layout
    Layout\Stack,      // Stack layout
    Layout\View,       // Custom view
    
    // Special
    ColumnGroup,       // Group columns
    ViewColumn,        // Custom view column
};
```

### Table Filters (from packages/tables/src/Filters/)

```php
use Filament\Tables\Filters\{
    Filter,            // Custom filter
    SelectFilter,      // Select dropdown filter
    MultiSelectFilter, // Multiple select filter
    TernaryFilter,     // Yes/No/All filter
    TrashedFilter,     // Soft deletes filter
    QueryBuilder,      // Advanced query builder
};
```

### Actions (from packages/actions/src/)

```php
use Filament\Actions\{
    // Single Record
    Action,            // Generic action
    CreateAction,      // Create new record
    EditAction,        // Edit record
    ViewAction,        // View record
    DeleteAction,      // Delete record
    ReplicateAction,   // Replicate record
    RestoreAction,     // Restore soft-deleted
    ForceDeleteAction, // Force delete
    
    // Relationships
    AttachAction,      // Attach to relationship
    DetachAction,      // Detach from relationship
    AssociateAction,   // Associate with parent
    DissociateAction,  // Dissociate from parent
    
    // Bulk Actions
    BulkAction,        // Custom bulk action
    DeleteBulkAction,  // Bulk delete
    RestoreBulkAction, // Bulk restore
    ForceDeleteBulkAction, // Bulk force delete
    DetachBulkAction,  // Bulk detach
    DissociateBulkAction, // Bulk dissociate
    ExportBulkAction,  // Bulk export
    
    // Import/Export
    ImportAction,      // Import data
    ExportAction,      // Export data
    
    // UI
    ActionGroup,       // Group actions
    ButtonAction,      // Button action
    IconButtonAction,  // Icon button
    SelectAction,      // Select action
};
```

---

## Namespace Architecture

### Critical Rule: Schemas vs Forms

```php
// ❌ WRONG - Common mistakes
use Filament\Forms\Components\Tabs;  // Layout in Forms
use Filament\Schemas\Components\TextInput;  // Field in Schemas

// ✅ CORRECT - Proper namespaces
use Filament\Schemas\Components\Tabs;       // Layout → Schemas
use Filament\Forms\Components\TextInput;    // Field → Forms
```

### Complete Namespace Map

| Component Type | Namespace | Examples |
|---------------|-----------|----------|
| **Layout Components** | `Filament\Schemas\Components\` | Tabs, Grid, Section, Fieldset, Group, Flex, Wizard |
| **Form Fields** | `Filament\Forms\Components\` | TextInput, Select, Toggle, DatePicker, FileUpload |
| **Display Components** | `Filament\Schemas\Components\` | Text, Icon, Image, Html, EmptyState |
| **Schema Utilities** | `Filament\Schemas\Components\Utilities\` | Get (for closures) |
| **Schema Definition** | `Filament\Schemas\` | Schema (NOT Form!) |
| **Table Columns** | `Filament\Tables\Columns\` | TextColumn, ImageColumn, ToggleColumn |
| **Table Filters** | `Filament\Tables\Filters\` | SelectFilter, TernaryFilter, QueryBuilder |
| **Actions** | `Filament\Actions\` | EditAction, DeleteAction, BulkActionGroup |
| **Support Enums** | `Filament\Support\Enums\` | Alignment, GridDirection, IconPosition, Size |

---

## Advanced Form Patterns

### 1. Section with Advanced Features

```php
use Filament\Schemas\Components\Section;

Section::make('Thông tin sản phẩm')
    ->description('Nhập thông tin cơ bản của sản phẩm')
    ->icon('heroicon-o-shopping-bag')
    ->iconColor('success')
    ->collapsible()              // Can be collapsed
    ->collapsed(false)           // Initial state
    ->compact()                  // Compact styling
    ->aside()                    // Aside layout (label on side)
    ->headerActions([            // Actions in header
        Action::make('import')
            ->label('Import')
            ->icon('heroicon-o-arrow-up-tray'),
    ])
    ->footerActions([            // Actions in footer
        Action::make('preview')
            ->label('Xem trước'),
    ])
    ->schema([
        // Fields here...
    ]),
```

### 2. Select with Advanced Features

```php
use Filament\Forms\Components\Select;

Select::make('category_id')
    ->label('Danh mục')
    ->relationship('category', 'name')
    ->searchable()                    // Enable search
    ->preload()                       // Preload all options
    ->native(false)                   // Use custom UI (not native select)
    ->multiple()                      // Multiple selection
    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->code})")
    ->createOptionForm([              // Inline create
        TextInput::make('name')
            ->label('Tên danh mục')
            ->required(),
    ])
    ->createOptionUsing(function($data) {
        return Category::create($data)->id;
    })
    ->editOptionForm([                // Inline edit
        TextInput::make('name')
            ->label('Tên danh mục')
            ->required(),
    ])
    ->searchPrompt('Tìm kiếm danh mục...')
    ->noSearchResultsMessage('Không tìm thấy danh mục')
    ->allowHtml()                     // Allow HTML in labels
    ->optionsLimit(100)               // Limit options
    ->placeholder('Chọn danh mục'),
```

### 3. TextInput with All Features

```php
use Filament\Forms\Components\TextInput;

TextInput::make('price')
    ->label('Giá')
    ->numeric()                       // Numeric input
    ->prefix('₫')                     // Prefix
    ->suffix('VND')                   // Suffix
    ->minValue(0)                     // Min value
    ->maxValue(1000000000)            // Max value
    ->step(1000)                      // Step increment
    ->mask(RawJs::make('$money($input)'))  // Mask
    ->stripCharacters(',')            // Strip characters
    ->required()
    ->live(onBlur: true)              // Live update on blur
    ->afterStateUpdated(function ($state, $set) {
        // Calculate discount
        $set('final_price', $state * 0.9);
    })
    ->helperText('Nhập giá bằng VNĐ')
    ->hint('Giá gốc')
    ->hintIcon('heroicon-o-information-circle')
    ->placeholder('0')
    ->autocomplete('off')
    ->readOnly(fn($context) => $context === 'view')
    ->disabled(fn($context) => $context === 'view'),
```

### 4. Conditional Fields

```php
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;

Select::make('type')
    ->label('Loại sản phẩm')
    ->options([
        'simple' => 'Đơn giản',
        'variable' => 'Biến thể',
        'digital' => 'Số hóa',
    ])
    ->live()                          // Required for reactive
    ->required(),

// Show only when type is 'variable'
Grid::make(2)
    ->visible(fn (Get $get) => $get('type') === 'variable')
    ->schema([
        Select::make('size')
            ->label('Kích thước')
            ->options(['S', 'M', 'L', 'XL']),
        
        Select::make('color')
            ->label('Màu sắc')
            ->options(['Đỏ', 'Xanh', 'Vàng']),
    ]),

// Show only when type is 'digital'
FileUpload::make('download_file')
    ->label('File tải về')
    ->visible(fn (Get $get) => $get('type') === 'digital')
    ->required(),
```

### 5. Repeater with Relationship

```php
use Filament\Forms\Components\Repeater;

Repeater::make('variants')
    ->label('Biến thể')
    ->relationship('variants')        // Relationship repeater
    ->schema([
        TextInput::make('sku')
            ->label('Mã SKU')
            ->required(),
        
        TextInput::make('price')
            ->label('Giá')
            ->numeric()
            ->required(),
        
        TextInput::make('stock')
            ->label('Tồn kho')
            ->numeric()
            ->default(0),
    ])
    ->orderable()                     // Drag to reorder
    ->collapsible()                   // Collapse items
    ->collapsed()                     // Initially collapsed
    ->itemLabel(fn($state) => $state['sku'] ?? 'Biến thể mới')
    ->addActionLabel('Thêm biến thể')
    ->defaultItems(0)                 // Start with 0 items
    ->minItems(1)                     // Min items
    ->maxItems(10)                    // Max items
    ->reorderable()
    ->cloneable()                     // Allow cloning items
    ->grid(2)                         // Grid layout
    ->columns(2),
```

### 6. Builder (Dynamic Blocks)

```php
use Filament\Forms\Components\Builder;

Builder::make('content')
    ->label('Nội dung')
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
        
        Builder\Block::make('gallery')
            ->label('Thư viện ảnh')
            ->icon('heroicon-o-photo')
            ->schema([
                FileUpload::make('images')
                    ->label('Hình ảnh')
                    ->image()
                    ->multiple()
                    ->required(),
            ]),
    ])
    ->collapsible()
    ->cloneable()
    ->blockNumbers(false),            // Hide block numbers
```

### 7. Wizard (Multi-step)

```php
use Filament\Schemas\Components\Wizard;

Wizard::make([
    Wizard\Step::make('Thông tin cơ bản')
        ->icon('heroicon-o-information-circle')
        ->description('Nhập thông tin cơ bản')
        ->schema([
            TextInput::make('name')->required(),
            Select::make('category_id')->required(),
        ])
        ->columns(2),
    
    Wizard\Step::make('Mô tả')
        ->icon('heroicon-o-document-text')
        ->schema([
            RichEditor::make('description'),
        ]),
    
    Wizard\Step::make('Hình ảnh')
        ->icon('heroicon-o-photo')
        ->schema([
            FileUpload::make('images')
                ->image()
                ->multiple(),
        ]),
])
    ->submitAction(view('filament.submit-button'))
    ->skippable()                     // Can skip steps
    ->persistStepInQueryString()      // Persist in URL
    ->startOnStep(2),                 // Start on specific step
```

---

## Advanced Table Patterns

### 1. TextColumn with All Features

```php
use Filament\Tables\Columns\TextColumn;

TextColumn::make('name')
    ->label('Tên sản phẩm')
    ->searchable()                    // Enable search
    ->sortable()                      // Enable sort
    ->toggleable()                    // Can hide/show
    ->copyable()                      // Copy to clipboard
    ->copyMessage('Đã copy!')
    ->badge()                         // Display as badge
    ->color(fn($state) => match($state) {
        'active' => 'success',
        'draft' => 'warning',
        default => 'gray',
    })
    ->icon('heroicon-o-check-circle')
    ->iconPosition('after')
    ->size('sm')                      // xs, sm, md, lg
    ->weight('bold')                  // normal, medium, semibold, bold
    ->fontFamily('mono')              // sans, serif, mono
    ->wrap()                          // Allow text wrap
    ->lineClamp(2)                    // Limit lines
    ->limit(50)                       // Limit characters
    ->formatStateUsing(fn($state) => strtoupper($state))
    ->description(fn($record) => $record->category->name)
    ->descriptionIcon('heroicon-o-folder')
    ->url(fn($record) => route('product.show', $record))
    ->openUrlInNewTab()
    ->tooltip('Click để xem chi tiết'),
```

### 2. ImageColumn with Features

```php
use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('cover_image.file_path')
    ->label('Hình ảnh')
    ->disk('public')
    ->width(80)
    ->height(80)
    ->circular()                      // Circular image
    ->square()                        // Square aspect ratio
    ->stacked()                       // Stack multiple images
    ->limit(3)                        // Limit stacked images
    ->limitedRemainingText()          // Show "+X more"
    ->ring(2)                         // Ring around image
    ->overlap(4)                      // Overlap amount
    ->checkFileExistence(false)       // Don't check if file exists
    ->defaultImageUrl(asset('images/placeholder.png'))
    ->extraImgAttributes(['loading' => 'lazy']),
```

### 3. ToggleColumn (Inline Edit)

```php
use Filament\Tables\Columns\ToggleColumn;

ToggleColumn::make('active')
    ->label('Hiển thị')
    ->onIcon('heroicon-o-check-circle')
    ->offIcon('heroicon-o-x-circle')
    ->onColor('success')
    ->offColor('danger')
    ->beforeStateUpdated(function ($record, $state) {
        // Log activity
        activity()
            ->performedOn($record)
            ->log($state ? 'activated' : 'deactivated');
    })
    ->afterStateUpdated(function ($record, $state) {
        // Clear cache
        Cache::forget("product.{$record->id}");
    }),
```

### 4. SelectColumn (Inline Edit)

```php
use Filament\Tables\Columns\SelectColumn;

SelectColumn::make('status')
    ->label('Trạng thái')
    ->options([
        'draft' => 'Nháp',
        'published' => 'Đã xuất bản',
        'archived' => 'Lưu trữ',
    ])
    ->selectablePlaceholder(false)    // Can't select null
    ->rules(['required'])
    ->beforeStateUpdated(function ($record, $state) {
        // Validation
        if ($state === 'published' && !$record->content) {
            throw new \Exception('Không thể xuất bản khi chưa có nội dung');
        }
    }),
```

### 5. Advanced Filters

```php
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;

// Select Filter with Relationship
SelectFilter::make('category_id')
    ->label('Danh mục')
    ->relationship('category', 'name')
    ->searchable()
    ->preload()
    ->multiple()
    ->indicator('Danh mục'),

// Ternary Filter (Yes/No/All)
TernaryFilter::make('active')
    ->label('Trạng thái')
    ->placeholder('Tất cả')
    ->trueLabel('Đang hiển thị')
    ->falseLabel('Đang ẩn')
    ->queries(
        true: fn($query) => $query->where('active', true),
        false: fn($query) => $query->where('active', false),
        blank: fn($query) => $query,
    )
    ->indicator('Hiển thị'),

// Custom Filter
Filter::make('created_at')
    ->form([
        DatePicker::make('created_from')
            ->label('Từ ngày'),
        DatePicker::make('created_until')
            ->label('Đến ngày'),
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
            $indicators[] = 'Từ ' . Carbon::parse($data['created_from'])->format('d/m/Y');
        }
        
        if ($data['created_until'] ?? null) {
            $indicators[] = 'Đến ' . Carbon::parse($data['created_until'])->format('d/m/Y');
        }
        
        return $indicators;
    }),
```

### 6. Table with All Features

```php
public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn($query) => $query->with(['category', 'coverImage']))
        ->defaultSort('order', 'asc')
        ->reorderable('order')
        ->poll('30s')                 // Auto refresh every 30s
        ->deferLoading()              // Defer initial load
        ->striped()                   // Striped rows
        ->columns([
            // Columns...
        ])
        ->filters([
            // Filters...
        ])
        ->filtersFormColumns(3)       // Filter form columns
        ->persistFiltersInSession()   // Persist filters
        ->filtersTriggerAction(
            fn($action) => $action
                ->button()
                ->label('Lọc')
        )
        ->actions([
            // Single record actions...
        ])
        ->bulkActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
                BulkAction::make('activate')
                    ->label('Kích hoạt')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(fn($records) => 
                        $records->each->update(['active' => true])
                    ),
            ]),
        ])
        ->emptyStateHeading('Chưa có sản phẩm')
        ->emptyStateDescription('Tạo sản phẩm đầu tiên của bạn')
        ->emptyStateIcon('heroicon-o-shopping-bag')
        ->emptyStateActions([
            Action::make('create')
                ->label('Tạo sản phẩm')
                ->url(route('filament.admin.resources.products.create'))
                ->button(),
        ])
        ->recordUrl(fn($record) => route('filament.admin.resources.products.edit', $record))
        ->recordAction('edit')        // Default click action
        ->selectCurrentPageOnly()     // Select only current page
        ->deselectAllRecordsWhenFiltered(false);
}
```

---

## Performance Optimization

### 1. Eager Loading

```php
// ✅ Good - Single query with relationships
->modifyQueryUsing(fn($query) => $query->with([
    'category',
    'tags',
    'coverImage',
    'variants' => fn($q) => $q->where('active', true),
]))

// ❌ Bad - N+1 queries
TextColumn::make('category.name')  // Without eager loading
```

### 2. Caching Navigation Badge

```php
use Illuminate\Support\Facades\Cache;

public static function getNavigationBadge(): ?string
{
    return Cache::remember(
        'products.active.count',
        now()->addMinutes(10),
        fn() => (string) static::getModel()::where('active', true)->count()
    );
}

// Clear cache in Observer
class ProductObserver
{
    public function saved($product): void
    {
        Cache::forget('products.active.count');
    }
}
```

### 3. Deferred Loading

```php
public static function table(Table $table): Table
{
    return $table
        ->deferLoading()              // Defer initial data load
        ->paginated([10, 25, 50, 100])
        ->defaultPaginationPageOption(25);
}
```

### 4. Limit Preloaded Options

```php
Select::make('category_id')
    ->relationship('category', 'name')
    ->searchable()
    ->preload()
    ->optionsLimit(50),               // Limit preloaded options
```

---

## Concerns & Traits Reference

### Section Concerns (from source)

```php
use Filament\Schemas\Components\Concerns\{
    CanBeCollapsed,           // ->collapsible(), ->collapsed()
    CanBeCompact,             // ->compact()
    CanBeDivided,             // ->divideAfter(), ->divideBefore()
    CanBeSecondary,           // ->secondary()
    HasDescription,           // ->description()
    HasFooterActions,         // ->footerActions()
    HasHeaderActions,         // ->headerActions()
    HasHeading,               // ->heading()
    HasLabel,                 // ->label()
};
```

### Field Concerns (from source)

```php
use Filament\Forms\Components\Concerns\{
    CanAllowHtml,             // ->allowHtml()
    CanBeAutocapitalized,     // ->autocapitalize()
    CanBeAutocompleted,       // ->autocomplete()
    CanBeLengthConstrained,   // ->minLength(), ->maxLength()
    CanBeNative,              // ->native()
    CanBePreloaded,           // ->preload()
    CanBeReadOnly,            // ->readOnly()
    CanBeSearchable,          // ->searchable()
    CanDisableOptions,        // ->disableOptionWhen()
    CanFixIndistinctState,    // Fix indistinct state
    CanLimitItemsLength,      // ->maxItems()
    CanSelectPlaceholder,     // ->selectablePlaceholder()
    HasAffixes,               // ->prefix(), ->suffix()
    HasDatalistOptions,       // ->datalist()
    HasExtraInputAttributes,  // ->extraInputAttributes()
    HasInputMode,             // ->inputMode()
    HasLoadingMessage,        // ->loadingMessage()
    HasOptions,               // ->options()
    HasPivotData,             // Pivot data for BelongsToMany
    HasPlaceholder,           // ->placeholder()
    HasStep,                  // ->step()
};
```

### Column Concerns (from source)

```php
use Filament\Tables\Columns\Concerns\{
    CanBeSearchable,          // ->searchable()
    CanBeSortable,            // ->sortable()
    CanBeToggled,             // ->toggleable()
    CanFormatState,           // ->formatStateUsing()
    HasColor,                 // ->color()
    HasDescription,           // ->description()
    HasIcon,                  // ->icon()
    HasIconColor,             // ->iconColor()
};
```

---

## Complete Resource Example

```php
<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\ProductResource\Pages;
use App\Filament\Resources\Products\ProductResource\RelationManagers\ImagesRelationManager;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\{Select, TextInput, Toggle, RichEditor, FileUpload};
use Filament\Resources\Resource;
use Filament\Schemas\Components\{Grid, Section, Tabs};
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\{BulkActionGroup, DeleteBulkAction};
use Filament\Tables\Columns\{ImageColumn, TextColumn, ToggleColumn};
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationLabel = 'Sản phẩm';
    protected static ?string $modelLabel = 'sản phẩm';
    protected static ?string $pluralModelLabel = 'Các sản phẩm';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Cửa hàng';
    protected static ?int $navigationSort = 1;
    
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('active', true)->count();
    }
    
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make()->tabs([
                Tabs\Tab::make('Thông tin chính')->schema([
                    Section::make()->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Tên sản phẩm')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, $set) => 
                                    $set('slug', \Str::slug($state))
                                ),
                            
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
                            
                            Select::make('type')
                                ->label('Loại sản phẩm')
                                ->options([
                                    'simple' => 'Đơn giản',
                                    'variable' => 'Biến thể',
                                ])
                                ->default('simple')
                                ->live()
                                ->required(),
                            
                            TextInput::make('price')
                                ->label('Giá')
                                ->numeric()
                                ->prefix('₫')
                                ->required()
                                ->visible(fn(Get $get) => $get('type') === 'simple'),
                            
                            TextInput::make('stock')
                                ->label('Tồn kho')
                                ->numeric()
                                ->default(0)
                                ->visible(fn(Get $get) => $get('type') === 'simple'),
                            
                            Toggle::make('active')
                                ->label('Đang hiển thị')
                                ->default(true),
                        ]),
                    ]),
                ]),
                
                Tabs\Tab::make('Mô tả')->schema([
                    RichEditor::make('description')
                        ->label('Mô tả chi tiết')
                        ->columnSpanFull(),
                ]),
            ])->columnSpanFull(),
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['category', 'coverImage']))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                ImageColumn::make('coverImage.file_path')
                    ->label('Ảnh')
                    ->disk('public')
                    ->width(60)
                    ->height(60),
                
                TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->category?->name)
                    ->weight('medium'),
                
                TextColumn::make('price')
                    ->label('Giá')
                    ->money('VND')
                    ->sortable(),
                
                TextColumn::make('stock')
                    ->label('Tồn kho')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),
                
                ToggleColumn::make('active')
                    ->label('Hiển thị'),
                
                TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Danh mục')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                
                TernaryFilter::make('active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hiển thị')
                    ->falseLabel('Đang ẩn'),
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
            ->emptyStateHeading('Chưa có sản phẩm')
            ->emptyStateDescription('Tạo sản phẩm đầu tiên')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
    
    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
```

---

**For quick reference, see SKILL.md**
