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
        Schema::table('catalog_attribute_groups', function (Blueprint $table) {
            $table->string('icon_path')->nullable()->after('display_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_attribute_groups', function (Blueprint $table) {
            $table->dropColumn('icon_path');
        });
    }
};
