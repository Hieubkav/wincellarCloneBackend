<?php

namespace App\Filament\Resources\CatalogTerms\Schemas;

use App\Models\CatalogAttributeGroup;
use App\Models\CatalogTerm;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CatalogTermForm
{
    /**
     * Configure the catalog term management form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Term details')
                    ->columns(2)
                    ->schema([
                        Select::make('group_id')
                            ->label('Attribute group')
                            ->required()
                            ->options(fn (): array => CatalogAttributeGroup::query()
                                ->orderBy('position')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('parent_id')
                            ->label('Parent term')
                            ->placeholder('Top level')
                            ->options(function (Get $get, ?CatalogTerm $record): array {
                                $groupId = $get('group_id');

                                if (!$groupId) {
                                    return [];
                                }

                                return CatalogTerm::query()
                                    ->where('group_id', $groupId)
                                    ->when(
                                        $record,
                                        fn ($query) => $query->where('id', '!=', $record->getKey())
                                    )
                                    ->orderBy('position')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, Get $get): void {
                                if (blank($get('slug')) && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->rule('alpha_dash')
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in URLs and integrations. Allowed: letters, numbers, dash, underscore.'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Optional copy for tooltips or landing pages.'),
                        Grid::make()
                            ->schema([
                                TextInput::make('icon_type')
                                    ->label('Icon type')
                                    ->maxLength(50),
                                TextInput::make('icon_value')
                                    ->label('Icon value')
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                        Grid::make()
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->inline(false),
                                TextInput::make('position')
                                    ->label('Display order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1)
                                    ->helperText('Lower numbers appear first within the group.'),
                            ])
                            ->columns(2),
                    ]),
                Section::make('Metadata')
                    ->collapsed()
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addButtonLabel('Add metadata')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
