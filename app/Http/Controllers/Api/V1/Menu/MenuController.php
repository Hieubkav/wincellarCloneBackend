<?php

namespace App\Http\Controllers\Api\V1\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\Api\V1\Menu\MenuAssembler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function __construct(private readonly MenuAssembler $assembler) {}

    public function __invoke(): JsonResponse
    {
        $cacheVersion = (int) Cache::get('api_cache_version', 0);
        $cacheKey = "api:v1:menus:{$cacheVersion}";

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

        return response()->json([
            'data' => $payload,
            'meta' => [
                'cache_version' => $cacheVersion,
            ],
        ]);
    }
}
