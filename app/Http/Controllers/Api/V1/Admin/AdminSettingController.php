<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use App\Models\Setting;
use App\Support\Cache\ApiCacheVersionManager;
use App\Support\Catalog\AttributeIconResolver;
use App\Support\Settings\FontRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $setting = Setting::with(['logoImage', 'faviconImage', 'ogImage', 'productWatermarkImage'])->first();

        if (! $setting) {
            $setting = Setting::create([]);
        }

        $contactConfig = $this->normalizeContactConfig($setting->contact_config);
        $productContactCtaConfig = $this->normalizeProductContactCtaConfig($setting->product_contact_cta_config);

        return SuccessResponse::make([
            'id' => $setting->id,
            'site_name' => $setting->site_name,
            'hotline' => $setting->hotline,
            'email' => $setting->email,
            'address' => $setting->address,
            'hours' => $setting->hours,
            'google_map_embed' => $setting->extra['google_map_embed'] ?? null,
            'footer_config' => $setting->footer_config,
            'contact_config' => $contactConfig,
            'product_contact_cta_config' => $productContactCtaConfig,
            'meta_default_title' => $setting->meta_default_title,
            'meta_default_description' => $setting->meta_default_description,
            'meta_default_keywords' => $setting->meta_default_keywords,
            'site_tagline' => $setting->site_tagline,
            'organization_legal_name' => $setting->organization_legal_name,
            'organization_short_name' => $setting->organization_short_name,
            'primary_phone' => $setting->primary_phone,
            'primary_email' => $setting->primary_email,
            'price_range' => $setting->price_range,
            'social_links_schema' => $setting->social_links_schema,
            'default_meta_title_template' => $setting->default_meta_title_template,
            'default_og_title' => $setting->default_og_title,
            'default_og_description' => $setting->default_og_description,
            'indexing_enabled' => $setting->indexing_enabled,
            'logo_image_id' => $setting->logo_image_id,
            'logo_image_url' => $setting->logoImage?->url,
            'favicon_image_id' => $setting->favicon_image_id,
            'favicon_image_url' => $setting->faviconImage?->url,
            'og_image_id' => $setting->og_image_id,
            'og_image_url' => $setting->ogImage?->url,
            'product_watermark_image_id' => $setting->product_watermark_image_id,
            'product_watermark_image_url' => $setting->productWatermarkImage?->url,
            'product_watermark_type' => $setting->product_watermark_type ?? 'image',
            'product_watermark_position' => $setting->product_watermark_position,
            'product_watermark_size' => $setting->product_watermark_size,
            'product_watermark_text' => $setting->product_watermark_text,
            'product_watermark_text_size' => $setting->product_watermark_text_size ?? 'medium',
            'product_watermark_text_position' => $setting->product_watermark_text_position ?? 'center',
            'product_watermark_text_opacity' => $setting->product_watermark_text_opacity ?? 50,
            'product_watermark_text_repeat' => (bool) ($setting->product_watermark_text_repeat ?? false),
            'global_font_key' => $setting->global_font_key,
            'home_font_key' => $setting->home_font_key,
            'product_list_font_key' => $setting->product_list_font_key,
            'product_detail_font_key' => $setting->product_detail_font_key,
            'article_list_font_key' => $setting->article_list_font_key,
            'article_detail_font_key' => $setting->article_detail_font_key,
            'updated_at' => $setting->updated_at?->toIso8601String(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $setting = Setting::first();

        if (! $setting) {
            $setting = Setting::create([]);
        }

        $validated = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'hotline' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'hours' => ['nullable', 'string', 'max:255'],
            'google_map_embed' => ['nullable', 'string'],
            'footer_config' => ['nullable', 'array'],
            'contact_config' => ['nullable', 'array'],
            'product_contact_cta_config' => ['nullable', 'array'],
            'product_contact_cta_config.mode' => ['nullable', 'string', 'in:contact_page,social_4_buttons'],
            'product_contact_cta_config.items' => ['nullable', 'array'],
            'product_contact_cta_config.items.facebook' => ['nullable', 'string', 'max:255'],
            'product_contact_cta_config.items.zalo' => ['nullable', 'string', 'max:255'],
            'product_contact_cta_config.items.phone' => ['nullable', 'string', 'max:255'],
            'product_contact_cta_config.items.tiktok' => ['nullable', 'string', 'max:255'],
            'meta_default_title' => ['nullable', 'string', 'max:255'],
            'meta_default_description' => ['nullable', 'string', 'max:500'],
            'meta_default_keywords' => ['nullable', 'string', 'max:500'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'organization_legal_name' => ['nullable', 'string', 'max:255'],
            'organization_short_name' => ['nullable', 'string', 'max:255'],
            'primary_phone' => ['nullable', 'string', 'max:255'],
            'primary_email' => ['nullable', 'email', 'max:255'],
            'price_range' => ['nullable', 'string', 'max:50'],
            'social_links_schema' => ['nullable', 'array'],
            'social_links_schema.*' => ['string', 'max:255'],
            'default_meta_title_template' => ['nullable', 'string', 'max:255'],
            'default_og_title' => ['nullable', 'string', 'max:255'],
            'default_og_description' => ['nullable', 'string', 'max:500'],
            'indexing_enabled' => ['nullable', 'boolean'],
            'logo_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'favicon_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'og_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'product_watermark_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'product_watermark_type' => ['nullable', 'string', 'in:image,text'],
            'product_watermark_position' => ['nullable', 'string', 'in:none,top_left,top_right,bottom_left,bottom_right'],
            'product_watermark_size' => ['nullable', 'string', 'in:64x64,96x96,128x128,160x160,192x192'],
            'product_watermark_text' => ['nullable', 'string', 'max:100'],
            'product_watermark_text_size' => ['nullable', 'string', 'in:xxsmall,xsmall,small,medium,large,xlarge,xxlarge'],
            'product_watermark_text_position' => ['nullable', 'string', 'in:top,center,bottom'],
            'product_watermark_text_opacity' => ['nullable', 'integer', 'min:5', 'max:100'],
            'product_watermark_text_repeat' => ['nullable', 'boolean'],
            'global_font_key' => ['nullable', 'string', 'in:'.implode(',', FontRegistry::all())],
            'home_font_key' => ['nullable', 'string', 'in:'.implode(',', FontRegistry::all())],
            'product_list_font_key' => ['nullable', 'string', 'in:'.implode(',', FontRegistry::all())],
            'product_detail_font_key' => ['nullable', 'string', 'in:'.implode(',', FontRegistry::all())],
            'article_list_font_key' => ['nullable', 'string', 'in:'.implode(',', FontRegistry::all())],
            'article_detail_font_key' => ['nullable', 'string', 'in:'.implode(',', FontRegistry::all())],
        ]);

        $fontFields = [
            'global_font_key',
            'home_font_key',
            'product_list_font_key',
            'product_detail_font_key',
            'article_list_font_key',
            'article_detail_font_key',
        ];

        foreach ($fontFields as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        if (array_key_exists('contact_config', $validated)) {
            $validated['contact_config'] = $this->normalizeContactConfig($validated['contact_config']);
        }

        if (array_key_exists('product_contact_cta_config', $validated)) {
            $validated['product_contact_cta_config'] = $this->normalizeProductContactCtaConfig(
                $validated['product_contact_cta_config']
            );
        }

        if (isset($validated['google_map_embed'])) {
            $extra = $setting->extra ?? [];
            $extra['google_map_embed'] = $validated['google_map_embed'];
            $validated['extra'] = $extra;
            unset($validated['google_map_embed']);
        }

        // Check if watermark settings changed - need to clear image proxy cache
        $watermarkFields = [
            'product_watermark_type',
            'product_watermark_image_id',
            'product_watermark_position',
            'product_watermark_size',
            'product_watermark_text',
            'product_watermark_text_size',
            'product_watermark_text_position',
            'product_watermark_text_opacity',
            'product_watermark_text_repeat',
        ];

        $watermarkChanged = false;
        foreach ($watermarkFields as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== $setting->$field) {
                $watermarkChanged = true;
                break;
            }
        }

        $setting->update($validated);

        if ($watermarkChanged) {
            $this->clearImageProxyCache();
        }

        return SuccessResponse::make(
            [
                'updated_at' => $setting->fresh()?->updated_at?->toIso8601String(),
                'watermark_changed' => $watermarkChanged,
            ],
            'Cập nhật cấu hình thành công'
        );
    }

    private function clearImageProxyCache(): void
    {
        $version = ApiCacheVersionManager::bumpImageProxyVersion();

        \Log::info('Image proxy cache version bumped due to watermark settings change', [
            'image_proxy_cache_version' => $version,
        ]);
    }

    private function normalizeContactConfig(?array $contactConfig): ?array
    {
        if (! is_array($contactConfig)) {
            return $contactConfig;
        }

        if (! isset($contactConfig['cards']) || ! is_array($contactConfig['cards'])) {
            return $contactConfig;
        }

        $contactConfig['cards'] = array_map(function ($card) {
            if (! is_array($card)) {
                return $card;
            }

            $icon = $card['icon'] ?? null;
            if (is_string($icon) && $icon !== '') {
                if (! str_starts_with($icon, 'http://') && ! str_starts_with($icon, 'https://') && ! str_starts_with($icon, '/')) {
                    $card['icon'] = AttributeIconResolver::normalizeIconName($icon);
                }
            }

            return $card;
        }, $contactConfig['cards']);

        return $contactConfig;
    }

    private function normalizeProductContactCtaConfig(?array $config): ?array
    {
        if (! is_array($config)) {
            return $config;
        }

        $mode = $config['mode'] ?? 'contact_page';
        $allowedModes = ['contact_page', 'social_4_buttons'];
        if (! in_array($mode, $allowedModes, true)) {
            $mode = 'contact_page';
        }

        $items = is_array($config['items'] ?? null) ? $config['items'] : [];

        $normalizedItems = [
            'facebook' => $this->normalizeStringValue($items['facebook'] ?? null),
            'zalo' => $this->normalizeStringValue($items['zalo'] ?? null),
            'phone' => $this->normalizeStringValue($items['phone'] ?? null),
            'tiktok' => $this->normalizeStringValue($items['tiktok'] ?? null),
        ];

        return [
            'mode' => $mode,
            'items' => $normalizedItems,
        ];
    }

    private function normalizeStringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
