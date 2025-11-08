<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RichEditorMedia extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'field_name',
        'file_path',
        'disk',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
