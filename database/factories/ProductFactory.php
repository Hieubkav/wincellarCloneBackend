<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = Str::title($this->faker->unique()->words(3, true));
        $price = $this->faker->numberBetween(180_000, 6_500_000);
        $originalPrice = $price;

        if ($this->faker->boolean(35)) {
            $originalPrice = (int) round($price * $this->faker->randomFloat(2, 1.1, 1.45));
        }

        $badges = $this->faker->boolean(30)
            ? collect(['SALE', 'HOT', 'NEW', 'LIMITED'])->shuffle()->take($this->faker->numberBetween(1, 2))->all()
            : null;

        $extraAttrs = [];
        $volume = $this->faker->randomElement([null, 375, 500, 700, 750, 1000]);
        if ($volume !== null) {
            $extraAttrs['dung_tich'] = [
                'label' => 'Dung tích',
                'value' => $volume,
                'type' => 'number',
            ];
        }

        $alcohol = $this->faker->randomElement([null, $this->faker->randomFloat(1, 11, 16)]);
        if ($alcohol !== null) {
            $extraAttrs['1abv'] = [
                'label' => '%ABV',
                'value' => $alcohol,
                'type' => 'number',
            ];
        }

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 999),
            'product_category_id' => \App\Models\ProductCategory::factory(),
            'type_id' => \App\Models\ProductType::factory(),
            'description' => $this->faker->paragraphs(3, true),
            'price' => $price,
            'original_price' => $originalPrice,
            'badges' => $badges,
            'extra_attrs' => $extraAttrs ?: null,
            'active' => $this->faker->boolean(92),
            'meta_title' => "{$name} | Wincellar",
            'meta_description' => $this->faker->sentence(20),
        ];
    }
}

