<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductTermPivot extends Pivot
{
    protected $table = 'product_term_assignments';

    protected $foreignKey = 'product_id';

    protected $relatedKey = 'term_id';
}
