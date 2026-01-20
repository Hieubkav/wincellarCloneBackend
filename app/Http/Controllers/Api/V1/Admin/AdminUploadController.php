<?php
 
 namespace App\Http\Controllers\Api\V1\Admin;
 
 use App\Http\Controllers\Controller;
 use Illuminate\Http\JsonResponse;
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Storage;
 use Illuminate\Support\Str;
 
 class AdminUploadController extends Controller
 {
     private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
     private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
 
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
         $extension = $file->getClientOriginalExtension();
         $slugifiedName = $this->slugifyFilename($originalName);
         $filename = "{$slugifiedName}-" . time() . '-' . Str::random(6) . ".{$extension}";
         
         $path = $file->storeAs($directory, $filename, 'public');
         
         if (!$path) {
             return response()->json([
                 'success' => false,
                 'message' => 'Không thể lưu file',
             ], 500);
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
