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
            $table->string('input_type', 20)
                ->nullable()
                ->after('filter_type'); // text | number khi filter_type = nhap_tay
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_attribute_groups', function (Blueprint $table) {
            $table->dropColumn('input_type');
        });
    }
};
