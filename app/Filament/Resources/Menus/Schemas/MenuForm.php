<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Tiêu đề menu')
                    ->helperText('Tên hiển thị trên menu. Để trống nếu dùng từ thuật ngữ')
                    ->default(null),
                Select::make('term_id')
                    ->label('Thuật ngữ liên kết')
                    ->helperText('Chọn thuật ngữ để tự động lấy tên và liên kết')
                    ->relationship('term', 'name')
                    ->searchable()
                    ->default(null),
                Select::make('type')
                    ->label('Kiểu menu')
                    ->helperText('Menu thường (standard) hoặc menu mở rộng (mega)')
                    ->options([
                        'standard' => 'Menu thường',
                        'mega' => 'Menu mở rộng (Mega)',
                    ])
                    ->required()
                    ->default('standard'),
                TextInput::make('href')
                    ->label('Đường dẫn')
                    ->helperText('Đường dẫn tùy chỉnh (nếu không dùng thuật ngữ)')
                    ->default(null),
                Textarea::make('config')
                    ->label('Cấu hình JSON')
                    ->helperText('Cấu hình bổ sung dạng JSON (dành cho dev)')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('order')
                    ->label('Thứ tự hiển thị')
                    ->helperText('Số nhỏ sẽ hiển thị trước')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->label('Đang hiển thị')
                    ->helperText('Bật để hiển thị mục menu này')
                    ->required()
                    ->default(true),
            ]);
    }
}
