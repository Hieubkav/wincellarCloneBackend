<?php

namespace App\Filament\Resources\UrlRedirects;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\UrlRedirects\Pages\CreateUrlRedirect;
use App\Filament\Resources\UrlRedirects\Pages\EditUrlRedirect;
use App\Filament\Resources\UrlRedirects\Pages\ListUrlRedirects;
use App\Filament\Resources\UrlRedirects\Pages\ViewUrlRedirect;
use App\Models\Article;
use App\Models\Product;
use App\Models\UrlRedirect;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Select as SchemaSelect;
use Filament\Schemas\Components\TextInput as SchemaTextInput;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UrlRedirectResource extends Resource
{
    protected static ?string $model = UrlRedirect::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedArrowRightCircle;

    protected static ?string $recordTitleAttribute = 'from_slug';

    protected static UnitEnum|string|null $navigationGroup = 'Cài đặt';

    protected static ?string $navigationLabel = 'Chuyển hướng URL';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Chuyển hướng URL';

    protected static ?string $pluralModelLabel = 'Các chuyển hướng URL';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaGrid::make()
                    ->schema([
                        SchemaTextInput::make('from_slug')
                            ->label('Slug cũ')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->helperText('Slug cũ, không được trùng với slug hiện hữu của Product hoặc Article'),
                        SchemaTextInput::make('to_slug')
                            ->label('Slug mới')
                            ->required()
                            ->maxLength(255)
                            ->rules(['alpha_dash']),
                        SchemaSelect::make('target_type')
                            ->label('Loại mục tiêu')
                            ->options([
                                UrlRedirect::TARGET_PRODUCT => 'Sản phẩm',
                                UrlRedirect::TARGET_ARTICLE => 'Bài viết',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('target_id', null);
                            }),
                        SchemaSelect::make('target_id')
                            ->label('Mục tiêu')
                            ->options(function (callable $get) {
                                $type = $get('target_type');
                                if ($type === UrlRedirect::TARGET_PRODUCT) {
                                    return Product::active()->pluck('name', 'id');
                                } elseif ($type === UrlRedirect::TARGET_ARTICLE) {
                                    return Article::active()->pluck('title', 'id');
                                }
                                return [];
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
        Tables\Columns\TextColumn::make('from_slug')
        ->label('Slug cũ')
        ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('to_slug')
            ->label('Slug mới')
        ->searchable(),
        Tables\Columns\TextColumn::make('target_type')
        ->label('Loại mục tiêu')
        ->badge(),
        Tables\Columns\TextColumn::make('target.name')
        ->label('Tên mục tiêu')
            ->getStateUsing(function (UrlRedirect $record) {
            return $record->target?->name ?? $record->target?->title ?? 'Không có';
            }),
        Tables\Columns\TextColumn::make('hit_count')
        ->label('Số lần truy cập')
                ->sortable(),
                Tables\Columns\TextColumn::make('last_triggered_at')
                    ->label('Lần cuối kích hoạt')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
            //
            ]);
    }

    public static function getTableActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
        ];
    }

    public static function getTableBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
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
            'index' => ListUrlRedirects::route('/'),
            'create' => CreateUrlRedirect::route('/create'),
            'view' => ViewUrlRedirect::route('/{record}'),
            'edit' => EditUrlRedirect::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['target']);
    }
}
