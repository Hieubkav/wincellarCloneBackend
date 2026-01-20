<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['coverImage', 'categories', 'type'])
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->input('q') . '%');
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->input('type_id'));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('product_categories.id', $request->input('category_id')));
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'original_price' => $p->original_price,
                'active' => $p->active,
                'type_id' => $p->type_id,
                'type_name' => $p->type?->name,
                'category_name' => $p->categories->first()?->name,
                'cover_image_url' => $p->coverImage?->url,
                'created_at' => $p->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with(['coverImage', 'images', 'categories', 'type', 'terms.group'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'original_price' => $product->original_price,
                'active' => $product->active,
                'type_id' => $product->type_id,
                'category_ids' => $product->categories->pluck('id'),
                'cover_image_url' => $product->coverImage?->url,
                'images' => $product->images->map(fn($img) => [
                    'id' => $img->id,
                    'url' => $img->url,
                ]),
                'created_at' => $product->created_at?->toIso8601String(),
                'updated_at' => $product->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'active' => ['boolean'],
            'type_id' => ['nullable', 'exists:product_types,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:product_categories,id'],
            'cover_image_path' => ['nullable', 'string', 'max:255'],
            'term_ids' => ['nullable', 'array'],
            'term_ids.*' => ['integer', 'exists:catalog_terms,id'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['active'] = $validated['active'] ?? true;

        $product = Product::create($validated);

        if (!empty($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        if ($request->filled('cover_image_path')) {
            $this->attachCoverImage($product, $request->input('cover_image_path'));
        }

        if ($request->has('term_ids')) {
            $this->syncTerms($product, $request->input('term_ids', []));
        }

        return response()->json([
            'success' => true,
            'data' => ['id' => $product->id],
            'message' => 'Tạo sản phẩm thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'active' => ['boolean'],
            'type_id' => ['nullable', 'exists:product_types,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:product_categories,id'],
            'cover_image_path' => ['nullable', 'string', 'max:255'],
            'term_ids' => ['nullable', 'array'],
            'term_ids.*' => ['integer', 'exists:catalog_terms,id'],
        ]);

        $product->update($validated);

        if (isset($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        if ($request->has('cover_image_path')) {
            $coverPath = $request->input('cover_image_path');
            if ($coverPath) {
                $this->attachCoverImage($product, $coverPath);
            }
        }

        if ($request->has('term_ids')) {
            $this->syncTerms($product, $request->input('term_ids', []));
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa sản phẩm thành công',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

        $count = Product::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$count} sản phẩm",
            'count' => $count,
        ]);
    }

    private function attachCoverImage(Product $product, string $filePath): void
    {
        $product->images()->where('order', 0)->delete();

        Image::create([
            'file_path' => $filePath,
            'disk' => 'public',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'order' => 0,
        ]);
    }

    private function syncTerms(Product $product, array $termIds): void
    {
        $termIds = array_filter(array_map('intval', $termIds));
        $product->terms()->sync($termIds);
    }
}
