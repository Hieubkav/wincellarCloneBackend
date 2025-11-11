<?php

namespace App\Http\Controllers\Api\V1\SocialLink;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SocialLinkController extends Controller
{
    /**
     * Get active social links for frontend
     * 
     * Returns list of social media links with icon URLs
     * Cache: 5 minutes (invalidated by SocialLinkObserver)
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $socialLinks = Cache::remember(
            'api:v1:social-links',
            300, // 5 minutes
            fn() => SocialLink::query()
                ->active()
                ->with('iconImage')
                ->orderBy('order', 'asc')
                ->get()
                ->map(fn(SocialLink $link) => [
                    'id' => $link->id,
                    'platform' => $link->platform,
                    'url' => $link->url,
                    'icon_url' => $link->icon_url,
                    'order' => $link->order,
                ])
        );

        return response()->json([
            'data' => $socialLinks,
            'meta' => [
                'api_version' => 'v1',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
