<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\CatalogTerm;
use App\Models\Image;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Services\Media\MediaCanonicalService;
use App\Support\Media\MediaSemanticRegistry;
use Illuminate\Console\Command;

class RebuildCanonicalSlugs extends Command
{
    protected $signature = 'media:rebuild-canonical-slugs {--chunk=500} {--dry-run} {--force-slug} {--only=}';

    protected $description = 'Rebuild canonical slug theo format thien-kim-wine-{loai}-{ten}-{anh-x} cho ảnh legacy';

    public function handle(MediaCanonicalService $canonicalService): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $forceSlug = (bool) $this->option('force-slug');
        $only = $this->parseOnlyOption();

        $maps = $this->buildContextMaps();

        $updated = 0;
        $skipped = 0;

        Image::query()->orderBy('id')->chunkById($chunkSize, function ($images) use ($canonicalService, $dryRun, $forceSlug, $only, $maps, &$updated, &$skipped) {
            foreach ($images as $image) {
                $context = $this->resolveContext($image, $maps);

                if ($only && ! in_array($context['semantic'], $only, true)) {
                    $skipped++;

                    continue;
                }

                $newSemantic = $context['semantic'];
                $entityBase = $context['entity'];
                $index = $context['index'];

                $newSlug = $canonicalService->makeCanonicalSlug($newSemantic, $entityBase, $index);
                $newKey = $image->canonical_key ?: $canonicalService->resolveCanonicalKey($image);

                $dirty = false;
                if ($image->semantic_type !== $newSemantic) {
                    $image->semantic_type = $newSemantic;
                    $dirty = true;
                }
                if ($image->canonical_key !== $newKey) {
                    $image->canonical_key = $newKey;
                    $dirty = true;
                }
                if ($forceSlug || $image->canonical_slug !== $newSlug) {
                    $image->canonical_slug = $newSlug;
                    $dirty = true;
                }

                if ($dirty) {
                    $updated++;
                    if (! $dryRun) {
                        $image->saveQuietly();
                    }
                } else {
                    $skipped++;
                }
            }
        });

        $this->info("Hoàn tất. Updated={$updated}, Skipped={$skipped}.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    private function buildContextMaps(): array
    {
        $productIds = Image::query()->where('model_type', Product::class)->pluck('model_id')->unique()->filter()->all();
        $articleIds = Image::query()->where('model_type', Article::class)->pluck('model_id')->unique()->filter()->all();
        $termIds = Image::query()->where('model_type', CatalogTerm::class)->pluck('model_id')->unique()->filter()->all();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'slug', 'name'])
            ->mapWithKeys(fn ($p) => [$p->id => $p->slug ?: $p->name])
            ->toArray();

        $articles = Article::query()
            ->whereIn('id', $articleIds)
            ->get(['id', 'slug', 'title'])
            ->mapWithKeys(fn ($a) => [$a->id => $a->slug ?: $a->title])
            ->toArray();

        $terms = CatalogTerm::query()
            ->whereIn('id', $termIds)
            ->get(['id', 'slug', 'name'])
            ->mapWithKeys(fn ($t) => [$t->id => $t->slug ?: $t->name])
            ->toArray();

        $settings = Setting::query()->first();
        $settingName = $settings?->site_name ?: 'thien-kim-wine';
        $settingsMap = [];
        if ($settings?->logo_image_id) {
            $settingsMap[$settings->logo_image_id] = MediaSemanticRegistry::SETTINGS_LOGO;
        }
        if ($settings?->favicon_image_id) {
            $settingsMap[$settings->favicon_image_id] = MediaSemanticRegistry::SETTINGS_FAVICON;
        }
        if ($settings?->og_image_id) {
            $settingsMap[$settings->og_image_id] = MediaSemanticRegistry::SETTINGS_OG;
        }
        if ($settings?->product_watermark_image_id) {
            $settingsMap[$settings->product_watermark_image_id] = MediaSemanticRegistry::SETTINGS_WATERMARK;
        }

        $socialMap = SocialLink::query()
            ->whereNotNull('icon_image_id')
            ->get(['icon_image_id', 'platform'])
            ->mapWithKeys(fn ($link) => [$link->icon_image_id => $link->platform ?: 'social'])
            ->toArray();

        return [
            'products' => $products,
            'articles' => $articles,
            'terms' => $terms,
            'settings' => $settingsMap,
            'settings_name' => $settingName,
            'social' => $socialMap,
        ];
    }

    /**
     * @param  array<string, array<int|string, string>>  $maps
     * @return array{semantic:string,entity:string,index:int}
     */
    private function resolveContext(Image $image, array $maps): array
    {
        $semantic = MediaSemanticRegistry::normalize($image->semantic_type)
            ?? MediaSemanticRegistry::fromModelType($image->model_type)
            ?? MediaSemanticRegistry::SHARED;

        $entity = $image->alt
            ?? pathinfo($image->file_path ?? '', PATHINFO_FILENAME)
            ?: $semantic;

        if ($image->model_type === Product::class && isset($maps['products'][$image->model_id])) {
            $entity = $maps['products'][$image->model_id];
            $semantic = MediaSemanticRegistry::PRODUCT;
        } elseif ($image->model_type === Article::class && isset($maps['articles'][$image->model_id])) {
            $entity = $maps['articles'][$image->model_id];
            $semantic = MediaSemanticRegistry::ARTICLE;
        } elseif ($image->model_type === CatalogTerm::class && isset($maps['terms'][$image->model_id])) {
            $entity = $maps['terms'][$image->model_id];
            $semantic = MediaSemanticRegistry::TERM;
        } elseif (isset($maps['settings'][$image->id])) {
            $semantic = $maps['settings'][$image->id];
            $entity = $maps['settings_name'];
        } elseif (isset($maps['social'][$image->id])) {
            $semantic = MediaSemanticRegistry::SOCIAL;
            $entity = $maps['social'][$image->id];
        }

        $index = ($image->order ?? 0) + 1;
        if ($index < 1) {
            $index = 1;
        }

        return [
            'semantic' => $semantic,
            'entity' => $entity,
            'index' => $index,
        ];
    }

    /**
     * @return array<int, string>|null
     */
    private function parseOnlyOption(): ?array
    {
        $only = $this->option('only');
        if (! $only) {
            return null;
        }

        $items = array_filter(array_map('trim', explode(',', (string) $only)));

        return $items ?: null;
    }
}
