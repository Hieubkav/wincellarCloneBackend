<?php

namespace App\Models;

use App\Models\Concerns\HasMediaGallery;
use App\Models\Concerns\HasRichEditorMedia;
use App\Support\Product\ProductPricing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Product extends Model
{
    use HasFactory;
    use HasMediaGallery;
    use HasRichEditorMedia;

    protected array $richEditorFields = ['description'];

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            ProductPricing::assertValidPricing($product->price, $product->original_price);
        });
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type_id',
        'description',
        'price',
        'original_price',
        'alcohol_percent',
        'volume_ml',
        'badges',
        'extra_attrs',
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
            'extra_attrs' => 'array',
            'active' => 'bool',
            'price' => 'int',
            'original_price' => 'int',
            'alcohol_percent' => 'float',
            'volume_ml' => 'int',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category_product')
            ->withTimestamps();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
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
        return $this->belongsToMany(
            CatalogTerm::class,
            'product_term_assignments',
            'product_id',
            'term_id'
        )
            ->using(\App\Models\Pivots\ProductTermPivot::class)
            ->withPivot(['position', 'extra'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('products.active', true);
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
            return optional($term->group)->code === $groupCode;
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

    protected function mediaPlaceholderKey(): string
    {
        return 'product';
    }

    public function getDiscountPercentAttribute(): ?int
    {
        return ProductPricing::discountPercent($this->price, $this->original_price);
    }

    public function getShouldShowContactCtaAttribute(): bool
    {
        return ProductPricing::shouldShowContactCta($this->price);
    }
}
