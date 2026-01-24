<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertAbsoluteUrlsToRelative extends Command
{
    protected $signature = 'urls:convert-to-relative 
                            {--dry-run : Show what would be changed without making changes}
                            {--export-sql : Export SQL statements instead of executing}';

    protected $description = 'Convert absolute URLs (http://127.0.0.1:8000/storage/...) to relative paths (/storage/...) in database';

    private array $urlPatterns = [
        'http://127.0.0.1:8000/storage/',
        'http://localhost:8000/storage/',
        'https://127.0.0.1:8000/storage/',
        'https://localhost:8000/storage/',
    ];

    private array $sqlStatements = [];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $exportSql = $this->option('export-sql');

        $this->info('=== Converting Absolute URLs to Relative Paths ===');
        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $totalChanges = 0;

        // 1. Process home_components.config (JSON column)
        $totalChanges += $this->processHomeComponents($dryRun, $exportSql);

        // 2. Process articles.content (HTML/text with embedded URLs)
        $totalChanges += $this->processArticlesContent($dryRun, $exportSql);

        // 3. Process products.description (if any URLs)
        $totalChanges += $this->processProductsDescription($dryRun, $exportSql);

        $this->newLine();
        $this->info("=== Total changes: {$totalChanges} ===");

        if ($exportSql && !empty($this->sqlStatements)) {
            $this->newLine();
            $this->info('=== SQL Statements for Production ===');
            $this->newLine();
            
            $sqlOutput = implode("\n\n", $this->sqlStatements);
            $this->line($sqlOutput);
            
            // Save to file
            $sqlFile = database_path('sql/convert_urls_to_relative.sql');
            if (!is_dir(dirname($sqlFile))) {
                mkdir(dirname($sqlFile), 0755, true);
            }
            file_put_contents($sqlFile, $sqlOutput);
            $this->newLine();
            $this->info("SQL saved to: {$sqlFile}");
        }

        return Command::SUCCESS;
    }

    private function processHomeComponents(bool $dryRun, bool $exportSql): int
    {
        $this->info('Processing home_components.config...');
        
        $components = DB::table('home_components')->get();
        $changesCount = 0;

        foreach ($components as $component) {
            if (empty($component->config)) {
                continue;
            }

            $originalConfig = $component->config;
            $newConfig = $this->convertUrlsInString($originalConfig);

            if ($originalConfig !== $newConfig) {
                $changesCount++;
                $this->line("  - ID {$component->id} ({$component->type}): URLs found and converted");

                if ($exportSql) {
                    $escapedConfig = addslashes($newConfig);
                    $this->sqlStatements[] = "UPDATE home_components SET config = '{$escapedConfig}', updated_at = NOW() WHERE id = {$component->id};";
                }

                if (!$dryRun && !$exportSql) {
                    DB::table('home_components')
                        ->where('id', $component->id)
                        ->update([
                            'config' => $newConfig,
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        $this->info("  => {$changesCount} home_components updated");
        return $changesCount;
    }

    private function processArticlesContent(bool $dryRun, bool $exportSql): int
    {
        $this->info('Processing articles.content...');
        
        if (!DB::getSchemaBuilder()->hasTable('articles')) {
            $this->warn('  Table articles does not exist, skipping...');
            return 0;
        }

        $articles = DB::table('articles')->whereNotNull('content')->get();
        $changesCount = 0;

        foreach ($articles as $article) {
            $originalContent = $article->content;
            $newContent = $this->convertUrlsInString($originalContent);

            if ($originalContent !== $newContent) {
                $changesCount++;
                $this->line("  - Article ID {$article->id}: URLs converted");

                if ($exportSql) {
                    $escapedContent = addslashes($newContent);
                    $this->sqlStatements[] = "UPDATE articles SET content = '{$escapedContent}', updated_at = NOW() WHERE id = {$article->id};";
                }

                if (!$dryRun && !$exportSql) {
                    DB::table('articles')
                        ->where('id', $article->id)
                        ->update([
                            'content' => $newContent,
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        $this->info("  => {$changesCount} articles updated");
        return $changesCount;
    }

    private function processProductsDescription(bool $dryRun, bool $exportSql): int
    {
        $this->info('Processing products.description...');
        
        if (!DB::getSchemaBuilder()->hasTable('products')) {
            $this->warn('  Table products does not exist, skipping...');
            return 0;
        }

        $products = DB::table('products')->whereNotNull('description')->get();
        $changesCount = 0;

        foreach ($products as $product) {
            $originalDesc = $product->description;
            $newDesc = $this->convertUrlsInString($originalDesc);

            if ($originalDesc !== $newDesc) {
                $changesCount++;
                $this->line("  - Product ID {$product->id}: URLs converted");

                if ($exportSql) {
                    $escapedDesc = addslashes($newDesc);
                    $this->sqlStatements[] = "UPDATE products SET description = '{$escapedDesc}', updated_at = NOW() WHERE id = {$product->id};";
                }

                if (!$dryRun && !$exportSql) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'description' => $newDesc,
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        $this->info("  => {$changesCount} products updated");
        return $changesCount;
    }

    private function convertUrlsInString(string $content): string
    {
        $result = $content;
        
        foreach ($this->urlPatterns as $pattern) {
            $result = str_replace($pattern, '/storage/', $result);
        }

        return $result;
    }
}
