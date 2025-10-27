<?php

namespace Database\Factories;

use App\Models\CatalogAttributeGroup;
use App\Models\CatalogTerm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CatalogTerm>
 */
class CatalogTermFactory extends Factory
{
    protected $model = CatalogTerm::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'group_id' => CatalogAttributeGroup::factory(),
            'parent_id' => null,
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->optional()->sentence(12),
            'icon_type' => $this->faker->randomElement(['lucide', 'emoji']),
            'icon_value' => $this->faker->randomElement(['sparkles', 'leaf', '🍇']),
            'metadata' => [
                'color' => $this->faker->hexColor(),
            ],
            'is_active' => true,
            'position' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function child(?CatalogTerm $parent = null): self
    {
        return $this->state(fn () => [
            'parent_id' => $parent?->id ?? CatalogTerm::factory(),
        ]);
    }
}

