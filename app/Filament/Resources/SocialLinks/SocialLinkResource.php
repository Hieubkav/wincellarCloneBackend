<?php

namespace App\Filament\Resources\SocialLinks;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\SocialLinks\Pages\CreateSocialLink;
use App\Filament\Resources\SocialLinks\Pages\EditSocialLink;
use App\Filament\Resources\SocialLinks\Pages\ListSocialLinks;
use App\Models\Image;
use App\Models\SocialLink;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $recordTitleAttribute = 'platform';

    protected static UnitEnum|string|null $navigationGroup = 'Nội dung';

    protected static ?string $navigationLabel = 'Liên kết mạng xã hội';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Liên kết mạng xã hội';

    protected static ?string $pluralModelLabel = 'Các liên kết mạng xã hội';

    public static function getNavigationBadge(): ?string
    {
        $activeCount = static::getModel()::query()
            ->where('active', true)
            ->count();

        return $activeCount > 0 ? (string) $activeCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Thông tin cơ bản')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('platform')
                                    ->label('Tên mạng xã hội')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('url')
                                    ->label('Đường dẫn')
                                    ->required()
                                    ->url()
                                    ->maxLength(255),
                                Toggle::make('active')
                                    ->label('Đang hiển thị')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(2),
                    ]),
                    
                Section::make('Biểu tượng')
                    ->description('Chọn icon từ thư viện hoặc tải lên icon mới')
                    ->schema([
                        Tabs::make('IconSelection')
                            ->tabs([
                                Tabs\Tab::make('Chọn từ thư viện')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        Select::make('icon_image_id')
                                            ->label('Biểu tượng có sẵn')
                                            ->relationship('iconImage', 'file_path', fn ($query) => $query->whereNull('model_id')->orWhereNull('model_type'))
                                            ->searchable()
                                            ->preload()
                                            ->getOptionLabelFromRecordUsing(fn (Image $record) => basename($record->file_path))
                                            ->helperText('Chọn icon có sẵn trong hệ thống'),
                                    ]),
                                Tabs\Tab::make('Tải lên mới')
                                    ->icon('heroicon-o-arrow-up-tray')
                                    ->schema([
                                        FileUpload::make('new_icon')
                                            ->label('Upload icon mới')
                                            ->image()
                                            ->disk('public')
                                            ->directory('icons')
                                            ->imageEditor()
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/*'])
                                            ->saveUploadedFileUsing(function ($file, $set, $get) {
                                                $filename = uniqid('icon_') . '.webp';
                                                $path = 'icons/' . $filename;
                                                $disk = 'public';

                                                $manager = new ImageManager(new Driver());
                                                $image = $manager->read($file->getRealPath());

                                                if ($image->width() > 256 || $image->height() > 256) {
                                                    $image->scale(width: 256);
                                                }

                                                $webp = $image->toWebp(quality: 90);
                                                Storage::disk($disk)->put($path, $webp);

                                                // Tạo Image record mới
                                                $imageRecord = Image::create([
                                                    'file_path' => $path,
                                                    'disk' => $disk,
                                                    'alt' => 'Social link icon',
                                                    'mime' => 'image/webp',
                                                    'active' => true,
                                                ]);

                                                // Set icon_image_id cho SocialLink
                                                $set('icon_image_id', $imageRecord->id);

                                                return $path;
                                            })
                                            ->dehydrated(false)
                                            ->helperText('Tải lên icon mới (tự động convert sang WebP, tối đa 256px)'),
                                    ]),
                            ])
                            ->contained(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('iconImage'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->label('Tên mạng xã hội')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\ImageColumn::make('iconImage.file_path')
                    ->label('Biểu tượng')
                    ->disk('public')
                    ->width(40)
                    ->height(40)
                    ->circular(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
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
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialLinks::route('/'),
            'create' => CreateSocialLink::route('/create'),
            'edit' => EditSocialLink::route('/{record}/edit'),
        ];
    }
}
