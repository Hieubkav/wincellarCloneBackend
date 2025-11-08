<?php

namespace App\Observers;

use App\Models\RichEditorMedia;
use Illuminate\Support\Facades\Storage;

class RichEditorMediaObserver
{
    public function deleted(RichEditorMedia $richEditorMedia): void
    {
        if ($richEditorMedia->file_path && Storage::disk($richEditorMedia->disk)->exists($richEditorMedia->file_path)) {
            Storage::disk($richEditorMedia->disk)->delete($richEditorMedia->file_path);
        }
    }

    public function forceDeleted(RichEditorMedia $richEditorMedia): void
    {
        if ($richEditorMedia->file_path && Storage::disk($richEditorMedia->disk)->exists($richEditorMedia->file_path)) {
            Storage::disk($richEditorMedia->disk)->delete($richEditorMedia->file_path);
        }
    }
}
