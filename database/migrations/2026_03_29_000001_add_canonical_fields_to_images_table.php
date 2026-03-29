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
        Schema::table('images', function (Blueprint $table) {
            $table->string('semantic_type', 50)->nullable()->after('extra_attributes');
            $table->string('canonical_key', 40)->nullable()->after('semantic_type');
            $table->string('canonical_slug', 255)->nullable()->after('canonical_key');

            $table->unique('canonical_key', 'images_canonical_key_unique');
            $table->index(['semantic_type', 'canonical_slug'], 'images_semantic_slug_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropIndex('images_semantic_slug_index');
            $table->dropUnique('images_canonical_key_unique');
            $table->dropColumn(['semantic_type', 'canonical_key', 'canonical_slug']);
        });
    }
};
