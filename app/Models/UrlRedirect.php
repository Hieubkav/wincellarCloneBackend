<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class UrlRedirect extends Model
{
    use HasFactory;

    public const TARGET_PRODUCT = 'Product';
    public const TARGET_ARTICLE = 'Article';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'from_slug',
        'to_slug',
        'target_type',
        'target_id',
        'auto_generated',
        'hit_count',
        'last_triggered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auto_generated' => 'bool',
            'hit_count' => 'int',
            'last_triggered_at' => 'datetime',
        ];
    }

    /**
     * Áp morph map cho 2 loại slug redirect (product/article) để dùng morphTo thuận tiện.
     */
    protected static function booted(): void
    {
        Relation::morphMap([
            self::TARGET_PRODUCT => Product::class,
            self::TARGET_ARTICLE => Article::class,
        ], true);
    }

    public function target(): MorphTo
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'target_id')
            ->where('target_type', self::TARGET_PRODUCT);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'target_id')
            ->where('target_type', self::TARGET_ARTICLE);
    }
}
