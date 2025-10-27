<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEventAggregateDaily extends Model
{
    use HasFactory;

    protected $table = 'tracking_event_aggregates_daily';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'date',
        'event_type',
        'product_id',
        'article_id',
        'views',
        'clicks',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views' => 'int',
            'clicks' => 'int',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
