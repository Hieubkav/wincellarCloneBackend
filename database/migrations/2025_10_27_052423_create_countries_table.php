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
        /*
         * Mục tiêu: chuẩn hóa thông tin quốc gia cho sản phẩm, giúp filter + analytics (thị trường theo country).
         * - `code` (ISO2/3) dùng để kết nối với service bên ngoài và hiển thị icon quốc kỳ.
         * - `slug` để phục vụ URL thân thiện và redirect.
         */
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
