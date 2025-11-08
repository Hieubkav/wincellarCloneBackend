<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuBlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'blocks';

    protected static ?string $title = 'Các khối menu';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin khối menu')
                    ->description('Khối menu chứa các mục menu con (dùng cho mega menu)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Tiêu đề khối')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('attribute_group_id')
                            ->label('Nhóm thuộc tính')
                            ->relationship('attributeGroup', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Chọn nhóm thuộc tính để tự động lấy các terms'),

                        TextInput::make('max_terms')
                            ->label('Giới hạn số lượng mục')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Để trống = không giới hạn'),

                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->default(true)
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Cấu hình bổ sung')
                    ->collapsed()
                    ->schema([
                        Textarea::make('config')
                            ->label('Cấu hình JSON')
                            ->rows(4)
                            ->helperText('Cấu hình custom dạng JSON (nếu cần)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['attributeGroup']))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width(60)
                    ->alignCenter()
                    ->color('gray'),

                TextColumn::make('title')
                    ->label('Tiêu đề khối')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-rectangle-group')
                    ->color('info'),

                TextColumn::make('attributeGroup.name')
                    ->label('Nhóm thuộc tính')
                    ->badge()
                    ->color('purple')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('max_terms')
                    ->label('Giới hạn')
                    ->numeric()
                    ->sortable()
                    ->placeholder('∞')
                    ->alignCenter(),

                TextColumn::make('items_count')
                    ->label('Số mục')
                    ->counts('items')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tạo khối menu')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->modalHeading('Tạo khối menu mới')
                    ->modalWidth('2xl'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->color('warning'),
                DeleteAction::make()
                    ->iconButton()
                    ->color('danger'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Chưa có khối menu nào')
            ->emptyStateDescription('Tạo khối menu đầu tiên để bắt đầu xây dựng mega menu')
            ->emptyStateIcon('heroicon-o-rectangle-group');
    }
}
