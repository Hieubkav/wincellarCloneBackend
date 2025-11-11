# Filament Resource Generator - Detailed Implementation

## Complete Form Implementation

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
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')->label('Tên danh mục')->required(),
                        ]),
                    
                    TextInput::make('price')
                        ->label('Giá')
                        ->numeric()
                        ->prefix('VNĐ'),
                    
                    TextInput::make('sku')
                        ->label('Mã SKU')
                        ->unique(ignoreRecord: true),
                ]),
                
                RichEditor::make('description')
                    ->label('Mô tả')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'link',
                        'bulletList',
                        'orderedList',
                    ]),
                
                Toggle::make('active')
                    ->label('Đang hiển thị')
                    ->default(true),
            ]),
            
            Tabs\Tab::make('SEO')->schema([
                TextInput::make('meta_title')->label('Meta Title'),
                Textarea::make('meta_description')->label('Meta Description')->rows(3),
                TextInput::make('slug')->label('Slug')->disabled(),
            ])->hidden(),  // Observer handles these
        ])->columnSpanFull(),
    ]);
}
```

## Complete Table Implementation

```php
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
            
            TextColumn::make('order')
                ->label('#')
                ->sortable()
                ->width(50),
            
            TextColumn::make('name')
                ->label('Tên sản phẩm')
                ->searchable()
                ->sortable()
                ->description(fn ($record) => $record->sku),
            
            TextColumn::make('category.name')
                ->label('Danh mục')
                ->sortable()
                ->searchable(),
            
            TextColumn::make('price')
                ->label('Giá')
                ->money('VND')
                ->sortable(),
            
            IconColumn::make('active')
                ->label('Hiển thị')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle'),
            
            TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            SelectFilter::make('category_id')
                ->label('Danh mục')
                ->relationship('category', 'name')
                ->multiple()
                ->preload(),
            
            TernaryFilter::make('active')
                ->label('Trạng thái')
                ->placeholder('Tất cả')
                ->trueLabel('Hiển thị')
                ->falseLabel('Ẩn'),
        ])
        ->recordActions([
            EditAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
        ])
        ->bulkActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
                BulkAction::make('activate')
                    ->label('Hiển thị')
                    ->icon('heroicon-o-check')
                    ->action(fn (Collection $records) => $records->each->update(['active' => true]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('deactivate')
                    ->label('Ẩn')
                    ->icon('heroicon-o-x-mark')
                    ->action(fn (Collection $records) => $records->each->update(['active' => false]))
                    ->deselectRecordsAfterCompletion(),
            ]),
        ]);
}
```

## Observer Pattern - Complete

```php
<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    public function creating(Product $product): void
    {
        // Auto-generate slug if empty
        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name);
        }
        
        // Auto-generate SEO fields if empty
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }
        
        if (empty($product->meta_description)) {
            $description = strip_tags($product->description ?? '');
            $product->meta_description = Str::limit($description, 160);
        }
        
        // Auto-set order
        if ($product->order === null) {
            $product->order = (Product::max('order') ?? 0) + 1;
        }
    }
    
    public function updating(Product $product): void
    {
        // Update slug if name changed
        if ($product->isDirty('name') && empty($product->getOriginal('slug'))) {
            $product->slug = Str::slug($product->name);
        }
        
        // Update meta_title if changed
        if ($product->isDirty('name') && empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }
    }
    
    public function deleted(Product $product): void
    {
        // Cleanup related data if needed
        $product->images()->delete();
    }
}
```

## Advanced Patterns

### With Pivot Data (Many-to-Many)

```php
// Form
Select::make('tags')
    ->relationship('tags', 'name')
    ->multiple()
    ->preload()
    ->createOptionForm([
        TextInput::make('name')->required(),
    ]),
```

### With Conditional Fields

```php
Select::make('type')
    ->label('Loại')
    ->options([
        'simple' => 'Sản phẩm đơn giản',
        'variable' => 'Sản phẩm biến thể',
    ])
    ->reactive(),

Grid::make(2)->schema([
    TextInput::make('price')->visible(fn (Get $get) => $get('type') === 'simple'),
    Repeater::make('variations')->visible(fn (Get $get) => $get('type') === 'variable'),
]),
```

### With Custom Actions

```php
->actions([
    EditAction::make(),
    Action::make('duplicate')
        ->label('Nhân bản')
        ->icon('heroicon-o-document-duplicate')
        ->action(function (Product $record) {
            $newProduct = $record->replicate();
            $newProduct->name = $record->name . ' (Copy)';
            $newProduct->slug = null;
            $newProduct->save();
        }),
])
```

## Migration Template

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2)->nullable();
    $table->string('sku')->unique()->nullable();
    
    // Foreign keys
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    
    // SEO
    $table->string('meta_title')->nullable();
    $table->text('meta_description')->nullable();
    
    // Status
    $table->boolean('active')->default(true);
    $table->unsignedInteger('order')->default(0)->index();
    
    $table->timestamps();
    $table->softDeletes();
});
```

## Model Template

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'sku',
        'category_id',
        'meta_title',
        'meta_description',
        'active',
        'order',
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];
    
    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'model')->orderBy('order');
    }
    
    public function coverImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'model')->where('order', 0);
    }
}
```
