<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('global_font_key')->nullable()->after('meta_default_keywords');
            $table->string('home_font_key')->nullable()->after('global_font_key');
            $table->string('product_list_font_key')->nullable()->after('home_font_key');
            $table->string('product_detail_font_key')->nullable()->after('product_list_font_key');
            $table->string('article_list_font_key')->nullable()->after('product_detail_font_key');
            $table->string('article_detail_font_key')->nullable()->after('article_list_font_key');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'global_font_key',
                'home_font_key',
                'product_list_font_key',
                'product_detail_font_key',
                'article_list_font_key',
                'article_detail_font_key',
            ]);
        });
    }
};
