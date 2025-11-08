<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cập nhật giá trị filter_type từ tiếng Anh sang tiếng Việt
        // single -> chon_don
        DB::table('catalog_attribute_groups')
            ->where('filter_type', 'single')
            ->update(['filter_type' => 'chon_don']);

        // multi, hierarchy, range, tag -> chon_nhieu
        DB::table('catalog_attribute_groups')
            ->whereIn('filter_type', ['multi', 'hierarchy', 'range', 'tag'])
            ->update(['filter_type' => 'chon_nhieu']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: chuyển về giá trị cũ
        DB::table('catalog_attribute_groups')
            ->where('filter_type', 'chon_don')
            ->update(['filter_type' => 'single']);

        DB::table('catalog_attribute_groups')
            ->where('filter_type', 'chon_nhieu')
            ->update(['filter_type' => 'multi']);
    }
};
