<?php

namespace App\Filament\Widgets;

use App\Models\TrackingEvent;
use App\Models\Visitor;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrafficOverviewWidget extends BaseWidget
{
    protected static ?int $sort = -2;
    
    protected ?string $pollingInterval = '30s';
    
    protected ?string $heading = 'Tổng quan Traffic';
    
    protected string $view = 'filament.widgets.traffic-overview-widget';
    
    public ?string $filter = 'today';

    protected function getStats(): array
    {
        $period = $this->filter ?? 'today';
        
        [$startDate, $endDate] = $this->getDateRange($period);
        
        $totalVisits = TrackingEvent::whereBetween('occurred_at', [$startDate, $endDate])->count();
        
        $uniqueVisitors = Visitor::whereBetween('last_seen_at', [$startDate, $endDate])->count();
        
        $productViews = TrackingEvent::where('event_type', TrackingEvent::TYPE_PRODUCT_VIEW)
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->count();
        
        $ctaContacts = TrackingEvent::where('event_type', TrackingEvent::TYPE_CTA_CONTACT)
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->count();

        $previousPeriod = $this->getPreviousPeriodData($period, $startDate, $endDate);

        return [
            Stat::make('Tổng lượt truy cập', number_format($totalVisits))
                ->description($this->getChangeDescription($totalVisits, $previousPeriod['visits']))
                ->descriptionIcon($this->getChangeIcon($totalVisits, $previousPeriod['visits']))
                ->color($this->getChangeColor($totalVisits, $previousPeriod['visits']))
                ->chart($this->getChartData($period, 'total')),
            
            Stat::make('Người dùng duy nhất', number_format($uniqueVisitors))
                ->description($this->getChangeDescription($uniqueVisitors, $previousPeriod['visitors']))
                ->descriptionIcon($this->getChangeIcon($uniqueVisitors, $previousPeriod['visitors']))
                ->color($this->getChangeColor($uniqueVisitors, $previousPeriod['visitors']))
                ->chart($this->getChartData($period, 'visitors')),
            
            Stat::make('Lượt xem sản phẩm', number_format($productViews))
                ->description($this->getChangeDescription($productViews, $previousPeriod['product_views']))
                ->descriptionIcon($this->getChangeIcon($productViews, $previousPeriod['product_views']))
                ->color($this->getChangeColor($productViews, $previousPeriod['product_views']))
                ->chart($this->getChartData($period, 'product_views')),
            
            Stat::make('Lượt nhấn liên hệ', number_format($ctaContacts))
                ->description($this->getChangeDescription($ctaContacts, $previousPeriod['cta_contacts']))
                ->descriptionIcon($this->getChangeIcon($ctaContacts, $previousPeriod['cta_contacts']))
                ->color($this->getChangeColor($ctaContacts, $previousPeriod['cta_contacts']))
                ->chart($this->getChartData($period, 'cta_contacts')),
        ];
    }

    protected function getDateRange(string $period): array
    {
        $now = Carbon::now();
        
        return match($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'all' => [Carbon::parse('2020-01-01'), $now->copy()->endOfDay()],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    protected function getPreviousPeriodData(string $period, Carbon $startDate, Carbon $endDate): array
    {
        $diff = $startDate->diffInDays($endDate) + 1;
        $previousStart = $startDate->copy()->subDays($diff);
        $previousEnd = $endDate->copy()->subDays($diff);

        return [
            'visits' => TrackingEvent::whereBetween('occurred_at', [$previousStart, $previousEnd])->count(),
            'visitors' => Visitor::whereBetween('last_seen_at', [$previousStart, $previousEnd])->count(),
            'product_views' => TrackingEvent::where('event_type', TrackingEvent::TYPE_PRODUCT_VIEW)
                ->whereBetween('occurred_at', [$previousStart, $previousEnd])
                ->count(),
            'cta_contacts' => TrackingEvent::where('event_type', TrackingEvent::TYPE_CTA_CONTACT)
                ->whereBetween('occurred_at', [$previousStart, $previousEnd])
                ->count(),
        ];
    }

    protected function getChangeDescription(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? '100% tăng so với kỳ trước' : 'Không có dữ liệu kỳ trước';
        }

        $change = (($current - $previous) / $previous) * 100;
        $changeFormatted = number_format(abs($change), 1);

        return $change >= 0 
            ? "{$changeFormatted}% tăng so với kỳ trước"
            : "{$changeFormatted}% giảm so với kỳ trước";
    }

    protected function getChangeIcon(int $current, int $previous): string
    {
        return $current >= $previous ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    protected function getChangeColor(int $current, int $previous): string
    {
        return $current >= $previous ? 'success' : 'danger';
    }

    protected function getChartData(string $period, string $type): array
    {
        $now = Carbon::now();
        
        $days = match($period) {
            'today' => 24, // hours
            'week' => 7,
            'month' => 30,
            'year' => 12, // months
            'all' => 12, // months
            default => 7,
        };

        $data = [];
        
        if ($period === 'today') {
            for ($i = $days - 1; $i >= 0; $i--) {
                $hour = $now->copy()->subHours($i)->startOfHour();
                $data[] = $this->getDataForHour($hour, $type);
            }
        } elseif (in_array($period, ['year', 'all'])) {
            for ($i = $days - 1; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i)->startOfMonth();
                $data[] = $this->getDataForMonth($month, $type);
            }
        } else {
            for ($i = $days - 1; $i >= 0; $i--) {
                $day = $now->copy()->subDays($i)->startOfDay();
                $data[] = $this->getDataForDay($day, $type);
            }
        }

        return $data;
    }

    protected function getDataForHour(Carbon $hour, string $type): int
    {
        $query = match($type) {
            'visitors' => Visitor::whereBetween('last_seen_at', [$hour, $hour->copy()->endOfHour()]),
            'product_views' => TrackingEvent::where('event_type', TrackingEvent::TYPE_PRODUCT_VIEW)
                ->whereBetween('occurred_at', [$hour, $hour->copy()->endOfHour()]),
            'cta_contacts' => TrackingEvent::where('event_type', TrackingEvent::TYPE_CTA_CONTACT)
                ->whereBetween('occurred_at', [$hour, $hour->copy()->endOfHour()]),
            default => TrackingEvent::whereBetween('occurred_at', [$hour, $hour->copy()->endOfHour()]),
        };

        return $query->count();
    }

    protected function getDataForDay(Carbon $day, string $type): int
    {
        $query = match($type) {
            'visitors' => Visitor::whereBetween('last_seen_at', [$day, $day->copy()->endOfDay()]),
            'product_views' => TrackingEvent::where('event_type', TrackingEvent::TYPE_PRODUCT_VIEW)
                ->whereBetween('occurred_at', [$day, $day->copy()->endOfDay()]),
            'cta_contacts' => TrackingEvent::where('event_type', TrackingEvent::TYPE_CTA_CONTACT)
                ->whereBetween('occurred_at', [$day, $day->copy()->endOfDay()]),
            default => TrackingEvent::whereBetween('occurred_at', [$day, $day->copy()->endOfDay()]),
        };

        return $query->count();
    }

    protected function getDataForMonth(Carbon $month, string $type): int
    {
        $query = match($type) {
            'visitors' => Visitor::whereBetween('last_seen_at', [$month, $month->copy()->endOfMonth()]),
            'product_views' => TrackingEvent::where('event_type', TrackingEvent::TYPE_PRODUCT_VIEW)
                ->whereBetween('occurred_at', [$month, $month->copy()->endOfMonth()]),
            'cta_contacts' => TrackingEvent::where('event_type', TrackingEvent::TYPE_CTA_CONTACT)
                ->whereBetween('occurred_at', [$month, $month->copy()->endOfMonth()]),
            default => TrackingEvent::whereBetween('occurred_at', [$month, $month->copy()->endOfMonth()]),
        };

        return $query->count();
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hôm nay',
            'week' => 'Tuần này',
            'month' => 'Tháng này',
            'year' => 'Năm nay',
            'all' => 'Tất cả',
        ];
    }
}
