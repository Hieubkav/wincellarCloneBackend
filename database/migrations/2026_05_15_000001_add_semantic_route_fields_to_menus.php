<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('semantic_type', 50)->nullable()->after('href');
            $table->json('route_payload')->nullable()->after('semantic_type');
        });

        Schema::table('menu_block_items', function (Blueprint $table) {
            $table->string('semantic_type', 50)->nullable()->after('href');
            $table->json('route_payload')->nullable()->after('semantic_type');
        });
    }

    public function down(): void
    {
        Schema::table('menu_block_items', function (Blueprint $table) {
            $table->dropColumn(['semantic_type', 'route_payload']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['semantic_type', 'route_payload']);
        });
    }
};
