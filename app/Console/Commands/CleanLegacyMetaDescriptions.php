<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CleanLegacyMetaDescriptions extends Command
{
    protected $signature = 'seo:clean-legacy-meta-descriptions
        {--model=all : Model cần xử lý (all|Article|Product)}
        {--apply : Ghi dữ liệu vào DB (mặc định dry-run)}';

    protected $description = 'Clean meta_description legacy có HTML/Lexical cho articles/products';

    public function handle(): int
    {
        $modelOption = strtolower((string) $this->option('model'));
        $apply = (bool) $this->option('apply');

        $modelMap = [
            'article' => Article::class,
            'product' => Product::class,
        ];

        $targets = $modelOption === 'all'
            ? $modelMap
            : (array_key_exists($modelOption, $modelMap) ? [$modelOption => $modelMap[$modelOption]] : []);

        if (empty($targets)) {
            $this->error('Model không hợp lệ. Dùng: all|Article|Product');

            return self::FAILURE;
        }

        if (! $apply) {
            $this->info('Dry-run: chỉ thống kê, không ghi DB. Dùng --apply để cập nhật.');
        }

        $totalScanned = 0;
        $totalAffected = 0;
        $samples = [];

        foreach ($targets as $label => $modelClass) {
            $this->line("Đang quét {$modelClass}...");

            $result = $this->scanModel($modelClass, $apply, $samples);
            $totalScanned += $result['scanned'];
            $totalAffected += $result['affected'];

            $this->info("{$modelClass}: scanned {$result['scanned']}, affected {$result['affected']}");
        }

        $this->info("Tổng scanned: {$totalScanned}");
        $this->info("Tổng affected: {$totalAffected}");

        if (! empty($samples)) {
            $this->line('Sample diff (tối đa 5):');
            foreach ($samples as $sample) {
                $this->line("- {$sample['model']}#{$sample['id']}");
                $this->line("  before: {$sample['before']}");
                $this->line("  after : {$sample['after']}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  array<int, array{model:string,id:int|string,before:string,after:string}>  $samples
     * @return array{scanned:int,affected:int}
     */
    protected function scanModel(string $modelClass, bool $apply, array &$samples): array
    {
        $scanned = 0;
        $affected = 0;

        $this->baseQuery($modelClass)
            ->chunkById(200, function ($records) use ($modelClass, $apply, &$samples, &$scanned, &$affected): void {
                foreach ($records as $record) {
                    $scanned++;

                    $before = (string) $record->meta_description;
                    $after = $this->cleanMetaDescription($before);

                    if ($after === $before) {
                        continue;
                    }

                    $affected++;

                    if (count($samples) < 5) {
                        $samples[] = [
                            'model' => class_basename($modelClass),
                            'id' => $record->getKey(),
                            'before' => $this->shorten($before),
                            'after' => $this->shorten($after),
                        ];
                    }

                    if ($apply) {
                        $record->meta_description = $after;
                        $record->saveQuietly();
                    }
                }
            });

        return [
            'scanned' => $scanned,
            'affected' => $affected,
        ];
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    protected function baseQuery(string $modelClass): Builder
    {
        return $modelClass::query()
            ->whereNotNull('meta_description')
            ->where('meta_description', '<>', '')
            ->where(function (Builder $query): void {
                $query
                    ->where('meta_description', 'like', '%<%')
                    ->orWhere('meta_description', 'like', '%lexical__%')
                    ->orWhere('meta_description', 'like', '%&nbsp;%');
            })
            ->orderBy('id');
    }

    protected function cleanMetaDescription(string $html): string
    {
        $withSeparators = preg_replace('/<(\/)?(p|div|li|ul|ol|h[1-6]|blockquote|pre|tr|td|th)[^>]*>/i', ' ', $html);
        $withSeparators = preg_replace('/<br\s*\/?\s*>/i', ' ', (string) $withSeparators);
        $withoutTags = preg_replace('/<[^>]*>/', ' ', (string) $withSeparators);

        $decoded = str_replace(
            ['&nbsp;', '&amp;', '&quot;', '&#39;', '&lt;', '&gt;', '&ldquo;', '&rdquo;', '&lsquo;', '&rsquo;', '&hellip;'],
            [' ', '&', '"', "'", '<', '>', '"', '"', "'", "'", '...'],
            (string) $withoutTags
        );

        return trim(preg_replace('/\s+/', ' ', $decoded) ?? '');
    }

    protected function shorten(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        if (mb_strlen($value) <= 120) {
            return $value;
        }

        return mb_substr($value, 0, 117).'...';
    }
}
