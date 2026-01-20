 <?php
 
 namespace App\Http\Controllers\Api\V1\Admin;
 
 use App\Http\Controllers\Controller;
 use App\Models\Product;
 use App\Models\Article;
 use App\Models\ProductCategory;
 use App\Models\ProductType;
 use App\Models\TrackingEvent;
 use App\Models\Visitor;
 use App\Models\VisitorSession;
 use Illuminate\Http\JsonResponse;
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\DB;
 use Carbon\Carbon;
 
 class AdminDashboardController extends Controller
 {
     public function stats(): JsonResponse
     {
         $today = Carbon::today();
         $yesterday = Carbon::yesterday();
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
                         'visitors' => Visitor::whereDate('first_seen_at', $today)->count(),
                         'sessions' => VisitorSession::whereDate('started_at', $today)->count(),
                         'page_views' => TrackingEvent::whereDate('occurred_at', $today)->count(),
                         'product_views' => TrackingEvent::whereDate('occurred_at', $today)
                             ->where('event_type', TrackingEvent::TYPE_PRODUCT_VIEW)->count(),
                         'article_views' => TrackingEvent::whereDate('occurred_at', $today)
                             ->where('event_type', TrackingEvent::TYPE_ARTICLE_VIEW)->count(),
                         'cta_clicks' => TrackingEvent::whereDate('occurred_at', $today)
                             ->where('event_type', TrackingEvent::TYPE_CTA_CONTACT)->count(),
                     ],
                     'yesterday' => [
                         'visitors' => Visitor::whereDate('first_seen_at', $yesterday)->count(),
                         'page_views' => TrackingEvent::whereDate('occurred_at', $yesterday)->count(),
                     ],
                     'last_7_days' => [
                         'visitors' => Visitor::where('first_seen_at', '>=', $lastWeek)->count(),
                         'page_views' => TrackingEvent::where('occurred_at', '>=', $lastWeek)->count(),
                     ],
                     'last_30_days' => [
                         'visitors' => Visitor::where('first_seen_at', '>=', $lastMonth)->count(),
                         'page_views' => TrackingEvent::where('occurred_at', '>=', $lastMonth)->count(),
                     ],
                 ],
             ],
         ]);
     }
 
     public function trafficChart(Request $request): JsonResponse
     {
         $days = min($request->integer('days', 7), 90);
         $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
 
         $dailyData = TrackingEvent::query()
             ->select(
                 DB::raw('DATE(occurred_at) as date'),
                 DB::raw('COUNT(*) as total_events'),
                 DB::raw("SUM(CASE WHEN event_type = 'product_view' THEN 1 ELSE 0 END) as product_views"),
                 DB::raw("SUM(CASE WHEN event_type = 'article_view' THEN 1 ELSE 0 END) as article_views"),
                 DB::raw("SUM(CASE WHEN event_type = 'cta_contact' THEN 1 ELSE 0 END) as cta_clicks")
             )
             ->where('occurred_at', '>=', $startDate)
             ->groupBy(DB::raw('DATE(occurred_at)'))
             ->orderBy('date')
             ->get();
 
         $visitorData = Visitor::query()
             ->select(
                 DB::raw('DATE(first_seen_at) as date'),
                 DB::raw('COUNT(*) as visitors')
             )
             ->where('first_seen_at', '>=', $startDate)
             ->groupBy(DB::raw('DATE(first_seen_at)'))
             ->pluck('visitors', 'date');
 
         $chartData = [];
         for ($i = 0; $i < $days; $i++) {
             $date = Carbon::now()->subDays($days - 1 - $i)->format('Y-m-d');
             $dayData = $dailyData->firstWhere('date', $date);
             
             $chartData[] = [
                 'date' => $date,
                 'label' => Carbon::parse($date)->format('d/m'),
                 'visitors' => $visitorData[$date] ?? 0,
                 'page_views' => $dayData?->total_events ?? 0,
                 'product_views' => $dayData?->product_views ?? 0,
                 'article_views' => $dayData?->article_views ?? 0,
                 'cta_clicks' => $dayData?->cta_clicks ?? 0,
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
