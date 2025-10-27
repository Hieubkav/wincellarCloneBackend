<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Article extends Model
{
    use HasFactory;

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

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'model')
            ->orderBy('order');
    }

    public function coverImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'model')
            ->where('order', 0);
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
}
