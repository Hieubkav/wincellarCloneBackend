<?php

use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $setting = Setting::query()->first();
        if (! $setting) {
            $setting = Setting::create([]);
        }

        $contactConfig = is_array($setting->contact_config) ? $setting->contact_config : [];
        if (! empty($contactConfig['social_links'])) {
            return;
        }

        $links = SocialLink::query()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($links->isEmpty()) {
            return;
        }

        $contactConfig['social_links'] = $links->map(function (SocialLink $link) {
            return [
                'id' => 'social-'.$link->id,
                'platform' => $link->platform,
                'url' => $link->url,
                'icon_url' => $link->icon_url,
                'order' => $link->order ?? 0,
                'active' => (bool) $link->active,
            ];
        })->values()->all();

        $setting->contact_config = $contactConfig;
        $setting->save();
    }

    public function down(): void
    {
        $setting = Setting::query()->first();
        if (! $setting) {
            return;
        }

        $contactConfig = is_array($setting->contact_config) ? $setting->contact_config : [];
        if (! array_key_exists('social_links', $contactConfig)) {
            return;
        }

        unset($contactConfig['social_links']);
        $setting->contact_config = $contactConfig === [] ? null : $contactConfig;
        $setting->save();
    }
};
