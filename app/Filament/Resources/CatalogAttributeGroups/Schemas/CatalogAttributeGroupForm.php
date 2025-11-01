<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

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
                Section::make('General')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique identifier for integrations, e.g. brand, country.'),
                        TextInput::make('name')
                            ->label('Display name')
                            ->required()
                            ->maxLength(255),
                        Select::make('filter_type')
                            ->label('Filter type')
                            ->required()
                            ->default('multi')
                            ->options([
                                'single' => 'Single',
                                'multi' => 'Multi',
                                'hierarchy' => 'Hierarchy',
                                'range' => 'Range',
                            ])
                            ->helperText('Controls how filters are rendered and queried on the storefront.'),
                        Grid::make()
                            ->schema([
                                Toggle::make('is_filterable')
                                    ->label('Filterable')
                                    ->default(true)
                                    ->inline(false),
                                Toggle::make('is_primary')
                                    ->label('Primary group')
                                    ->default(false)
                                    ->inline(false)
                                    ->helperText('Primary groups power key navigation such as breadcrumbs.'),
                            ])
                            ->columns(2),
                        TextInput::make('position')
                            ->label('Position')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Lower numbers appear first in listings.'),
                    ]),
                Section::make('Display configuration')
                    ->collapsed()
                    ->schema([
                        KeyValue::make('display_config')
                            ->keyLabel('Config key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addButtonLabel('Add configuration')
                            ->nullable()
                            ->helperText('Optional overrides for the frontend (icons, colors, templates, etc.).')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
