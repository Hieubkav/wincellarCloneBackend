<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Make model_type and model_id nullable to support orphaned images
     * (logo, favicon, social icons, etc.)
     */
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            // Make morphs nullable (drop/recreate constraint handled by Laravel automatically)
            $table->string('model_type')->nullable()->change();
            $table->unsignedBigInteger('model_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('model_type')->nullable(false)->change();
            $table->unsignedBigInteger('model_id')->nullable(false)->change();
        });
    }
};
