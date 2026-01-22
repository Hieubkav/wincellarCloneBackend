<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AdminUploadController extends Controller
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const WEBP_QUALITY = 85;

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,gif,webp|max:5120',
            'folder' => 'nullable|string|max:50',
        ]);

        $file = $request->file('image');

        if (!$file || !$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'File upload không hợp lệ',
            ], 422);
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return response()->json([
                'success' => false,
                'message' => 'File không được vượt quá 5MB',
            ], 422);
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES)) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ chấp nhận file ảnh JPEG, PNG, GIF, WebP',
            ], 422);
        }

        $folder = $request->input('folder', 'products');
        $folder = preg_replace('/[^a-z0-9\-_]/i', '', $folder);

        $dateDir = now()->format('Y/m/d');
        $directory = "uploads/{$folder}/content/{$dateDir}";

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugifiedName = $this->slugifyFilename($originalName);
        $filename = "{$slugifiedName}-" . time() . '-' . Str::random(6) . ".webp";
        $path = "{$directory}/{$filename}";

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $webp = $image->toWebp(quality: self::WEBP_QUALITY);
            Storage::disk('public')->put($path, $webp);
        } catch (\Throwable $e) {
            \Log::warning('Image upload failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể xử lý ảnh này',
            ], 422);
        }

        $url = '/storage/' . $path;

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
                'path' => $path,
                'filename' => $filename,
            ],
        ]);
    }

    public function uploadImageFromUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'folder' => ['nullable', 'string', 'max:50'],
        ]);

        $response = Http::timeout(10)->get($validated['url']);
        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải ảnh từ URL',
            ], 422);
        }

        $contentType = $response->header('Content-Type');
        if (!$contentType || !in_array(strtolower($contentType), self::ALLOWED_MIMES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'URL không phải ảnh hợp lệ',
            ], 422);
        }

        $body = $response->body();
        if (strlen($body) > self::MAX_FILE_SIZE) {
            return response()->json([
                'success' => false,
                'message' => 'Ảnh không được vượt quá 5MB',
            ], 422);
        }

        $folder = $request->input('folder', 'products');
        $folder = preg_replace('/[^a-z0-9\-_]/i', '', $folder);

        $dateDir = now()->format('Y/m/d');
        $directory = "uploads/{$folder}/content/{$dateDir}";

        $pathInfo = parse_url($validated['url'], PHP_URL_PATH) ?: '';
        $originalName = $pathInfo ? pathinfo($pathInfo, PATHINFO_FILENAME) : 'image';
        $slugifiedName = $this->slugifyFilename($originalName);
        $filename = "{$slugifiedName}-" . time() . '-' . Str::random(6) . ".webp";
        $path = "{$directory}/{$filename}";

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($body);
            $webp = $image->toWebp(quality: self::WEBP_QUALITY);
            Storage::disk('public')->put($path, $webp);
        } catch (\Throwable $e) {
            \Log::warning('Image URL upload failed', [
                'url' => $validated['url'],
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể xử lý ảnh từ URL',
            ], 422);
        }

        $url = '/storage/' . $path;

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
                'path' => $path,
                'filename' => $filename,
            ],
        ]);
    }

    private function slugifyFilename(string $name): string
    {
        return Str::of($name)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9\s-]/', '')
            ->replaceMatches('/\s+/', '-')
            ->replaceMatches('/-+/', '-')
            ->trim('-')
            ->toString() ?: 'image';
    }
}
