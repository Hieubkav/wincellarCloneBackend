<?php

namespace App\Models;

use App\Models\Concerns\HasMediaGallery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;
    use HasMediaGallery;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'author_id',
        'active',
        'meta_title',
        'meta_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'bool',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }

    public function trackingDailyAggregates(): HasMany
    {
        return $this->hasMany(TrackingEventAggregateDaily::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    protected function mediaPlaceholderKey(): string
    {
        return 'article';
    }
}
