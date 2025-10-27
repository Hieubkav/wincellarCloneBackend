<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    use HasFactory;

    public const TYPE_PRODUCT_VIEW = 'product_view';
    public const TYPE_ARTICLE_VIEW = 'article_view';
    public const TYPE_CTA_CONTACT = 'cta_contact';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'visitor_id',
        'session_id',
        'event_type',
        'product_id',
        'article_id',
        'metadata',
        'occurred_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(VisitorSession::class, 'session_id');
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
