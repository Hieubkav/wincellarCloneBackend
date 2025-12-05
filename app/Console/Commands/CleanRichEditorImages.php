<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CleanRichEditorImages extends Command
{
    protected $signature = 'rich-editor:clean-unused {--dry-run : Chỉ hiển thị, không xóa file}';

    protected $description = 'Dọn các file ảnh rich editor không còn được tham chiếu trong CSDL (Products/Articles)';

    public function handle(): int
    {
        $this->info('Đang quét ảnh rich editor...');

        $dryRun = (bool) $this->option('dry-run');

        $used = $this->collectUsedImages();
        $this->info("Đã thu thập {$used['count']} đường dẫn đang sử dụng trong DB");

        $storage = $this->collectStorageFiles();
        $this->info("Phát hiện {$storage['count']} file trên disk public (thư mục rich editor)");

        $unused = array_diff($storage['files'], $used['files']);

        if (empty($unused)) {
            $this->info('✔ Không có file mồ côi.');
            return self::SUCCESS;
        }

        $this->warn('Số file mồ côi: ' . count($unused));
        $this->line('Danh sách:');
        foreach ($unused as $file) {
            $this->line(" - {$file}");
        }

        if ($dryRun) {
            $this->info('Dry-run: không xóa file.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Xóa toàn bộ các file trên?', false)) {
            $this->info('Đã hủy.');
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($unused as $file) {
            if (Storage::disk('public')->delete($file)) {
                $deleted++;
            }
        }

        $this->info("Đã xóa {$deleted} / " . count($unused) . ' file.');

        return self::SUCCESS;
    }

    /**
     * Lấy danh sách ảnh đang được tham chiếu trong DB.
     *
     * @return array{count:int,files:array<int,string>}
     */
    protected function collectUsedImages(): array
    {
        $files = [];

        foreach (Product::whereNotNull('description')->cursor() as $product) {
            $files = array_merge($files, $this->extractImages($product->description));
        }

        foreach (Article::whereNotNull('content')->cursor() as $article) {
            $files = array_merge($files, $this->extractImages($article->content));
        }

        $files = array_unique(array_map(fn ($path) => $this->normalizePath($path), $files));

        return [
            'count' => count($files),
            'files' => $files,
        ];
    }

    /**
     * Lấy danh sách file thực tế trong storage thuộc thư mục rich editor.
     *
     * @return array{count:int,files:array<int,string>}
     */
    protected function collectStorageFiles(): array
    {
        $roots = [
            storage_path('app/public/uploads/products/content'),
            storage_path('app/public/uploads/articles/content'),
            storage_path('app/public/rich-editor-images'), // legacy
        ];

        $files = [];

        foreach ($roots as $root) {
            if (!File::exists($root)) {
                continue;
            }

            foreach (File::allFiles($root) as $file) {
                $relative = str_replace(storage_path('app/public') . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
                $files[] = $relative;
            }
        }

        return [
            'count' => count($files),
            'files' => $files,
        ];
    }

    /**
     * Trích xuất đường dẫn ảnh từ HTML/JSON của Lexical.
     *
     * @return array<int,string>
     */
    protected function extractImages(string $content): array
    {
        $paths = [];

        // img src
        if (preg_match_all('/<img[^>]+src=[\"\']([^\"\']+)[\"\'][^>]*>/i', $content, $matches)) {
            $paths = array_merge($paths, $matches[1]);
        }

        // data-url
        if (preg_match_all('/data-url=[\"\']([^\"\']+)[\"\']/', $content, $dataMatches)) {
            $paths = array_merge($paths, $dataMatches[1]);
        }

        // JSON "url":"..."
        if (preg_match_all('/"url":"([^"]+)"/', $content, $jsonMatches)) {
            $paths = array_merge($paths, $jsonMatches[1]);
        }

        $paths = array_filter(array_map(fn ($path) => $this->normalizePath($path), $paths));

        return array_unique($paths);
    }

    protected function normalizePath(string $url): ?string
    {
        $url = str_replace(config('app.url'), '', $url);
        $url = str_replace(url('/'), '', $url);
        $url = ltrim($url, '/');

        if (str_starts_with($url, 'storage/')) {
            $url = substr($url, strlen('storage/'));
        }

        if (str_starts_with($url, 'uploads/') || str_starts_with($url, 'rich-editor-images')) {
            return $url;
        }

        return null;
    }
}
