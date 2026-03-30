<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('product_detail_faq_enabled')->default(true)->after('product_detail_rules');
            $table->string('product_detail_faq_title')->nullable()->after('product_detail_faq_enabled');
            $table->string('product_detail_faq_eyebrow')->nullable()->after('product_detail_faq_title');
            $table->json('product_detail_faq_items')->nullable()->after('product_detail_faq_eyebrow');
            $table->string('product_detail_faq_position')->default('after_description')->after('product_detail_faq_items');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'product_detail_faq_enabled',
                'product_detail_faq_title',
                'product_detail_faq_eyebrow',
                'product_detail_faq_items',
                'product_detail_faq_position',
            ]);
        });
    }
};
