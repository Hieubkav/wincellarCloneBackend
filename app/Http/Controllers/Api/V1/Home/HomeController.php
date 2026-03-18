<?php

namespace App\Http\Controllers\Api\V1\Home;

use App\Enums\HomeComponentType;
use App\Http\Controllers\Controller;
use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentAssembler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(private readonly HomeComponentAssembler $assembler) {}

    public function __invoke(): JsonResponse
    {
        $cacheVersion = (int) Cache::get('api_cache_version', 0);
        $cacheKey = "api:v1:home:{$cacheVersion}";

        $payload = Cache::remember($cacheKey, 600, function () {
            $components = HomeComponent::query()
                ->active()
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            return $this->assembler->build($components);
        });

        return response()->json([
            'data' => $payload,
            'meta' => [
                'cache_version' => $cacheVersion,
            ],
        ]);
    }

    public function speedDial(): JsonResponse
    {
        $cacheVersion = (int) Cache::get('api_cache_version', 0);
        $cacheKey = "api:v1:home:speed-dial:{$cacheVersion}";

        $payload = Cache::remember($cacheKey, 600, function () {
            $component = HomeComponent::query()
                ->active()
                ->where('type', HomeComponentType::SpeedDial->value)
                ->orderBy('order')
                ->orderBy('id')
                ->first();

            if (! $component) {
                return null;
            }

            return $this->assembler->buildSingle($component);
        });

        return response()->json([
            'data' => $payload,
            'meta' => [
                'cache_version' => $cacheVersion,
            ],
        ]);
    }
}
