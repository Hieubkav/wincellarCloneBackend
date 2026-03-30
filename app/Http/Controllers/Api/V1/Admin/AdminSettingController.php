<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use App\Models\Image;
use App\Models\Setting;
use App\Services\Media\MediaCanonicalService;
use App\Support\Cache\ApiCacheVersionManager;
use App\Support\Catalog\AttributeIconResolver;
use App\Support\Media\MediaSemanticRegistry;
use App\Support\Settings\FontRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function lite(Request $request): JsonResponse
    {
        $controllerStart = microtime(true);
        $queryStart = microtime(true);
        $setting = Setting::query()->select(['id', 'product_shopee_link_enabled', 'updated_at'])->first();
        $queryMs = (microtime(true) - $queryStart) * 1000;

        if (! $setting) {
            $setting = Setting::create([]);
        }

        $transformStart = microtime(true);
        $payload = [
            'id' => $setting->id,
            'product_shopee_link_enabled' => (bool) ($setting->product_shopee_link_enabled ?? false),
            'updated_at' => $setting->updated_at?->toIso8601String(),
        ];
        $transformMs = (microtime(true) - $transformStart) * 1000;

        return SuccessResponse::make($payload, null, 200, $this->buildAuditMeta(
            $request,
            $controllerStart,
            $queryMs,
            $transformMs
        ));
    }

    public function show(Request $request): JsonResponse
    {
        $controllerStart = microtime(true);
        $queryStart = microtime(true);
        $setting = Setting::with(['logoImage', 'faviconImage', 'ogImage', 'productWatermarkImage'])->first();
        $queryMs = (microtime(true) - $queryStart) * 1000;

        if (! $setting) {
            $setting = Setting::create([]);
        }

        $contactConfig = $this->normalizeContactConfig($setting->contact_config);
        $productContactCtaConfig = $this->normalizeProductContactCtaConfig($setting->product_contact_cta_config);

        $transformStart = microtime(true);
        $payload = [
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
            'product_shopee_link_enabled' => (bool) ($setting->product_shopee_link_enabled ?? false),
            'product_mobile_main_image_height' => $setting->product_mobile_main_image_height,
            'product_detail_rules' => $setting->product_detail_rules,
            'product_detail_faq_enabled' => (bool) ($setting->product_detail_faq_enabled ?? true),
            'product_detail_faq_title' => $setting->product_detail_faq_title,
            'product_detail_faq_eyebrow' => $setting->product_detail_faq_eyebrow,
            'product_detail_faq_items' => $setting->product_detail_faq_items,
            'product_detail_faq_position' => $setting->product_detail_faq_position,
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
            'logo_image_canonical_url' => $setting->logoImage?->canonical_url,
            'favicon_image_id' => $setting->favicon_image_id,
            'favicon_image_url' => $setting->faviconImage?->url,
            'favicon_image_canonical_url' => $setting->faviconImage?->canonical_url,
            'og_image_id' => $setting->og_image_id,
            'og_image_url' => $setting->ogImage?->url,
            'og_image_canonical_url' => $setting->ogImage?->canonical_url,
            'product_watermark_image_id' => $setting->product_watermark_image_id,
            'product_watermark_image_url' => $setting->productWatermarkImage?->url,
            'product_watermark_image_canonical_url' => $setting->productWatermarkImage?->canonical_url,
            'product_watermark_type' => $setting->product_watermark_type ?? 'image',
            'product_watermark_position' => $setting->product_watermark_position,
            'product_watermark_size' => $setting->product_watermark_size,
            'product_watermark_text' => $setting->product_watermark_text,
            'product_watermark_text_size' => $setting->product_watermark_text_size ?? 'medium',
            'product_watermark_text_position' => $setting->product_watermark_text_position ?? 'center',
            'product_watermark_text_position_y' => $setting->product_watermark_text_position_y,
            'product_watermark_text_opacity' => $setting->product_watermark_text_opacity ?? 50,
            'product_watermark_text_repeat' => (bool) ($setting->product_watermark_text_repeat ?? false),
            'global_font_key' => $setting->global_font_key,
            'home_font_key' => $setting->home_font_key,
            'product_list_font_key' => $setting->product_list_font_key,
            'product_detail_font_key' => $setting->product_detail_font_key,
            'article_list_font_key' => $setting->article_list_font_key,
            'article_detail_font_key' => $setting->article_detail_font_key,
            'updated_at' => $setting->updated_at?->toIso8601String(),
        ];
        $transformMs = (microtime(true) - $transformStart) * 1000;

        return SuccessResponse::make($payload, null, 200, $this->buildAuditMeta(
            $request,
            $controllerStart,
            $queryMs,
            $transformMs
        ));
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
            'contact_config.social_links' => ['nullable', 'array'],
            'contact_config.social_links.*.id' => ['nullable', 'string', 'max:100'],
            'contact_config.social_links.*.platform' => ['nullable', 'string', 'max:100'],
            'contact_config.social_links.*.url' => ['nullable', 'string', 'max:500'],
            'contact_config.social_links.*.icon_url' => ['nullable', 'string', 'max:500'],
            'contact_config.social_links.*.order' => ['nullable', 'integer', 'min:0'],
            'contact_config.social_links.*.active' => ['nullable', 'boolean'],
            'product_contact_cta_config' => ['nullable', 'array'],
            'product_contact_cta_config.mode' => ['nullable', 'string', 'in:contact_page,social_4_buttons'],
            'product_contact_cta_config.items' => ['nullable', 'array'],
            'product_contact_cta_config.items.facebook' => ['nullable', 'string', 'max:255'],
            'product_contact_cta_config.items.zalo' => ['nullable', 'string', 'max:255'],
            'product_contact_cta_config.items.phone' => ['nullable', 'string', 'max:255'],
            'product_contact_cta_config.items.tiktok' => ['nullable', 'string', 'max:255'],
            'product_shopee_link_enabled' => ['nullable', 'boolean'],
            'product_mobile_main_image_height' => ['nullable', 'integer', 'min:240', 'max:520'],
            'product_detail_rules' => ['nullable', 'array'],
            'product_detail_rules.*' => ['nullable', 'string', 'max:255'],
            'product_detail_faq_enabled' => ['nullable', 'boolean'],
            'product_detail_faq_title' => ['nullable', 'string', 'max:255'],
            'product_detail_faq_eyebrow' => ['nullable', 'string', 'max:255'],
            'product_detail_faq_position' => ['nullable', 'string', 'in:after_description,after_same_type,after_related_products'],
            'product_detail_faq_items' => ['nullable', 'array'],
            'product_detail_faq_items.*.question' => ['nullable', 'string', 'max:500'],
            'product_detail_faq_items.*.answer' => ['nullable', 'string'],
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
            'product_watermark_text_position_y' => ['nullable', 'integer', 'min:0', 'max:100'],
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

        if (array_key_exists('product_detail_rules', $validated)) {
            $validated['product_detail_rules'] = $this->normalizeStringArray(
                $validated['product_detail_rules']
            );
        }

        if (array_key_exists('product_detail_faq_title', $validated)) {
            $validated['product_detail_faq_title'] = $this->normalizeStringValue($validated['product_detail_faq_title']);
        }

        if (array_key_exists('product_detail_faq_eyebrow', $validated)) {
            $validated['product_detail_faq_eyebrow'] = $this->normalizeStringValue($validated['product_detail_faq_eyebrow']);
        }

        if (array_key_exists('product_detail_faq_items', $validated)) {
            $validated['product_detail_faq_items'] = $this->normalizeFaqItems(
                $validated['product_detail_faq_items']
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
            'product_watermark_text_position_y',
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
        $this->syncSettingImageSemantics($setting, $validated);

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

        if (isset($contactConfig['cards']) && is_array($contactConfig['cards'])) {
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
        }

        if (array_key_exists('social_links', $contactConfig)) {
            $contactConfig['social_links'] = $this->normalizeContactSocialLinks($contactConfig['social_links']);
        }

        return $contactConfig;
    }

    private function normalizeContactSocialLinks(mixed $links): array
    {
        if (! is_array($links)) {
            return [];
        }

        $normalized = [];
        $index = 0;
        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }

            $platform = $this->normalizeStringValue($link['platform'] ?? null);
            $url = $this->normalizeStringValue($link['url'] ?? null);
            if (! $platform || ! $url) {
                continue;
            }

            $id = $this->normalizeStringValue($link['id'] ?? null) ?? 'social-'.$index;
            $order = is_numeric($link['order'] ?? null) ? (int) $link['order'] : $index;
            $active = array_key_exists('active', $link) ? (bool) $link['active'] : true;
            $iconUrl = $this->normalizeStringValue($link['icon_url'] ?? null);

            $normalized[] = [
                'id' => $id,
                'platform' => $platform,
                'url' => $url,
                'icon_url' => $iconUrl,
                'order' => $order,
                'active' => $active,
            ];

            $index++;
        }

        usort($normalized, fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        return array_values($normalized);
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

    private function normalizeStringArray(mixed $values): ?array
    {
        if (! is_array($values)) {
            return null;
        }

        $normalized = [];
        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            if ($trimmed === '') {
                continue;
            }

            $normalized[] = $trimmed;
        }

        return $normalized === [] ? null : array_values($normalized);
    }

    private function normalizeFaqItems(mixed $items): ?array
    {
        if (! is_array($items)) {
            return null;
        }

        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = $this->normalizeStringValue($item['question'] ?? null);
            $answer = $this->normalizeStringValue($item['answer'] ?? null);

            if (! $question || ! $answer) {
                continue;
            }

            $normalized[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $normalized === [] ? null : array_values($normalized);
    }

    private function buildAuditMeta(Request $request, float $controllerStart, float $queryMs, float $transformMs): ?array
    {
        if (! $request->boolean('audit')) {
            return null;
        }

        $audit = $request->attributes->get('audit', []);
        $audit['query_ms'] = (int) round($queryMs);
        $audit['transform_ms'] = (int) round($transformMs);
        $audit['controller_ms'] = (int) round((microtime(true) - $controllerStart) * 1000);

        return ['audit' => $audit];
    }

    private function syncSettingImageSemantics(Setting $setting, array $validated): void
    {
        $fields = [
            'logo_image_id',
            'favicon_image_id',
            'og_image_id',
            'product_watermark_image_id',
        ];

        foreach ($fields as $field) {
            if (! array_key_exists($field, $validated)) {
                continue;
            }

            $imageId = $validated[$field] ?? null;
            if (! $imageId) {
                continue;
            }

            $semantic = MediaSemanticRegistry::fromSettingField($field);
            if (! $semantic) {
                continue;
            }

            $image = Image::find($imageId);
            if (! $image) {
                continue;
            }

            app(MediaCanonicalService::class)->ensureMetadata($image, $semantic, $setting->site_name ?? $semantic);
            $image->saveQuietly();
        }
    }
}
