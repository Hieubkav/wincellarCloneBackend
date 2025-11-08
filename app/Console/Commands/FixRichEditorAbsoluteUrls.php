<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixRichEditorAbsoluteUrls extends Command
{
    protected $signature = 'rich-editor:fix-absolute-urls {--model=Product}';

    protected $description = 'Convert absolute URLs to relative paths in rich editor fields';

    public function handle()
    {
        $modelClass = "App\\Models\\" . $this->option('model');
        
        if (!class_exists($modelClass)) {
            $this->error("Model class {$modelClass} not found!");
            return 1;
        }

        $model = new $modelClass;
        
        if (!in_array('App\Models\Concerns\HasRichEditorMedia', class_uses_recursive($modelClass))) {
            $this->error("Model {$modelClass} does not use HasRichEditorMedia trait!");
            return 1;
        }

        $reflection = new \ReflectionClass($model);
        if (!$reflection->hasProperty('richEditorFields')) {
            $this->warn("No richEditorFields property defined in {$modelClass}");
            return 0;
        }

        $property = $reflection->getProperty('richEditorFields');
        $property->setAccessible(true);
        $fields = $property->getValue($model);
        
        if (empty($fields)) {
            $this->warn("No rich editor fields defined in {$modelClass}");
            return 0;
        }

        $this->info("Processing {$modelClass} with fields: " . implode(', ', $fields));

        $records = $modelClass::all();
        $updated = 0;

        foreach ($records as $record) {
            $changed = false;
            
            foreach ($fields as $field) {
                $content = $record->getAttribute($field);
                
                if (!$content) {
                    continue;
                }

                $newContent = $this->convertAbsoluteToRelative($content);
                
                if ($newContent !== $content) {
                    $record->setAttribute($field, $newContent);
                    $changed = true;
                }
            }
            
            if ($changed) {
                $record->saveQuietly();
                $updated++;
                $this->line("Updated: {$record->getKey()}");
            }
        }

        $this->info("✓ Updated {$updated} records");
        
        return 0;
    }

    protected function convertAbsoluteToRelative(string $content): string
    {
        $pattern = '/src=["\']https?:\/\/[^\/]+\/storage\/([^"\']+)["\']/i';
        
        return preg_replace_callback($pattern, function ($matches) {
            $path = $matches[1];
            return 'src="/storage/' . $path . '"';
        }, $content);
    }
}
