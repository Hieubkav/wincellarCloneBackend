<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = Str::title($this->faker->unique()->words(4, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(100, 999),
            'excerpt' => $this->faker->sentence(24),
            'content' => $this->faker->paragraphs(4, true),
            'author_id' => User::factory(),
            'active' => $this->faker->boolean(85),
            'meta_title' => "{$title} | Wincellar",
            'meta_description' => $this->faker->sentence(18),
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
