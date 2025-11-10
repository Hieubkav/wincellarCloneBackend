<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RevalidationService
{
    /**
     * Trigger Next.js on-demand revalidation
     * 
     * @param array<string> $paths Các paths cần revalidate (e.g., ["/", "/products"])
     * @param array<string> $tags Các tags cần revalidate (optional)
     * @return bool
     */
    public function triggerRevalidation(array $paths = [], array $tags = []): bool
    {
        $url = config('services.nextjs.revalidate_url');
        $secret = config('services.nextjs.revalidate_secret');

        if (!$url || !$secret) {
            Log::warning('Next.js revalidation not configured', [
                'url' => $url,
                'has_secret' => !empty($secret),
            ]);
            return false;
        }

        try {
            $response = Http::timeout(5)->post($url, [
                'secret' => $secret,
                'paths' => $paths,
                'tags' => $tags,
            ]);

            if ($response->successful()) {
                Log::info('Next.js revalidation triggered', [
                    'paths' => $paths,
                    'tags' => $tags,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::warning('Next.js revalidation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Next.js revalidation error', [
                'error' => $e->getMessage(),
                'paths' => $paths,
                'tags' => $tags,
            ]);
            return false;
        }
    }

    /**
     * Revalidate home page
     */
    public function revalidateHome(): bool
    {
        return $this->triggerRevalidation(['/'], ['home']);
    }

    /**
     * Revalidate all common pages
     */
    public function revalidateAll(): bool
    {
        return $this->triggerRevalidation(
            paths: ['/', '/products', '/filter'],
            tags: ['home', 'menu', 'products']
        );
    }
}
