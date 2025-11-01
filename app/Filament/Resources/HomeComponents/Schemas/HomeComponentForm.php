<?php

namespace App\Filament\Resources\HomeComponents\Schemas;

use App\Models\HomeComponent;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HomeComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Component configuration')
                    ->columns(2)
                    ->schema([
                        TextInput::make('type')
                            ->label('Component type')
                            ->required()
                            ->maxLength(120)
                            ->datalist(self::typeSuggestions())
                            ->helperText('Identifier used by the frontend renderer, e.g. hero_banner, featured_products.'),
                        Grid::make()
                            ->schema([
                                TextInput::make('order')
                                    ->label('Display order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1),
                                Toggle::make('active')
                                    ->label('Active')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        KeyValue::make('config')
                            ->label('Display configuration')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addButtonLabel('Add config item')
                            ->nullable()
                            ->columnSpanFull()
                            ->helperText('Optional key-value pairs consumed by the frontend (e.g. title, limit, theme).'),
                    ]),
            ]);
    }

    /**
     * @return list<string>
     */
    protected static function typeSuggestions(): array
    {
        return HomeComponent::query()
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort()
            ->values()
            ->all();
    }
}
