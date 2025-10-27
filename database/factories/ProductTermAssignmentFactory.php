<?php

namespace Database\Factories;

use App\Models\ProductTermAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductTermAssignment>
 */
class ProductTermAssignmentFactory extends Factory
{
    protected $model = ProductTermAssignment::class;

    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'term_id' => \App\Models\CatalogTerm::factory(),
            'is_primary' => $this->faker->boolean(40),
            'position' => $this->faker->numberBetween(0, 5),
            'extra' => null,
        ];
    }
}

