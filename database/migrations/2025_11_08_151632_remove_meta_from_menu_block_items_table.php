<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bỏ trường meta (JSON) - thay vào đó sử dụng morphMany Image
     * để upload ảnh icon cho menu_block_items theo pattern HasMediaGallery
     */
    public function up(): void
    {
        Schema::table('menu_block_items', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_block_items', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('badge');
        });
    }
};
