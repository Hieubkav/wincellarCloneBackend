<?php

namespace App\Models;

use App\Observers\SocialLinkObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(SocialLinkObserver::class)]
class SocialLink extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'platform',
        'url',
        'icon_image_id',
        'order',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'int',
            'active' => 'bool',
        ];
    }

    public function iconImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'icon_image_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getIconUrlAttribute(): ?string
    {
        if ($this->relationLoaded('iconImage') && $this->iconImage) {
            return $this->iconImage->url;
        }

        if ($this->icon_image_id && $this->iconImage) {
            return $this->iconImage->url;
        }

        return \App\Support\Media\MediaConfig::placeholder('term');
    }
}
