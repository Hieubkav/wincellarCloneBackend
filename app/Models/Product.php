<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Product extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'brand_id',
        'product_category_id',
        'type_id',
        'country_id',
        'region_id',
        'description',
        'price',
        'original_price',
        'alcohol_percent',
        'volume_ml',
        'badges',
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
            'badges' => 'array',
            'active' => 'bool',
            'price' => 'int',
            'original_price' => 'int',
            'alcohol_percent' => 'float',
            'volume_ml' => 'int',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'product_regions')
            ->withPivot('order')
            ->orderByPivot('order');
    }

    public function grapes(): BelongsToMany
    {
        return $this->belongsToMany(Grape::class, 'product_grapes')
            ->withPivot('order')
            ->orderByPivot('order');
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
