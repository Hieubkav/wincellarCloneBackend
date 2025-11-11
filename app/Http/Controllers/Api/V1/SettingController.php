<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Get application settings for frontend
     */
    public function __invoke(): JsonResource
    {
        // Cache settings for 1 hour since they rarely change
        // Cache will be invalidated by SettingObserver when admin updates
        $setting = Cache::remember(
            'api:v1:settings',
            3600,
            fn() => Setting::query()
                ->with(['logoImage', 'faviconImage'])
                ->first()
        );

        // If no settings exist, return default values
        if (!$setting) {
            $setting = new Setting([
                'site_name' => config('app.name'),
                'hotline' => '',
                'address' => '',
                'hours' => '',
                'email' => '',
            ]);
        }

        return new SettingResource($setting);
    }
}
