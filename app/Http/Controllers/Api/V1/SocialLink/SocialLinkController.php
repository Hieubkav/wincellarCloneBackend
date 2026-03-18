<?php

namespace App\Http\Controllers\Api\V1\SocialLink;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SocialLinkController extends Controller
{
    /**
     * Get active social links for frontend
     *
     * Returns list of social media links with icon URLs
     * Cache: 5 minutes (invalidated by SocialLinkObserver)
     */
    public function index(Request $request): JsonResponse
    {
        $startedAt = hrtime(true);
        $cacheKey = 'api:v1:social-links';
        $cacheHit = Cache::has($cacheKey);

        $socialLinks = Cache::remember(
            $cacheKey,
            300,
            fn () => SocialLink::query()
                ->active()
                ->with('iconImage')
                ->orderBy('order', 'asc')
                ->get()
                ->map(fn (SocialLink $link) => [
                    'id' => $link->id,
                    'platform' => $link->platform,
                    'url' => $link->url,
                    'icon_url' => $link->icon_url,
                    'order' => $link->order,
                ])
        );

        $meta = [
            'api_version' => 'v1',
            'timestamp' => now()->toIso8601String(),
        ];

        if ($request->boolean('audit')) {
            $meta['audit'] = [
                'cache_hit' => $cacheHit,
                'cache_key' => $cacheKey,
                'server_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
            ];
        }

        return response()->json([
            'data' => $socialLinks,
            'meta' => $meta,
        ]);
    }
}
