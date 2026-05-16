<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('article_category_id')
                ->nullable()
                ->after('category_key')
                ->constrained('article_categories')
                ->nullOnDelete();
            $table->index(['article_category_id', 'active', 'published_at']);
        });

        $now = now();
        $defaults = [
            ['legacy_key' => 'kien-thuc', 'name' => 'Kiến thức', 'slug' => 'kien-thuc', 'description' => 'Bài hướng dẫn, chia sẻ kinh nghiệm và kiến thức rượu.', 'position' => 10],
            ['legacy_key' => 'chinh-sach', 'name' => 'Chính sách', 'slug' => 'ho-tro', 'description' => 'Nội dung pháp lý, mua hàng, vận chuyển, đổi trả.', 'position' => 20],
            ['legacy_key' => 'gioi-thieu', 'name' => 'Giới thiệu', 'slug' => 'gioi-thieu', 'description' => 'Nội dung về thương hiệu, câu chuyện và cam kết.', 'position' => 30],
            ['legacy_key' => 'dich-vu', 'name' => 'Dịch vụ', 'slug' => 'dich-vu', 'description' => 'Dịch vụ quà tặng, doanh nghiệp và tư vấn.', 'position' => 40],
            ['legacy_key' => 'qua-tang', 'name' => 'Quà tặng', 'slug' => 'qua-tang', 'description' => 'Nội dung tư vấn quà tặng theo nhu cầu.', 'position' => 50],
            ['legacy_key' => 'tin-tuc', 'name' => 'Tin tức', 'slug' => 'tin-tuc', 'description' => 'Tin tức thị trường, xu hướng và hoạt động mới.', 'position' => 60],
            ['legacy_key' => 'su-kien', 'name' => 'Sự kiện', 'slug' => 'su-kien', 'description' => 'Sự kiện tasting, hoạt động thương hiệu và trải nghiệm.', 'position' => 70],
        ];

        foreach ($defaults as $category) {
            $id = DB::table('article_categories')->insertGetId([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'active' => true,
                'position' => $category['position'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('articles')
                ->where('category_key', $category['legacy_key'])
                ->update(['article_category_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['article_category_id']);
            $table->dropIndex(['article_category_id', 'active', 'published_at']);
            $table->dropColumn('article_category_id');
        });

        Schema::dropIfExists('article_categories');
    }
};
