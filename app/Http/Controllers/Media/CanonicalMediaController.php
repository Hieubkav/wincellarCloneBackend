<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Api\V1\ImageProxyController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ImageUrlService;
use App\Services\Media\MediaCanonicalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CanonicalMediaController extends Controller
{
    public function __construct(
        private MediaCanonicalService $canonicalService,
        private ImageUrlService $urlService
    ) {}

    public function show(Request $request, string $semantic, string $key, string $slug): Response
    {
        $image = $this->canonicalService->resolveByKey($key);

        if (! $image) {
            abort(404, 'Media not found');
        }

        $resolvedSemantic = $this->canonicalService->resolveSemanticType($image);

        if ($resolvedSemantic !== $semantic) {
            \Log::info('Canonical media semantic mismatch', [
                'requested' => $semantic,
                'resolved' => $resolvedSemantic,
                'image_id' => $image->id,
            ]);
        }

        if ($image->model_type === Product::class) {
            return app(ImageProxyController::class)->show($image->id);
        }

        $directResponse = $this->serveFromStorage($image, $slug);
        if ($directResponse) {
            return $directResponse;
        }

        $url = $this->urlService->getAbsoluteUrl($image);
        if ($url) {
            $remote = Http::timeout(10)->get($url);
            if ($remote->successful()) {
                $mime = $remote->header('Content-Type') ?: ($image->mime ?: 'image/jpeg');

                return response($remote->body(), 200, $this->buildHeaders($image, $mime, $slug));
            }
        }

        abort(404, 'Media not available');
    }

    private function serveFromStorage($image, string $slug): ?Response
    {
        $disk = $image->disk ?? config('filesystems.default');
        $path = $image->file_path;

        if (! $disk || ! $path) {
            return null;
        }

        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $content = Storage::disk($disk)->get($path);
        $mime = $image->mime ?: (Storage::disk($disk)->mimeType($path) ?: 'image/jpeg');

        return response($content, 200, $this->buildHeaders($image, $mime, $slug));
    }

    /**
     * @return array<string, string>
     */
    private function buildHeaders($image, string $mime, string $slug): array
    {
        $extension = pathinfo($image->file_path ?? '', PATHINFO_EXTENSION);
        $filename = $extension ? "{$slug}.{$extension}" : $slug;
        $etag = '"'.md5($image->id.'-'.$image->updated_at?->timestamp).'"';

        return [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'ETag' => $etag,
            'Last-Modified' => $image->updated_at?->toRfc7231String() ?? now()->toRfc7231String(),
        ];
    }
}
