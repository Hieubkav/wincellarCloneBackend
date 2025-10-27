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
         * Mục tiêu: lưu lại mọi hành động nhạy cảm trong admin (create/update/delete) để QA/Audit đối chiếu.
         * - `user_id` nullable vì có job hệ thống (nightly) cũng ghi log.
         * - `model_type`, `model_id`, `before`, `after` dùng để diff và export CSV theo yêu cầu v1.2.
         * - `ip_hash` giúp truy vết bất thường mà vẫn tuân thủ yêu cầu không lưu raw IP.
         */
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_hash', 128)->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
