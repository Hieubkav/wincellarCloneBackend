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
        // MariaDB/MySQL không support partial indexes (WHERE clause)
        // Giải pháp: Xóa unique constraint, dùng ImageObserver để đảm bảo order unique
        
        // Check if constraint exists before dropping
        $indexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE() 
            AND table_name = 'images' 
            AND index_name = 'images_unique_order_per_model'
        ");
        
        if ($indexExists[0]->count > 0) {
            Schema::table('images', function (Blueprint $table) {
                $table->dropUnique('images_unique_order_per_model');
            });
        }
        
        // Check if new index already exists
        $newIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE() 
            AND table_name = 'images' 
            AND index_name = 'images_order_index'
        ");
        
        if ($newIndexExists[0]->count == 0) {
            Schema::table('images', function (Blueprint $table) {
                // Thay bằng regular index (không unique) để tối ưu query
                $table->index(['model_type', 'model_id', 'order'], 'images_order_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            // Drop regular index
            $table->dropIndex('images_order_index');
            
            // Restore unique constraint
            $table->unique(['model_type', 'model_id', 'order'], 'images_unique_order_per_model');
        });
    }
};
