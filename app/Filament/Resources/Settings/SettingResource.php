<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Models\Image;
use App\Models\Setting;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static UnitEnum|string|null $navigationGroup = 'Hệ thống';

    protected static ?string $navigationLabel = 'Cấu hình chung';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Cấu hình chung';

    protected static ?string $pluralModelLabel = 'Cấu hình chung';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Thương hiệu')
                    ->description('Logo và favicon cho website')
                    ->schema([
                        // Logo
                        Grid::make()
                            ->schema([
                                Tabs::make('LogoSelection')
                                    ->label('Logo')
                                    ->tabs([
                                        Tabs\Tab::make('Chọn từ thư viện')
                                            ->icon('heroicon-o-photo')
                                            ->schema([
                                                Select::make('logo_image_id')
                                                    ->label('Logo có sẵn')
                                                    ->relationship('logoImage', 'file_path', fn ($query) => $query->whereNull('model_id')->orWhereNull('model_type'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->getOptionLabelFromRecordUsing(fn (Image $record) => basename($record->file_path))
                                                    ->helperText('Chọn logo có sẵn trong hệ thống'),
                                            ]),
                                        Tabs\Tab::make('Tải lên mới')
                                            ->icon('heroicon-o-arrow-up-tray')
                                            ->schema([
                                                FileUpload::make('new_logo')
                                                    ->label('Upload logo mới')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('branding')
                                                    ->imageEditor()
                                                    ->maxSize(5120)
                                                    ->acceptedFileTypes(['image/*'])
                                                    ->saveUploadedFileUsing(function ($file, $set) {
                                                        $filename = 'logo_' . time() . '.webp';
                                                        $path = 'branding/' . $filename;
                                                        $disk = 'public';

                                                        $manager = new ImageManager(new Driver());
                                                        $image = $manager->read($file->getRealPath());

                                                        if ($image->width() > 800) {
                                                            $image->scale(width: 800);
                                                        }

                                                        $webp = $image->toWebp(quality: 90);
                                                        Storage::disk($disk)->put($path, $webp);

                                                        // Observer will auto-fill: width, height, mime via saving() hook
                                                        $imageRecord = Image::create([
                                                            'file_path' => $path,
                                                            'disk' => $disk,
                                                            'alt' => 'Website logo',
                                                            'mime' => 'image/webp',
                                                            'active' => true,
                                                            // width, height auto-extracted by ImageObserver::saving()
                                                        ]);

                                                        $set('logo_image_id', $imageRecord->id);

                                                        return $path;
                                                    })
                                                    ->dehydrated(false)
                                                    ->helperText('Tải lên logo mới (tự động convert sang WebP, khuyến nghị tối đa 800px width)'),
                                            ]),
                                    ])
                                    ->contained(false)
                                    ->columnSpanFull(),
                            ]),

                        // Favicon
                        Grid::make()
                            ->schema([
                                Tabs::make('FaviconSelection')
                                    ->label('Favicon')
                                    ->tabs([
                                        Tabs\Tab::make('Chọn từ thư viện')
                                            ->icon('heroicon-o-photo')
                                            ->schema([
                                                Select::make('favicon_image_id')
                                                    ->label('Favicon có sẵn')
                                                    ->relationship('faviconImage', 'file_path', fn ($query) => $query->whereNull('model_id')->orWhereNull('model_type'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->getOptionLabelFromRecordUsing(fn (Image $record) => basename($record->file_path))
                                                    ->helperText('Chọn favicon có sẵn trong hệ thống'),
                                            ]),
                                        Tabs\Tab::make('Tải lên mới')
                                            ->icon('heroicon-o-arrow-up-tray')
                                            ->schema([
                                                FileUpload::make('new_favicon')
                                                    ->label('Upload favicon mới')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('branding')
                                                    ->imageEditor()
                                                    ->maxSize(1024)
                                                    ->acceptedFileTypes(['image/*'])
                                                    ->saveUploadedFileUsing(function ($file, $set) {
                                                        $filename = 'favicon_' . time() . '.webp';
                                                        $path = 'branding/' . $filename;
                                                        $disk = 'public';

                                                        $manager = new ImageManager(new Driver());
                                                        $image = $manager->read($file->getRealPath());

                                                        if ($image->width() > 64 || $image->height() > 64) {
                                                            $image->scale(width: 64);
                                                        }

                                                        $webp = $image->toWebp(quality: 90);
                                                        Storage::disk($disk)->put($path, $webp);

                                                        // Observer will auto-fill: width, height, mime via saving() hook
                                                        $imageRecord = Image::create([
                                                            'file_path' => $path,
                                                            'disk' => $disk,
                                                            'alt' => 'Website favicon',
                                                            'mime' => 'image/webp',
                                                            'active' => true,
                                                            // width, height auto-extracted by ImageObserver::saving()
                                                        ]);

                                                        $set('favicon_image_id', $imageRecord->id);

                                                        return $path;
                                                    })
                                                    ->dehydrated(false)
                                                    ->helperText('Tải lên favicon mới (tự động convert sang WebP, khuyến nghị 32x32 hoặc 64x64 px)'),
                                            ]),
                                    ])
                                    ->contained(false)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Thông tin liên hệ')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('site_name')
                                    ->label('Tên website')
                                    ->maxLength(255),
                                TextInput::make('hotline')
                                    ->label('Hotline')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('hours')
                                    ->label('Giờ làm việc')
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                        Textarea::make('address')
                            ->label('Địa chỉ')
                            ->rows(3)
                            ->maxLength(500),
                    ]),

                Section::make('SEO mặc định')
                    ->description('Meta tags mặc định cho toàn bộ website')
                    ->schema([
                        TextInput::make('meta_default_title')
                            ->label('Tiêu đề mặc định')
                            ->maxLength(255),
                        Textarea::make('meta_default_description')
                            ->label('Mô tả mặc định')
                            ->rows(3)
                            ->maxLength(500),
                        Textarea::make('meta_default_keywords')
                            ->label('Từ khóa mặc định')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('Các từ khóa cách nhau bằng dấu phẩy'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditSetting::route('/'),
        ];
    }
}
