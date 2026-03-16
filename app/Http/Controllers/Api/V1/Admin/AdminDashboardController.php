<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\TrackingEvent;
use App\Models\VisitorSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    private const STATS_CACHE_TTL = 60;
    private const CHART_CACHE_TTL = 120;
    private const TOP_CACHE_TTL = 120;

    public function bootstrap(Request $request): JsonResponse
    {
        $days = $this->resolveDays($request);
        $limit = $this->resolveLimit($request, 5);

        $cacheKey = sprintf('admin_dashboard_bootstrap_v1_%d_%d', $days, $limit);

        $data = Cache::remember($cacheKey, self::STATS_CACHE_TTL, function () use ($days, $limit) {
            return [
                'stats' => $this->buildStatsData(),
                'traffic_chart' => $this->buildTrafficChartData($days),
                'top_products' => $this->buildTopProductsData($days, $limit),
                'top_articles' => $this->buildTopArticlesData($days, $limit),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function stats(): JsonResponse
    {
        $data = Cache::remember('admin_dashboard_stats_v1', self::STATS_CACHE_TTL, fn () => $this->buildStatsData());

        return response()->json(['data' => $data]);
    }

    public function trafficChart(Request $request): JsonResponse
    {
        $days = $this->resolveDays($request);
        $cacheKey = sprintf('admin_dashboard_traffic_chart_v1_%d', $days);

        $data = Cache::remember($cacheKey, self::CHART_CACHE_TTL, fn () => $this->buildTrafficChartData($days));

        return response()->json(['data' => $data]);
    }

    public function topProducts(Request $request): JsonResponse
    {
        $days = $this->resolveDays($request);
        $limit = $this->resolveLimit($request);
        $cacheKey = sprintf('admin_dashboard_top_products_v1_%d_%d', $days, $limit);

        $data = Cache::remember($cacheKey, self::TOP_CACHE_TTL, fn () => $this->buildTopProductsData($days, $limit));

        return response()->json(['data' => $data]);
    }

    public function topArticles(Request $request): JsonResponse
    {
        $days = $this->resolveDays($request);
        $limit = $this->resolveLimit($request);
        $cacheKey = sprintf('admin_dashboard_top_articles_v1_%d_%d', $days, $limit);

        $data = Cache::remember($cacheKey, self::TOP_CACHE_TTL, fn () => $this->buildTopArticlesData($days, $limit));

        return response()->json(['data' => $data]);
    }

    public function recentEvents(Request $request): JsonResponse
    {
        $limit = min($request->integer('limit', 20), 100);

        $events = TrackingEvent::query()
            ->with(['product:id,name,slug', 'article:id,title,slug', 'visitor:id,anon_id'])
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'event_type' => $e->event_type,
                'event_label' => match ($e->event_type) {
                    TrackingEvent::TYPE_PRODUCT_VIEW => 'Xem sản phẩm',
                    TrackingEvent::TYPE_ARTICLE_VIEW => 'Xem bài viết',
                    TrackingEvent::TYPE_CTA_CONTACT => 'Click liên hệ',
                    default => $e->event_type,
                },
                'product' => $e->product ? [
                    'id' => $e->product->id,
                    'name' => $e->product->name,
                    'slug' => $e->product->slug,
                ] : null,
                'article' => $e->article ? [
                    'id' => $e->article->id,
                    'title' => $e->article->title,
                    'slug' => $e->article->slug,
                ] : null,
                'visitor_id' => $e->visitor?->anon_id,
                'occurred_at' => $e->occurred_at->toIso8601String(),
                'time_ago' => $e->occurred_at->diffForHumans(),
            ]);

        return response()->json(['data' => $events]);
    }

    private function buildStatsData(): array
    {
        $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $today = Carbon::today($tz);
        $yesterday = Carbon::yesterday($tz);
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();
        $yesterdayStart = $yesterday->copy()->startOfDay();
        $yesterdayEnd = $yesterday->copy()->endOfDay();
        $lastWeek = Carbon::now($tz)->subDays(7);
        $lastMonth = Carbon::now($tz)->subDays(30);

        $productCounts = Product::query()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        $articleCounts = Article::query()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        $todayTraffic = TrackingEvent::query()
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors')
            ->selectRaw('COUNT(*) as page_views')
            ->selectRaw('SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as product_views', [TrackingEvent::TYPE_PRODUCT_VIEW])
            ->selectRaw('SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as article_views', [TrackingEvent::TYPE_ARTICLE_VIEW])
            ->selectRaw('SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as cta_clicks', [TrackingEvent::TYPE_CTA_CONTACT])
            ->whereBetween('occurred_at', [$todayStart, $todayEnd])
            ->first();

        $yesterdayTraffic = TrackingEvent::query()
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors')
            ->selectRaw('COUNT(*) as page_views')
            ->whereBetween('occurred_at', [$yesterdayStart, $yesterdayEnd])
            ->first();

        $lastWeekTraffic = TrackingEvent::query()
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors')
            ->selectRaw('COUNT(*) as page_views')
            ->where('occurred_at', '>=', $lastWeek)
            ->first();

        $lastMonthTraffic = TrackingEvent::query()
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors')
            ->selectRaw('COUNT(*) as page_views')
            ->where('occurred_at', '>=', $lastMonth)
            ->first();

        return [
            'products' => [
                'total' => (int) ($productCounts?->total ?? 0),
                'active' => (int) ($productCounts?->active ?? 0),
            ],
            'articles' => [
                'total' => (int) ($articleCounts?->total ?? 0),
                'active' => (int) ($articleCounts?->active ?? 0),
            ],
            'categories' => ProductCategory::where('active', true)->count(),
            'types' => ProductType::where('active', true)->count(),
            'traffic' => [
                'today' => [
                    'visitors' => (int) ($todayTraffic?->visitors ?? 0),
                    'sessions' => VisitorSession::whereBetween('started_at', [$todayStart, $todayEnd])->count(),
                    'page_views' => (int) ($todayTraffic?->page_views ?? 0),
                    'product_views' => (int) ($todayTraffic?->product_views ?? 0),
                    'article_views' => (int) ($todayTraffic?->article_views ?? 0),
                    'cta_clicks' => (int) ($todayTraffic?->cta_clicks ?? 0),
                ],
                'yesterday' => [
                    'visitors' => (int) ($yesterdayTraffic?->visitors ?? 0),
                    'page_views' => (int) ($yesterdayTraffic?->page_views ?? 0),
                ],
                'last_7_days' => [
                    'visitors' => (int) ($lastWeekTraffic?->visitors ?? 0),
                    'page_views' => (int) ($lastWeekTraffic?->page_views ?? 0),
                ],
                'last_30_days' => [
                    'visitors' => (int) ($lastMonthTraffic?->visitors ?? 0),
                    'page_views' => (int) ($lastMonthTraffic?->page_views ?? 0),
                ],
            ],
        ];
    }

    private function buildTrafficChartData(int $days): array
    {
        $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now = Carbon::now($tz);
        $startDate = $now->copy()->startOfDay()->subDays($days - 1);
        $endDate = $now->copy()->endOfDay();

        $statsByDate = TrackingEvent::query()
            ->selectRaw('
                DATE(occurred_at) as date,
                COUNT(*) as total_events,
                COUNT(DISTINCT visitor_id) as unique_visitors,
                SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as product_views,
                SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as article_views,
                SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as cta_clicks
            ', [
                TrackingEvent::TYPE_PRODUCT_VIEW,
                TrackingEvent::TYPE_ARTICLE_VIEW,
                TrackingEvent::TYPE_CTA_CONTACT,
            ])
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        for ($i = 0; $i < $days; $i++) {
            $dayStart = $startDate->copy()->addDays($i)->startOfDay();
            $date = $dayStart->format('Y-m-d');
            $dayStats = $statsByDate->get($date);

            $chartData[] = [
                'date' => $date,
                'label' => $dayStart->format('d/m'),
                'visitors' => (int) ($dayStats->unique_visitors ?? 0),
                'page_views' => (int) ($dayStats->total_events ?? 0),
                'product_views' => (int) ($dayStats->product_views ?? 0),
                'article_views' => (int) ($dayStats->article_views ?? 0),
                'cta_clicks' => (int) ($dayStats->cta_clicks ?? 0),
            ];
        }

        return $chartData;
    }

    private function buildTopProductsData(int $days, int $limit): array
    {
        $startDate = Carbon::now()->subDays($days);

        $topProducts = TrackingEvent::query()
            ->select('product_id', DB::raw('COUNT(*) as views'))
            ->where('event_type', TrackingEvent::TYPE_PRODUCT_VIEW)
            ->whereNotNull('product_id')
            ->where('occurred_at', '>=', $startDate)
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();

        $productIds = $topProducts->pluck('product_id');

        $products = Product::with(['coverImage'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        return $topProducts->map(fn ($item) => [
            'id' => $item->product_id,
            'name' => $products[$item->product_id]?->name ?? 'Sản phẩm đã xóa',
            'slug' => $products[$item->product_id]?->slug,
            'image_url' => $products[$item->product_id]?->coverImage?->url,
            'views' => (int) $item->views,
        ])->values()->all();
    }

    private function buildTopArticlesData(int $days, int $limit): array
    {
        $startDate = Carbon::now()->subDays($days);

        $topArticles = TrackingEvent::query()
            ->select('article_id', DB::raw('COUNT(*) as views'))
            ->where('event_type', TrackingEvent::TYPE_ARTICLE_VIEW)
            ->whereNotNull('article_id')
            ->where('occurred_at', '>=', $startDate)
            ->groupBy('article_id')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();

        $articleIds = $topArticles->pluck('article_id');

        $articles = Article::with(['coverImage'])
            ->whereIn('id', $articleIds)
            ->get()
            ->keyBy('id');

        return $topArticles->map(fn ($item) => [
            'id' => $item->article_id,
            'title' => $articles[$item->article_id]?->title ?? 'Bài viết đã xóa',
            'slug' => $articles[$item->article_id]?->slug,
            'image_url' => $articles[$item->article_id]?->coverImage?->url,
            'views' => (int) $item->views,
        ])->values()->all();
    }

    private function resolveDays(Request $request): int
    {
        return min($request->integer('days', 7), 90);
    }

    private function resolveLimit(Request $request, int $default = 10): int
    {
        return min($request->integer('limit', $default), 50);
    }
}
