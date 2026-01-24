<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $setting = Setting::with(['logoImage', 'faviconImage', 'productWatermarkImage'])->first();

        if (!$setting) {
            $setting = Setting::create([]);
        }

        return response()->json([
            'data' => [
                'id' => $setting->id,
                'site_name' => $setting->site_name,
                'hotline' => $setting->hotline,
                'email' => $setting->email,
                'address' => $setting->address,
                'hours' => $setting->hours,
                'google_map_embed' => $setting->extra['google_map_embed'] ?? null,
                'footer_config' => $setting->footer_config,
                'contact_config' => $setting->contact_config,
                'meta_default_title' => $setting->meta_default_title,
                'meta_default_description' => $setting->meta_default_description,
                'meta_default_keywords' => $setting->meta_default_keywords,
                'logo_image_id' => $setting->logo_image_id,
                'logo_image_url' => $setting->logoImage?->url,
                'favicon_image_id' => $setting->favicon_image_id,
                'favicon_image_url' => $setting->faviconImage?->url,
                'product_watermark_image_id' => $setting->product_watermark_image_id,
                'product_watermark_image_url' => $setting->productWatermarkImage?->url,
                'product_watermark_type' => $setting->product_watermark_type ?? 'image',
                'product_watermark_position' => $setting->product_watermark_position,
                'product_watermark_size' => $setting->product_watermark_size,
                'product_watermark_text' => $setting->product_watermark_text,
                'product_watermark_text_size' => $setting->product_watermark_text_size ?? 'medium',
                'product_watermark_text_position' => $setting->product_watermark_text_position ?? 'center',
                'product_watermark_text_opacity' => $setting->product_watermark_text_opacity ?? 50,
                'updated_at' => $setting->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $setting = Setting::first();

        if (!$setting) {
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
            'meta_default_title' => ['nullable', 'string', 'max:255'],
            'meta_default_description' => ['nullable', 'string', 'max:500'],
            'meta_default_keywords' => ['nullable', 'string', 'max:500'],
            'logo_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'favicon_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'product_watermark_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'product_watermark_type' => ['nullable', 'string', 'in:image,text'],
            'product_watermark_position' => ['nullable', 'string', 'in:none,top_left,top_right,bottom_left,bottom_right'],
            'product_watermark_size' => ['nullable', 'string', 'in:64x64,96x96,128x128,160x160,192x192'],
            'product_watermark_text' => ['nullable', 'string', 'max:100'],
            'product_watermark_text_size' => ['nullable', 'string', 'in:xxsmall,xsmall,small,medium,large,xlarge,xxlarge'],
            'product_watermark_text_position' => ['nullable', 'string', 'in:top,center,bottom'],
            'product_watermark_text_opacity' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        if (isset($validated['google_map_embed'])) {
            $extra = $setting->extra ?? [];
            $extra['google_map_embed'] = $validated['google_map_embed'];
            $validated['extra'] = $extra;
            unset($validated['google_map_embed']);
        }

        $setting->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật cấu hình thành công',
        ]);
    }
}
