<?php

namespace App\Filament\Resources\MenuBlocks\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MenuBlockItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Các items trong cột';

    protected static ?string $recordTitleAttribute = 'label';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('label')
                    ->label('Nhãn hiển thị')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('VD: Vang đỏ, Pháp, Cabernet'),
                TextInput::make('href')
                    ->label('Đường dẫn')
                    ->required()
                    ->maxLength(2048)
                    ->placeholder('VD: /filter?type=1&category=2')
                    ->helperText('Nhập link thủ công'),
                TextInput::make('badge')
                    ->label('Badge')
                    ->maxLength(50)
                    ->placeholder('VD: HOT, NEW, SALE'),
                Toggle::make('active')
                    ->label('Hiển thị')
                    ->required()
                    ->default(true)
                    ->inline(false),

                Section::make('Icon (tùy chọn)')
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('icon_image')
                            ->label('Ảnh icon')
                            ->image()
                            ->disk('public')
                            ->directory('menu-items')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->maxFiles(1)
                            ->acceptedFileTypes(['image/*'])
                            ->saveUploadedFileUsing(function ($file) {
                                $filename = uniqid('menu_item_') . '.webp';
                                $path = 'menu-items/' . $filename;

                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($file->getRealPath());

                                if ($image->width() > 400) {
                                    $image->scale(width: 400);
                                }

                                $webp = $image->toWebp(quality: 85);
                                Storage::disk('public')->put($path, $webp);

                                return $path;
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width(60)
                    ->alignCenter()
                    ->color('gray'),
                ImageColumn::make('icon_image')
                    ->label('Icon')
                    ->disk('public')
                    ->width(40)
                    ->height(40)
                    ->defaultImageUrl(fn () => null)
                    ->placeholder('—')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('label')
                    ->label('Nhãn')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-tag')
                    ->color('primary'),
                TextColumn::make('href')
                    ->label('Đường dẫn')
                    ->limit(35)
                    ->searchable()
                    ->tooltip(fn ($record) => $record->href)
                    ->color('gray'),
                TextColumn::make('badge')
                    ->label('Badge')
                    ->badge()
                    ->color(fn ($state) => match(strtoupper($state ?? '')) {
                        'HOT' => 'danger',
                        'NEW' => 'success',
                        'SALE' => 'warning',
                        default => 'gray'
                    })
                    ->placeholder('—'),
                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Thêm item')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->modalHeading('Thêm menu item'),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->color('warning'),
                DeleteAction::make()->iconButton()->color('danger'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Chưa có item nào')
            ->emptyStateDescription('Thêm các link menu cho cột này')
            ->emptyStateIcon('heroicon-o-list-bullet');
    }
}
