<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_attribute_groups', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('code');
        });

        DB::table('catalog_attribute_groups')
            ->select(['id', 'code', 'name'])
            ->orderBy('id')
            ->chunkById(100, function ($groups): void {
                $used = [];
                foreach ($groups as $group) {
                    $base = Str::slug(str_replace('_', '-', (string) ($group->code ?: $group->name))) ?: 'thuoc-tinh';
                    $slug = $base;
                    $i = 2;

                    while (in_array($slug, $used, true) || DB::table('catalog_attribute_groups')->where('slug', $slug)->where('id', '!=', $group->id)->exists()) {
                        $slug = "{$base}-{$i}";
                        $i++;
                    }

                    $used[] = $slug;
                    DB::table('catalog_attribute_groups')->where('id', $group->id)->update(['slug' => $slug]);
                }
            });

        Schema::table('catalog_attribute_groups', function (Blueprint $table) {
            $table->unique('slug', 'catalog_attribute_groups_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_attribute_groups', function (Blueprint $table) {
            $table->dropUnique('catalog_attribute_groups_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
