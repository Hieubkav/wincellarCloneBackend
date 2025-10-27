<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

class Product extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'product_category_id',
        'type_id',
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

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
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

    public function termAssignments(): HasMany
    {
        return $this->hasMany(ProductTermAssignment::class);
    }

    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(CatalogTerm::class, 'product_term_assignments')
            ->withPivot(['is_primary', 'position', 'extra'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeWhereHasTerm(Builder $query, string $groupCode, $termIds): Builder
    {
        $termIds = collect($termIds)->filter()->values();
        if ($termIds->isEmpty()) {
            return $query;
        }

        return $query->whereHas('terms', function (Builder $termQuery) use ($groupCode, $termIds) {
            $termQuery->whereIn('catalog_terms.id', $termIds)
                ->whereHas('group', fn (Builder $groupQuery) => $groupQuery->where('code', $groupCode));
        });
    }

    public function primaryTerm(string $groupCode): ?CatalogTerm
    {
        $terms = $this->ensureTermsLoaded();

        return $terms->first(function (CatalogTerm $term) use ($groupCode) {
            return optional($term->group)->code === $groupCode
                && (bool) $term->pivot?->is_primary === true;
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, CatalogTerm>
     */
    public function termsByGroup(string $groupCode): Collection
    {
        $terms = $this->ensureTermsLoaded();

        return $terms
            ->filter(fn (CatalogTerm $term) => optional($term->group)->code === $groupCode)
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, CatalogTerm>
     */
    protected function ensureTermsLoaded(): Collection
    {
        if (!$this->relationLoaded('terms')) {
            $this->load(['terms.group']);
        }

        /** @var \Illuminate\Support\Collection<int, CatalogTerm> $terms */
        $terms = $this->getRelation('terms');

        return $terms;
    }
}
