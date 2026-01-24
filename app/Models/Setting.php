<?php

namespace App\Models;

use App\Observers\SettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(SettingObserver::class)]

class Setting extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'logo_image_id',
        'favicon_image_id',
        'product_watermark_image_id',
        'product_watermark_position',
        'product_watermark_size',
        'product_watermark_type',
        'product_watermark_text',
        'product_watermark_text_size',
        'product_watermark_text_position',
        'product_watermark_text_opacity',
        'site_name',
        'hotline',
        'address',
        'hours',
        'email',
        'google_map_embed',
        'footer_config',
        'meta_default_title',
        'meta_default_description',
        'meta_default_keywords',
        'extra',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'extra' => 'array',
            'meta_default_keywords' => 'array',
            'footer_config' => 'array',
        ];
    }

    public function logoImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'logo_image_id');
    }

    public function faviconImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'favicon_image_id');
    }

    public function productWatermarkImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'product_watermark_image_id');
    }
}
