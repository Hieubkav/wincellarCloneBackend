<?php

namespace App\Http\Controllers\Api\V1\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\Api\V1\Menu\MenuAssembler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function __construct(private readonly MenuAssembler $assembler)
    {
    }

    public function __invoke(): JsonResponse
    {
        $menus = Menu::query()
            ->with([
                'blocks' => function ($query) {
                    $query->active()->orderBy('order');
                },
                'blocks.items' => function ($query) {
                    $query->active()->orderBy('order');
                },
                'blocks.items.term.group',
                'term'
            ])
            ->active()
            ->orderBy('order')
            ->get();

        $payload = $this->assembler->build($menus);
        
        // Include cache version for frontend cache invalidation
        $cacheVersion = (int) Cache::get('api_cache_version', 0);

        return response()->json([
            'data' => $payload,
            'meta' => [
                'cache_version' => $cacheVersion,
            ],
        ]);
    }
}
