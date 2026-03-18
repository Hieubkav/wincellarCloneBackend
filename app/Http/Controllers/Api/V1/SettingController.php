<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Get application settings for frontend
     */
    public function __invoke(Request $request): JsonResponse
    {
        $cacheKey = 'api:v1:settings:payload';

        if ($request->boolean('audit')) {
            $cacheHit = Cache::has($cacheKey);
            $cacheReadStartedAt = hrtime(true);
            $cachedPayload = Cache::get($cacheKey);
            $cacheReadMs = round((hrtime(true) - $cacheReadStartedAt) / 1_000_000, 2);

            $queryMs = null;
            $serializeMs = null;
            $payload = $cachedPayload;

            if (! is_array($payload)) {
                $buildResult = $this->buildPayloadWithTimings();
                $payload = $buildResult['payload'];
                $queryMs = $buildResult['query_ms'];
                $serializeMs = $buildResult['serialize_ms'];

                Cache::put($cacheKey, $payload, 3600);
            }

            if (! is_array($payload)) {
                $payload = [];
            }

            $payload['meta']['audit'] = [
                'cache_driver' => config('cache.default'),
                'cache_key' => $cacheKey,
                'cache_hit' => $cacheHit && is_array($cachedPayload),
                'cache_read_ms' => $cacheReadMs,
                'query_ms' => $queryMs,
                'serialize_ms' => $serializeMs,
            ];

            return response()->json($payload);
        }

        $payload = Cache::remember($cacheKey, 3600, fn () => $this->buildPayload());

        return response()->json($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(): array
    {
        return $this->buildPayloadWithTimings()['payload'];
    }

    /**
     * @return array{payload: array<string, mixed>, query_ms: float, serialize_ms: float}
     */
    private function buildPayloadWithTimings(): array
    {
        $queryStartedAt = hrtime(true);
        $setting = Setting::query()
            ->with(['logoImage', 'faviconImage', 'ogImage', 'productWatermarkImage'])
            ->first();
        $queryMs = round((hrtime(true) - $queryStartedAt) / 1_000_000, 2);

        if (! $setting) {
            $setting = new Setting([
                'site_name' => config('app.name'),
                'hotline' => '',
                'address' => '',
                'hours' => '',
                'email' => '',
                'product_watermark_position' => 'none',
                'product_watermark_size' => '128x128',
            ]);
        }

        $serializeStartedAt = hrtime(true);
        $payload = (new SettingResource($setting))->response()->getData(true);
        $serializeMs = round((hrtime(true) - $serializeStartedAt) / 1_000_000, 2);

        return [
            'payload' => $payload,
            'query_ms' => $queryMs,
            'serialize_ms' => $serializeMs,
        ];
    }
}
