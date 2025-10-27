<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'term_id',
        'type',
        'href',
        'config',
        'order',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'order' => 'int',
            'active' => 'bool',
        ];
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(CatalogTerm::class, 'term_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(MenuBlock::class)
            ->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

