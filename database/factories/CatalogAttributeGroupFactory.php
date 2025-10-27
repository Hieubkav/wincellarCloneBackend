<?php

namespace Database\Factories;

use App\Models\CatalogAttributeGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CatalogAttributeGroup>
 */
class CatalogAttributeGroupFactory extends Factory
{
    protected $model = CatalogAttributeGroup::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'code' => Str::slug($name),
            'name' => Str::title($name),
            'filter_type' => $this->faker->randomElement(['single', 'multi', 'hierarchy', 'tag']),
            'is_filterable' => true,
            'is_primary' => $this->faker->boolean(30),
            'position' => $this->faker->numberBetween(0, 20),
            'display_config' => [
                'icon' => 'lucide:' . $this->faker->randomElement(['tag', 'sparkles', 'box']),
            ],
        ];
    }
}

