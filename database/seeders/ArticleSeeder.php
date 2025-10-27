<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeederContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $context = SeederContext::get();
        $faker = $context->faker();
        $now = $context->now();

        Schema::disableForeignKeyConstraints();
        DB::table('articles')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('images')
            ->where('model_type', $context->modelClass('article'))
            ->delete();

        $authorId = DB::table('users')->orderBy('id')->value('id');
        if (!$authorId) {
            $authorId = DB::table('users')->insertGetId([
                'name' => 'Content Admin',
                'email' => 'content-admin@example.com',
                'password' => Hash::make('Password!234'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $titles = [
            'Bí quyết chọn vang đỏ cho bữa tối sang trọng',
            'Khám phá terroir Bordeaux và hương vị đặc trưng',
            '5 bước bảo quản rượu vang tại nhà chuẩn sommelier',
            'Phối hợp rượu mạnh với cigar dành cho người mới',
            'Gợi ý quà tặng doanh nghiệp mùa lễ hội ấn tượng',
            'Tụ họp cuối tuần với combo bia craft Việt Nam',
            'Pairing phô mai và vang trắng: 7 công thức dễ áp dụng',
            'Chân dung nhà làm vang trẻ gây tiếng vang tại Ý',
            'Xu hướng cocktail low-ABV dành cho giới trẻ',
            'So sánh Single Malt vs Blended Scotch: nên chọn loại nào',
        ];

        $articleCount = $context->count('articles', count($titles));
        $articles = [];
        $images = [];

        for ($i = 0; $i < $articleCount; $i++) {
            $title = $titles[$i] ?? $faker->sentence(8);
            $slug = $context->uniqueSlug('articles', $title);
            $content = $this->buildContent($faker, $title);
            $excerpt = Str::limit(strip_tags($content), 180);

            $articles[] = [
                'id' => $i + 1,
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content' => $content,
                'author_id' => $authorId,
                'active' => $i % 11 !== 0,
                'meta_title' => "{$title} | Wincellar Insights",
                'meta_description' => Str::limit($excerpt, 150),
                'created_at' => $now->copy()->subDays($articleCount - $i),
                'updated_at' => $now->copy()->subDays(mt_rand(0, 14)),
            ];

            $images[] = [
                'id' => $context->nextImageId(),
                'file_path' => "articles/{$slug}.jpg",
                'disk' => 'public',
                'alt' => $title,
                'width' => 1280,
                'height' => 720,
                'mime' => 'image/jpeg',
                'model_type' => $context->modelClass('article'),
                'model_id' => $i + 1,
                'order' => 0,
                'active' => true,
                'extra_attributes' => json_encode(['source' => 'seeder', 'photographer' => $faker->name()]),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('articles')->insert($articles);
        DB::table('images')->insert($images);

        $faker->unique(true);
    }

    private function buildContent(\Faker\Generator $faker, string $title): string
    {
        $paragraphs = $faker->paragraphs(random_int(5, 8));
        $body = implode("\n\n", $paragraphs);

        return <<<MARKDOWN
## {$title}

{$body}

### Mẹo nhỏ từ chuyên gia

- {$faker->sentence()}
- {$faker->sentence()}
- {$faker->sentence()}

### Gợi ý pairing

{$faker->paragraph()}
MARKDOWN;
    }
}
