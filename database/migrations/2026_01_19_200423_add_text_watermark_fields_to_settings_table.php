<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('product_watermark_type')->default('image')->after('product_watermark_size');
            $table->string('product_watermark_text')->nullable()->after('product_watermark_type');
            $table->string('product_watermark_text_size')->default('medium')->after('product_watermark_text');
            $table->string('product_watermark_text_position')->default('center')->after('product_watermark_text_size');
            $table->integer('product_watermark_text_opacity')->default(50)->after('product_watermark_text_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'product_watermark_type',
                'product_watermark_text',
                'product_watermark_text_size',
                'product_watermark_text_position',
                'product_watermark_text_opacity',
            ]);
        });
    }
};
