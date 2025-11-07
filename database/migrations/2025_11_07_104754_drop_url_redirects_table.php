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
        Schema::dropIfExists('url_redirects');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không cần restore lại table này
    }
};
