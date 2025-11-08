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
        Schema::table('catalog_terms', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex('catalog_terms_parent_group_index');
            $table->dropColumn('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_terms', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('group_id')
                ->constrained('catalog_terms')
                ->nullOnDelete();
            $table->index(['parent_id', 'group_id'], 'catalog_terms_parent_group_index');
        });
    }
};
