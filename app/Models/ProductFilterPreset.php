<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFilterPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'slug',
        'filter_payload',
        'seo_title',
        'seo_description',
        'content',
        'position',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'filter_payload' => 'array',
            'position' => 'int',
            'active' => 'bool',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductFilterGroup::class, 'group_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
