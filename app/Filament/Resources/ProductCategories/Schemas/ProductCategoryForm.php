<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductCategoryForm
{
    /**
     * Build the management form for product categories.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Category details')
                    ->columns(2)
                    ->schema([
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
                            ->unique(ignoreRecord: true)
                            ->rule('alpha_dash')
                            ->helperText('Used in URLs and redirects. Characters: letters, numbers, dash, underscore.'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Optional copy for tooltips or SEO content on landing pages.'),
                        Grid::make()
                            ->schema([
                                TextInput::make('order')
                                    ->label('Display order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1)
                                    ->helperText('Lower numbers appear first in menus & filters.'),
                                Toggle::make('active')
                                    ->label('Active')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}
