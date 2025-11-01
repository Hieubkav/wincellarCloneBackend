<?php

namespace App\Filament\Pages;

use App\Models\TrackingEventAggregateDaily;
use App\Models\UrlRedirect;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use UnitEnum;

class AnalyticsDashboard extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected string $view = 'filament-panels::pages.page';

    protected static UnitEnum | string | null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Analytics Dashboard';

    public function getWidgets(): array
    {
        return [
            // Could add widgets here
        ];
    }

    public function getStats(): array
    {
        $totalViews = TrackingEventAggregateDaily::sum('views');
        $totalClicks = TrackingEventAggregateDaily::sum('clicks');
        $totalRedirectHits = UrlRedirect::sum('hit_count');
        $uniqueVisitors = TrackingEventAggregateDaily::distinct('visitor_id')->count('visitor_id');

        return [
            Stat::make('Total Views', $totalViews)
                ->icon('heroicon-o-eye'),
            Stat::make('Total Clicks', $totalClicks)
                ->icon('heroicon-o-cursor-arrow-rays'),
            Stat::make('Redirect Hits', $totalRedirectHits)
                ->icon('heroicon-o-arrow-right-circle'),
            Stat::make('Unique Visitors', $uniqueVisitors)
                ->icon('heroicon-o-users'),
        ];
    }
}
