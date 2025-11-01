<?php

namespace App\Filament\Pages;

use App\Models\TrackingEventAggregateDaily;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateRangeFilter;
use UnitEnum;

class TrackingEventAggregateDailyPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament-panels::pages.page';

    protected static UnitEnum | string | null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Daily Analytics Report';

    public function table(Table $table): Table
    {
        return $table
            ->query(TrackingEventAggregateDaily::query())
            ->columns([
                TextColumn::make('date')->date()->sortable(),
                TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'product_view' => 'success',
                        'article_view' => 'info',
                        'cta_contact' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('product.name')->label('Product')->placeholder('N/A'),
                TextColumn::make('article.title')->label('Article')->placeholder('N/A'),
                TextColumn::make('views')
                    ->summarize(Sum::make())
                    ->alignEnd(),
                TextColumn::make('clicks')
                    ->summarize(Sum::make())
                    ->alignEnd(),
            ])
            ->filters([
                DateRangeFilter::make('date'),
                SelectFilter::make('event_type')
                    ->options([
                        'product_view' => 'Product View',
                        'article_view' => 'Article View',
                        'cta_contact' => 'CTA Contact',
                    ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
