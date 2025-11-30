<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductCategory extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type_id',
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

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category_product')
            ->withTimestamps();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
