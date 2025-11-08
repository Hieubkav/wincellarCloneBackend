<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CatalogAttributeGroupForm
{
    /**
     * Compose the form used to manage catalog attribute groups.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin chung')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Mã nhóm')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Mã định danh duy nhất. Ví dụ: brand, country, region, grape'),
                        TextInput::make('name')
                            ->label('Tên hiển thị')
                            ->helperText('Ví dụ: Thương hiệu, Quốc gia, Vùng miền, Giống nho')
                            ->required()
                            ->maxLength(255),
                        Select::make('filter_type')
                            ->label('Kiểu bộ lọc')
                            ->required()
                            ->default('chon_nhieu')
                            ->options([
                                'chon_don' => 'Chọn đơn',
                                'chon_nhieu' => 'Chọn nhiều',
                            ])
                            ->helperText('Quyết định cách hiển thị bộ lọc trên website'),
                        Toggle::make('is_filterable')
                            ->label('Cho phép lọc')
                            ->helperText('Bật để hiển thị trong bộ lọc')
                            ->default(true)
                            ->inline(false),
                        TextInput::make('position')
                            ->label('Thứ tự hiển thị')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Số nhỏ sẽ hiển thị trước'),
                    ]),
                Section::make('Icon')
                    ->collapsed()
                    ->description('Upload icon cho nhóm thuộc tính')
                    ->schema([
                        FileUpload::make('icon_path')
                            ->label('Icon nhóm thuộc tính')
                            ->image()
                            ->disk('public')
                            ->directory('catalog-attribute-group-icons')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/*'])
                            ->helperText('Upload icon cho nhóm. Ảnh sẽ tự động chuyển sang webp và tối ưu.')
                            ->saveUploadedFileUsing(function ($file, $record) {
                                // Tạo tên file unique
                                $filename = uniqid() . '.webp';
                                $path = 'catalog-attribute-group-icons/' . $filename;
                                
                                // Convert sang webp và lưu
                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($file->getRealPath());
                                $image->scale(width: 200); // Resize về 200px width
                                $webp = $image->toWebp(quality: 85);
                                
                                Storage::disk('public')->put($path, $webp);
                                
                                return $path;
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
