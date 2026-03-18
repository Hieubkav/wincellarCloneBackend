<?php

namespace App\Http\Controllers\Api\V1\Home;

use App\Enums\HomeComponentType;
use App\Http\Controllers\Controller;
use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentAssembler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(private readonly HomeComponentAssembler $assembler) {}

    public function __invoke(Request $request): JsonResponse
    {
        $startedAt = hrtime(true);
        $cacheVersion = (int) Cache::get('api_cache_version', 0);

        if ($request->boolean('audit')) {
            $components = HomeComponent::query()
                ->active()
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            $result = $this->assembler->buildWithAudit($components);
            $result['audit']['server_ms'] = round((hrtime(true) - $startedAt) / 1_000_000, 2);

            return response()->json([
                'data' => $result['payload'],
                'meta' => [
                    'cache_version' => $cacheVersion,
                    'audit' => $result['audit'],
                ],
            ]);
        }

        $cacheKey = "api:v1:home:{$cacheVersion}";
        $cacheHit = Cache::has($cacheKey);

        $payload = Cache::remember($cacheKey, 600, function () {
            $components = HomeComponent::query()
                ->active()
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            return $this->assembler->build($components);
        });

        $meta = [
            'cache_version' => $cacheVersion,
        ];

        if ($request->boolean('audit_cached')) {
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

    public function speedDial(Request $request): JsonResponse
    {
        $startedAt = hrtime(true);
        $cacheVersion = (int) Cache::get('api_cache_version', 0);
        $cacheKey = "api:v1:home:speed-dial:{$cacheVersion}";
        $cacheHit = Cache::has($cacheKey);

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
