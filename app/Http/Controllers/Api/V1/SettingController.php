<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Get application settings for frontend
     */
    public function __invoke(): JsonResponse
    {
        $payload = Cache::remember('api:v1:settings:payload', 3600, function () {
            $setting = Setting::query()
                ->with(['logoImage', 'faviconImage', 'ogImage', 'productWatermarkImage'])
                ->first();

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

            return (new SettingResource($setting))->response()->getData(true);
        });

        return response()->json($payload);
    }
}
