<?php

namespace App\Filament\Resources\Products;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $recordTitleAttribute = 'name';

    protected static UnitEnum|string|null $navigationGroup = 'Sản phẩm';

    protected static ?string $navigationLabel = 'Sản phẩm';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Sản phẩm';

    protected static ?string $pluralModelLabel = 'Các sản phẩm';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Thông tin cơ bản
                Grid::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên sản phẩm')
                            ->placeholder('Rượu vang đỏ Bordeaux 2019')
                            ->hint('Tên hiển thị trên website')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Đường dẫn (URL)')
                            ->placeholder('ruou-vang-do-bordeaux-2019')
                            ->helperText('Chỉ dùng chữ thường, số, dấu gạch ngang')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->columnSpanFull(),

                        Select::make('product_category_id')
                            ->label('Danh mục sản phẩm')
                            ->placeholder('Chọn danh mục')
                            ->hint('Vang, Bia, Thịt nguội...')
                            ->options(ProductCategory::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('type_id')
                            ->label('Loại sản phẩm')
                            ->placeholder('Chọn loại chi tiết')
                            ->hint('Vang đỏ, Bia chai, Sparkling...')
                            ->options(ProductType::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Textarea::make('description')
                            ->label('Mô tả')
                            ->placeholder('Thông tin chi tiết về sản phẩm, xuất xứ, hương vị...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Giá cả & Khuyến mãi
                Grid::make()
                    ->schema([
                        TextInput::make('price')
                            ->label('Giá bán')
                            ->placeholder('350000')
                            ->hint('Giá hiện tại')
                            ->helperText('Để 0 nếu hiển thị "Liên hệ"')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('₫'),

                        TextInput::make('original_price')
                            ->label('Giá gốc')
                            ->placeholder('450000')
                            ->hint('Giá trước giảm')
                            ->helperText('Hiển thị % giảm tự động')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('₫'),

                        TagsInput::make('badges')
                            ->label('Nhãn hiển thị')
                            ->placeholder('Thêm nhãn...')
                            ->hint('HOT, NEW, SALE...')
                            ->helperText('Nhấn Enter để thêm')
                            ->suggestions(['HOT', 'NEW', 'SALE', 'LIMITED', 'BEST_SELLER'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Thông số kỹ thuật
                Grid::make()
                    ->schema([
                        TextInput::make('alcohol_percent')
                            ->label('Nồng độ cồn')
                            ->placeholder('13.5')
                            ->hint('% độ cồn')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.1)
                            ->suffix('%'),

                        TextInput::make('volume_ml')
                            ->label('Dung tích')
                            ->placeholder('750')
                            ->hint('Thể tích chai')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('ml'),

                        Toggle::make('active')
                            ->label('Hiển thị trên website')
                            ->hint('Bật/Tắt hiển thị')
                            ->default(true),
                    ])
                    ->columns(3),

                // SEO
                Grid::make()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Tiêu đề SEO')
                            ->placeholder('Rượu vang đỏ Bordeaux cao cấp - Wincellar')
                            ->hint('Tối đa 60 ký tự')
                            ->helperText('Để trống tự động dùng tên sản phẩm')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('meta_description')
                            ->label('Mô tả SEO')
                            ->placeholder('Khám phá rượu vang đỏ Bordeaux với hương vị tinh tế...')
                            ->hint('Tối đa 160 ký tự')
                            ->helperText('Hiển thị trên kết quả tìm kiếm Google')
                            ->rows(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                // Gallery will be handled by HasMediaGallery trait
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
        Tables\Columns\TextColumn::make('name')
        ->label('Tên')
        ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('productCategory.name')
            ->label('Danh mục')
            ->badge()
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('type.name')
            ->label('Loại sản phẩm')
            ->badge()
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('terms_count')
            ->label('Thuộc tính')
            ->counts('terms')
            ->badge()
            ->color('info')
            ->formatStateUsing(fn ($state) => $state . ' thuộc tính')
            ->sortable()
            ->tooltip('Số lượng thuộc tính (Brand, Origin, Grape...)'),
        Tables\Columns\TextColumn::make('price')
            ->label('Giá')
        ->money('VND')
            ->sortable(),
        Tables\Columns\TextColumn::make('original_price')
            ->label('Giá gốc')
        ->money('VND')
            ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('alcohol_percent')
                    ->label('Nồng độ cồn')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('volume_ml')
                    ->label('Dung tích')
                    ->suffix(' ml')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\IconColumn::make('active')
        ->label('Hoạt động')
        ->boolean(),
            Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ngày cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [
            ProductResource\RelationManagers\ProductTermAssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
