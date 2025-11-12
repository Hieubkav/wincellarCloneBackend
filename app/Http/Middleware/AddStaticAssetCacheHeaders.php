<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddStaticAssetCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * Add aggressive caching headers for static assets like images, fonts, etc.
     * These assets rarely change and should be cached for a long time.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply to successful responses
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Check if this is a static asset request (images, fonts, etc.)
        $path = $request->path();
        $isStaticAsset = $this->isStaticAsset($path);

        if ($isStaticAsset) {
            // Add cache headers for 1 year (max allowed by HTTP spec)
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            
            // Add ETag for validation
            if ($content = $response->getContent()) {
                $etag = md5($content);
                $response->headers->set('ETag', '"' . $etag . '"');
                
                // Check if client has valid cached version
                $clientEtag = $request->header('If-None-Match');
                if ($clientEtag === '"' . $etag . '"') {
                    return response('', 304)->withHeaders([
                        'Cache-Control' => 'public, max-age=31536000, immutable',
                        'ETag' => '"' . $etag . '"',
                    ]);
                }
            }
            
            // Add Expires header for older browsers
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }

        return $response;
    }

    /**
     * Check if the requested path is a static asset
     */
    private function isStaticAsset(string $path): bool
    {
        // Check for storage files (images, PDFs, etc.)
        if (str_starts_with($path, 'storage/')) {
            return true;
        }

        // Check by file extension
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $staticExtensions = [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico',  // Images
            'woff', 'woff2', 'ttf', 'eot', 'otf',              // Fonts
            'css', 'js',                                        // Stylesheets and scripts
            'pdf', 'zip', 'mp4', 'mp3', 'webm',                // Documents and media
        ];

        return in_array(strtolower($extension), $staticExtensions);
    }
}
