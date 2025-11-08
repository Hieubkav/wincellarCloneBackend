<?php

namespace App\Filament\Resources\MenuBlocks\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MenuBlockItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Các mục menu';

    protected static ?string $recordTitleAttribute = 'label';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin mục menu')
                    ->description('Chọn 1 trong 2 mode: (1) Chọn thuật ngữ → auto label/href, hoặc (2) Nhập thủ công label + đường dẫn')
                    ->columns(2)
                    ->schema([
                        Select::make('term_id')
                            ->label('Thuật ngữ (Mode 1: Auto)')
                            ->helperText('Chọn thuật ngữ từ catalog → tự động lấy tên và tạo link filter')
                            ->relationship('term', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        TextInput::make('label')
                            ->label('Nhãn hiển thị (Mode 2: Thủ công)')
                            ->helperText('Để trống nếu dùng thuật ngữ ở trên')
                            ->maxLength(255),

                        TextInput::make('href')
                            ->label('Đường dẫn (Mode 2: Thủ công)')
                            ->helperText('VD: tel:+84938123456, mailto:abc@xyz.com, https://...')
                            ->maxLength(2048),

                        TextInput::make('badge')
                            ->label('Nhãn đặc biệt')
                            ->maxLength(50)
                            ->placeholder('VD: Mới, Hot, Sale')
                            ->helperText('Hiển thị badge bên cạnh item'),

                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->required()
                            ->default(true)
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Icon hình ảnh')
                    ->description('Upload icon cho menu item (tùy chọn)')
                    ->collapsed()
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
                            ->helperText('Icon sẽ được tự động chuyển sang WebP 85% quality, max 400px width')
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['term']))
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
                    ->circular(),

                TextColumn::make('label')
                    ->label('Nhãn hiển thị')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-tag')
                    ->color('primary')
                    ->placeholder('(Từ thuật ngữ)')
                    ->description(fn ($record) => $record->term ? "Term: {$record->term->name}" : null),

                TextColumn::make('term.name')
                    ->label('Thuật ngữ')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),

                TextColumn::make('href')
                    ->label('Đường dẫn')
                    ->limit(40)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('(Auto từ term)')
                    ->copyable()
                    ->copyMessage('Đã copy!')
                    ->copyMessageDuration(1500),

                TextColumn::make('badge')
                    ->label('Badge')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tạo mục menu')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->modalHeading('Tạo mục menu mới')
                    ->modalWidth('3xl'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->color('warning')
                    ->modalWidth('3xl'),
                DeleteAction::make()
                    ->iconButton()
                    ->color('danger'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Chưa có mục menu nào')
            ->emptyStateDescription('Tạo mục menu đầu tiên cho khối này')
            ->emptyStateIcon('heroicon-o-list-bullet');
    }
}
