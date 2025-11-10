<?php

namespace App\Http\Controllers\Api\V1\Home;

use App\Http\Controllers\Controller;
use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentAssembler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(private readonly HomeComponentAssembler $assembler)
    {
    }

    public function __invoke(): JsonResponse
    {
        $components = HomeComponent::query()
            ->active()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $payload = $this->assembler->build($components);
        
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
