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
            $table->foreignId('product_watermark_image_id')
                ->nullable()
                ->after('favicon_image_id')
                ->constrained('images')
                ->nullOnDelete();

            $table->string('product_watermark_position', 20)
                ->default('none')
                ->after('product_watermark_image_id');

            $table->string('product_watermark_size', 16)
                ->default('128x128')
                ->after('product_watermark_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['product_watermark_image_id']);
            $table->dropColumn([
                'product_watermark_image_id',
                'product_watermark_position',
                'product_watermark_size',
            ]);
        });
    }
};
