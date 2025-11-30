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
        $filterType = $this->faker->randomElement(['chon_don', 'chon_nhieu', 'nhap_tay']);

        return [
            'code' => Str::slug($name),
            'name' => Str::title($name),
            'filter_type' => $filterType,
            'input_type' => $filterType === 'nhap_tay'
                ? $this->faker->randomElement(['text', 'number'])
                : null,
            'is_filterable' => true,
            'position' => $this->faker->numberBetween(0, 20),
            'display_config' => [
                'icon' => 'lucide:' . $this->faker->randomElement(['tag', 'sparkles', 'box']),
            ],
        ];
    }
}
