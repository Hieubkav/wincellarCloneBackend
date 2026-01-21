<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasVolume = Schema::hasColumn('products', 'volume_ml');
        $hasAlcohol = Schema::hasColumn('products', 'alcohol_percent');

        if ($hasVolume || $hasAlcohol) {
            $labels = DB::table('catalog_attribute_groups')
                ->whereIn('code', ['dung_tich', '1abv'])
                ->pluck('name', 'code')
                ->toArray();

            $select = ['id', 'extra_attrs'];
            if ($hasVolume) {
                $select[] = 'volume_ml';
            }
            if ($hasAlcohol) {
                $select[] = 'alcohol_percent';
            }

            DB::table('products')
                ->select($select)
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($hasVolume, $hasAlcohol, $labels): void {
                    foreach ($rows as $row) {
                        $extraAttrs = $row->extra_attrs ? json_decode($row->extra_attrs, true) : [];
                        $extraAttrs = is_array($extraAttrs) ? $extraAttrs : [];

                        if ($hasVolume && $row->volume_ml !== null && !array_key_exists('dung_tich', $extraAttrs)) {
                            $extraAttrs['dung_tich'] = [
                                'label' => $labels['dung_tich'] ?? 'Dung tích',
                                'value' => (int) $row->volume_ml,
                                'type' => 'number',
                            ];
                        }

                        if ($hasAlcohol && $row->alcohol_percent !== null && !array_key_exists('1abv', $extraAttrs)) {
                            $extraAttrs['1abv'] = [
                                'label' => $labels['1abv'] ?? '%ABV',
                                'value' => (float) $row->alcohol_percent,
                                'type' => 'number',
                            ];
                        }

                        if (!empty($extraAttrs)) {
                            DB::table('products')
                                ->where('id', $row->id)
                                ->update([
                                    'extra_attrs' => json_encode($extraAttrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                                ]);
                        }
                    }
                });
        }

        Schema::table('products', function (Blueprint $table) use ($hasVolume, $hasAlcohol): void {
            if ($hasVolume) {
                $table->dropColumn('volume_ml');
            }
            if ($hasAlcohol) {
                $table->dropColumn('alcohol_percent');
            }
        });
    }

    public function down(): void
    {
        $hasVolume = Schema::hasColumn('products', 'volume_ml');
        $hasAlcohol = Schema::hasColumn('products', 'alcohol_percent');

        if (! $hasVolume || ! $hasAlcohol) {
            Schema::table('products', function (Blueprint $table) use ($hasVolume, $hasAlcohol): void {
                if (! $hasVolume) {
                    $table->unsignedInteger('volume_ml')->nullable()->after('original_price');
                }
                if (! $hasAlcohol) {
                    $table->decimal('alcohol_percent', 5, 2)->nullable()->after('original_price');
                }
            });
        }

        $select = ['id', 'extra_attrs'];
        if (! $hasVolume) {
            $select[] = 'volume_ml';
        }
        if (! $hasAlcohol) {
            $select[] = 'alcohol_percent';
        }

        DB::table('products')
            ->select($select)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($hasVolume, $hasAlcohol): void {
                foreach ($rows as $row) {
                    $extraAttrs = $row->extra_attrs ? json_decode($row->extra_attrs, true) : [];
                    $extraAttrs = is_array($extraAttrs) ? $extraAttrs : [];

                    $updates = [];

                    if (! $hasVolume && array_key_exists('dung_tich', $extraAttrs)) {
                        $value = $extraAttrs['dung_tich']['value'] ?? null;
                        $updates['volume_ml'] = is_numeric($value) ? (int) $value : null;
                    }

                    if (! $hasAlcohol && array_key_exists('1abv', $extraAttrs)) {
                        $value = $extraAttrs['1abv']['value'] ?? null;
                        $updates['alcohol_percent'] = is_numeric($value) ? (float) $value : null;
                    }

                    if (!empty($updates)) {
                        DB::table('products')
                            ->where('id', $row->id)
                            ->update($updates);
                    }
                }
            });
    }
};
