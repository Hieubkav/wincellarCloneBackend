<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('product_detail_rules')->nullable()->after('product_mobile_main_image_height');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('product_detail_rules');
        });
    }
};
