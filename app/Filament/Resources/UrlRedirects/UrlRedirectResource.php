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

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'URL Redirects';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'URL Redirect';

    protected static ?string $pluralModelLabel = 'URL Redirects';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaGrid::make()
                    ->schema([
                        SchemaTextInput::make('from_slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->helperText('Slug cũ, không được trùng với slug hiện hữu của Product hoặc Article'),
                        SchemaTextInput::make('to_slug')
                            ->required()
                            ->maxLength(255)
                            ->rules(['alpha_dash']),
                        SchemaSelect::make('target_type')
                            ->options([
                                UrlRedirect::TARGET_PRODUCT => 'Product',
                                UrlRedirect::TARGET_ARTICLE => 'Article',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('target_id', null);
                            }),
                        SchemaSelect::make('target_id')
                            ->label('Target')
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to_slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('target_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('target.name')
                    ->label('Target Name')
                    ->getStateUsing(function (UrlRedirect $record) {
                        return $record->target?->name ?? $record->target?->title ?? 'N/A';
                    }),
                Tables\Columns\TextColumn::make('hit_count')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_triggered_at')
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
