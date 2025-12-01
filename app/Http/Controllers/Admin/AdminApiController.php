<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function getLibraryImages(Request $request): JsonResponse
    {
        try {
            $perPage = (int) ($request->query('per_page', '12'));
            $search = (string) ($request->query('search', ''));
            
            $query = Image::query()
                ->where('active', true)
                ->whereNull('deleted_at')
                ->orderByDesc('created_at');

            if (!empty($search)) {
                $searchTerm = "%{$search}%";
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('alt', 'like', $searchTerm)
                        ->orWhere('file_path', 'like', $searchTerm);
                });
            }

            $images = $query->paginate($perPage);

            return response()->json([
                'data' => collect($images->items())->map(fn(Image $image) => [
                    'id' => $image->id,
                    'url' => $image->url ?? '/images/placeholder.png',
                    'alt' => $image->alt ?? basename($image->file_path ?? ''),
                    'name' => basename($image->file_path ?? ''),
                    'mime' => $image->mime,
                ])->values(),
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'total' => $images->total(),
                'per_page' => $images->perPage(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in getLibraryImages: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }
}
