<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'href',
        'semantic_type',
        'route_payload',
        'badge',
        'icon',
        'depth',
        'order',
        'active',
        'open_in_new_tab',
    ];

    protected function casts(): array
    {
        return [
            'route_payload' => 'array',
            'depth' => 'int',
            'order' => 'int',
            'active' => 'bool',
            'open_in_new_tab' => 'bool',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('order')
            ->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
