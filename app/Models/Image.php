<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'file_path',
        'alt',
        'width',
        'height',
        'mime',
        'model_type',
        'model_id',
        'order',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width' => 'int',
            'height' => 'int',
            'order' => 'int',
            'active' => 'bool',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
