<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Article;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\TrackingEvent;
use App\Models\VisitorSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $today = Carbon::today($tz);
        $yesterday = Carbon::yesterday($tz);
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();
        $lastWeek = Carbon::now()->subDays(7);
        $lastMonth = Carbon::now()->subDays(30);

        return response()->json([
            'data' => [
                'products' => [
                    'total' => Product::count(),
                    'active' => Product::where('active', true)->count(),
                ],
                'articles' => [
                    'total' => Article::count(),
                    'active' => Article::where('active', true)->count(),
                ],
                'categories' => ProductCategory::where('active', true)->count(),
                'types' => ProductType::where('active', true)->count(),
                'traffic' => [
                    'today' => [
                        'visitors' => TrackingEvent::whereBetween('occurred_at', [$todayStart, $todayEnd])
                            ->distinct('visitor_id')
                            ->count('visitor_id'),
                        'sessions' => VisitorSession::whereBetween('started_at', [$todayStart, $todayEnd])->count(),
                        'page_views' => TrackingEvent::whereBetween('occurred_at', [$todayStart, $todayEnd])->count(),
                        'product_views' => TrackingEvent::whereBetween('occurred_at', [$todayStart, $todayEnd])
                            ->where('event_type', TrackingEvent::TYPE_PRODUCT_VIEW)
                            ->count(),
                        'article_views' => TrackingEvent::whereBetween('occurred_at', [$todayStart, $todayEnd])
                            ->where('event_type', TrackingEvent::TYPE_ARTICLE_VIEW)
                            ->count(),
                        'cta_clicks' => TrackingEvent::whereBetween('occurred_at', [$todayStart, $todayEnd])
                            ->where('event_type', TrackingEvent::TYPE_CTA_CONTACT)->count(),
                    ],
                    'yesterday' => [
                        'visitors' => TrackingEvent::whereDate('occurred_at', $yesterday)
                            ->distinct('visitor_id')
                            ->count('visitor_id'),
                        'page_views' => TrackingEvent::whereDate('occurred_at', $yesterday)->count(),
                    ],
                    'last_7_days' => [
                        'visitors' => TrackingEvent::where('occurred_at', '>=', $lastWeek)
                            ->distinct('visitor_id')
                            ->count('visitor_id'),
                        'page_views' => TrackingEvent::where('occurred_at', '>=', $lastWeek)->count(),
                    ],
                    'last_30_days' => [
                        'visitors' => TrackingEvent::where('occurred_at', '>=', $lastMonth)
                            ->distinct('visitor_id')
                            ->count('visitor_id'),
                        'page_views' => TrackingEvent::where('occurred_at', '>=', $lastMonth)->count(),
                    ],
                ],
            ],
        ]);
    }

    public function trafficChart(Request $request): JsonResponse
    {
        $days = min($request->integer('days', 7), 90);
        $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now = Carbon::now($tz);
        $startDate = $now->copy()->startOfDay()->subDays($days - 1);
        $endDate = $now->copy()->endOfDay();

        $chartData = [];
        for ($i = 0; $i < $days; $i++) {
            $dayStart = $startDate->copy()->addDays($i)->startOfDay();
            $dayEnd = $dayStart->copy()->endOfDay();
            $date = $dayStart->format('Y-m-d');
            
            $dayStats = TrackingEvent::query()
                ->selectRaw('
                    COUNT(*) as total_events,
                    COUNT(DISTINCT visitor_id) as unique_visitors,
                    SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as product_views,
                    SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as article_views,
                    SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as cta_clicks,
                    SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) as page_views_count
                ', [
                    TrackingEvent::TYPE_PRODUCT_VIEW,
                    TrackingEvent::TYPE_ARTICLE_VIEW,
                    TrackingEvent::TYPE_CTA_CONTACT,
                    TrackingEvent::TYPE_PAGE_VIEW,
                ])
                ->whereBetween('occurred_at', [$dayStart, $dayEnd])
                ->first();
            
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

        return response()->json(['data' => $chartData]);
    }

    public function topProducts(Request $request): JsonResponse
    {
        $days = min($request->integer('days', 7), 90);
        $limit = min($request->integer('limit', 10), 50);
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
        $products = Product::with('coverImage')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $data = $topProducts->map(fn($item) => [
            'id' => $item->product_id,
            'name' => $products[$item->product_id]?->name ?? 'Sản phẩm đã xóa',
            'slug' => $products[$item->product_id]?->slug,
            'image_url' => $products[$item->product_id]?->coverImage?->url,
            'views' => $item->views,
        ]);

        return response()->json(['data' => $data]);
    }

    public function topArticles(Request $request): JsonResponse
    {
        $days = min($request->integer('days', 7), 90);
        $limit = min($request->integer('limit', 10), 50);
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
        $articles = Article::with('coverImage')
            ->whereIn('id', $articleIds)
            ->get()
            ->keyBy('id');

        $data = $topArticles->map(fn($item) => [
            'id' => $item->article_id,
            'title' => $articles[$item->article_id]?->title ?? 'Bài viết đã xóa',
            'slug' => $articles[$item->article_id]?->slug,
            'image_url' => $articles[$item->article_id]?->coverImage?->url,
            'views' => $item->views,
        ]);

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
            ->map(fn($e) => [
                'id' => $e->id,
                'event_type' => $e->event_type,
                'event_label' => match($e->event_type) {
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
}
