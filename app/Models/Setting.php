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
        'site_name',
        'hotline',
        'address',
        'hours',
        'email',
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
}
