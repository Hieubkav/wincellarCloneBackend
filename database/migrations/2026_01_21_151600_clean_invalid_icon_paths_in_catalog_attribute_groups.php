<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clean up invalid icon_path values (SVG content or paths)
        // Only keep simple icon names (e.g., "Wine", "Grape")
        DB::table('catalog_attribute_groups')
            ->whereNotNull('icon_path')
            ->where(function ($query) {
                $query->where('icon_path', 'like', '%<%')
                      ->orWhere('icon_path', 'like', '%svg%')
                      ->orWhere('icon_path', 'like', '%/%')
                      ->orWhereRaw('LENGTH(icon_path) > 50');
            })
            ->update(['icon_path' => null]);
    }

    public function down(): void
    {
        // Cannot revert this cleanup
    }
};
