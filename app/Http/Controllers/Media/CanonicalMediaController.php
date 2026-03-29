<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Services\ImageUrlService;
use App\Services\Media\MediaCanonicalService;
use Illuminate\Http\Request;
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

        $url = $this->urlService->getAbsoluteUrl($image);

        if (! $url) {
            abort(404, 'Media not available');
        }

        return redirect()->away($url, 302)->withHeaders([
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
