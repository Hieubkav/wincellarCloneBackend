<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\YourModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Controller Template with Cache Version
 * 
 * USAGE:
 * 1. Copy this template
 * 2. Replace "YourModel" with your actual model
 * 3. Implement your query logic
 * 4. Always include cache_version in meta
 */
class YourController extends Controller
{
    public function index(): JsonResponse
    {
        // Your query logic
        $data = YourModel::query()
            ->active()
            ->orderBy('order')
            ->get();

        // Transform data (optional)
        $payload = $this->transform($data);
        
        // CRITICAL: Include cache version in response
        $cacheVersion = (int) Cache::get('api_cache_version', 0);

        return response()->json([
            'data' => $payload,
            'meta' => [
                'cache_version' => $cacheVersion,
                'updated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $item = YourModel::findOrFail($id);
        
        $cacheVersion = (int) Cache::get('api_cache_version', 0);

        return response()->json([
            'data' => $this->transformSingle($item),
            'meta' => [
                'cache_version' => $cacheVersion,
            ],
        ]);
    }

    /**
     * Transform collection
     */
    private function transform($collection): array
    {
        return $collection->map(fn($item) => $this->transformSingle($item))->toArray();
    }

    /**
     * Transform single item
     */
    private function transformSingle($item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            // Add more fields...
        ];
    }
}
