<?php

namespace App\Http\Controllers\Api\V1\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\Api\V1\Menu\MenuAssembler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function __construct(private readonly MenuAssembler $assembler) {}

    public function __invoke(Request $request): JsonResponse
    {
        $startedAt = hrtime(true);
        $cacheVersion = (int) Cache::get('api_cache_version', 0);
        $cacheKey = "api:v1:menus:{$cacheVersion}";

        $cacheHit = Cache::has($cacheKey);
        $payload = Cache::remember($cacheKey, 600, function () {
            $menus = Menu::query()
                ->with([
                    'blocks' => function ($query) {
                        $query->active()->orderBy('order');
                    },
                    'blocks.items' => function ($query) {
                        $query->active()->orderBy('order');
                    },
                ])
                ->active()
                ->orderBy('order')
                ->get();

            return $this->assembler->build($menus);
        });

        $meta = [
            'cache_version' => $cacheVersion,
        ];

        if ($request->boolean('audit')) {
            $meta['audit'] = [
                'cache_hit' => $cacheHit,
                'cache_key' => $cacheKey,
                'server_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
            ];
        }

        return response()->json([
            'data' => $payload,
            'meta' => $meta,
        ]);
    }
}
