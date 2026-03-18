<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminProductController extends Controller
{
    public function filters(Request $request): JsonResponse
    {
        $typeId = $request->integer('type_id');

        $types = ProductType::query()
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);

        $categoriesQuery = ProductCategory::query()
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('id');

        if ($typeId) {
            $categoriesQuery->where(function ($query) use ($typeId) {
                $query->where('type_id', $typeId)
                    ->orWhereNull('type_id');
            });
        }

        $categories = $categoriesQuery->get(['id', 'name', 'slug']);

        return response()->json([
            'data' => [
                'types' => $types->map(fn (ProductType $type) => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                ])->values(),
                'categories' => $categories->map(fn (ProductCategory $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])->values(),
            ],
        ]);
    }

    public function listForSelect(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('active', true)
            ->with('coverImage')
            ->orderBy('name', 'asc');

        // Support fetching by IDs for preview
        if ($request->filled('ids')) {
            $ids = array_filter(array_map('intval', explode(',', $request->input('ids'))));
            if (! empty($ids)) {
                $query->whereIn('id', $ids);
            }
        } elseif ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->input('q').'%');
        }

        $limit = min($request->integer('limit', 50), 200);
        $products = $query->limit($limit)->get();

        return response()->json([
            'data' => $products->map(fn ($p) => [
                'value' => $p->id,
                'label' => $p->name.' (#'.$p->id.')',
                'price' => $p->price,
                'cover_image' => $p->coverImage ? [
                    'id' => $p->coverImage->id,
                    'url' => $p->coverImage->url,
                    'alt' => $p->coverImage->alt,
                ] : null,
            ]),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $sortable = ['id', 'name', 'price', 'original_price', 'active', 'created_at'];
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = 'id';
        }

        $query = Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                'products.price',
                'products.original_price',
                'products.active',
                'products.type_id',
                'products.created_at',
            ])
            ->selectSub(
                DB::table('product_category_product')
                    ->join('product_categories', 'product_categories.id', '=', 'product_category_product.product_category_id')
                    ->whereColumn('product_category_product.product_id', 'products.id')
                    ->orderBy('product_categories.order')
                    ->orderBy('product_categories.id')
                    ->limit(1)
                    ->select('product_categories.name'),
                'category_name'
            )
            ->with([
                'coverImage:id,file_path,alt,disk,model_id,model_type,order',
                'type:id,name',
            ])
            ->orderBy($sortBy, $sortDir)
            ->orderBy('products.id', 'desc');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->input('q').'%');
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->input('type_id'));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('product_categories.id', $request->input('category_id')));
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'original_price' => $p->original_price,
                'active' => $p->active,
                'type_id' => $p->type_id,
                'type_name' => $p->type?->name,
                'category_name' => $p->category_name,
                'cover_image_url' => $p->coverImage?->proxy_url ?? $p->coverImage?->url,
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

    public function export(Request $request): StreamedResponse
    {
        $sortable = ['id', 'name', 'price', 'original_price', 'active', 'created_at'];
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = 'id';
        }

        $query = Product::query()
            ->select([
                'id',
                'name',
                'slug',
                'description',
                'price',
                'original_price',
                'active',
                'type_id',
                'extra_attrs',
                'created_at',
            ])
            ->with([
                'coverImage:id,file_path,alt,disk,model_id,model_type',
                'categories:id,name',
                'type:id,name',
                'images:id,model_id,model_type,file_path,alt,disk,order',
            ]);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->input('q').'%');
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->input('type_id'));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('product_categories.id', $request->input('category_id')));
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $query->orderBy($sortBy, $sortDir)->orderBy('id', 'desc');

        $fileName = 'danh-sach-san-pham-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fprintf($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'Tên sản phẩm',
                'Slug',
                'Phân loại',
                'Danh mục',
                'Giá bán',
                'Giá gốc',
                'Trạng thái',
                'Ảnh đại diện',
                'Ảnh phụ (URLs)',
                'Mô tả',
                'Thuộc tính',
                'Ngày tạo',
            ]);

            $query->chunk(200, function ($products) use ($handle) {
                foreach ($products as $product) {
                    $categoryNames = $product->categories->map(fn ($c) => $c->name)->implode(', ');
                    $typeName = $product->type?->name ?? '';
                    $coverImage = $product->coverImage?->absolute_url ?? '';
                    $additionalImages = $product->images
                        ->map(function ($img) {
                            return $img->absolute_url ?? $img->url ?? $img->file_path;
                        })
                        ->filter(fn ($url) => $url && $url !== $coverImage)
                        ->implode('; ');

                    fputcsv($handle, [
                        $product->id,
                        $product->name,
                        $product->slug,
                        $typeName,
                        $categoryNames,
                        $product->price,
                        $product->original_price,
                        $product->active ? 'Có' : 'Không',
                        $coverImage,
                        $additionalImages,
                        $product->description,
                        $product->extra_attrs ? json_encode($product->extra_attrs, JSON_UNESCAPED_UNICODE) : '',
                        $product->created_at?->toIso8601String(),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
                'meta_title' => $product->meta_title,
                'meta_description' => $product->meta_description,
                'shopee_url' => $product->shopee_url,
                'price' => $product->price,
                'original_price' => $product->original_price,
                'extra_attrs' => $product->extra_attrs,
                'term_ids' => $product->terms->pluck('id'),
                'active' => $product->active,
                'type_id' => $product->type_id,
                'category_ids' => $product->categories->pluck('id'),
                'cover_image_url' => $product->coverImage?->absolute_url ?? $product->cover_image_url,
                'images' => $product->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => $img->absolute_url,
                    'path' => $img->file_path,
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
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'shopee_url' => ['nullable', 'string', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'active' => ['boolean'],
            'type_id' => ['nullable', 'exists:product_types,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:product_categories,id'],
            'cover_image_path' => ['nullable', 'string', 'max:255'],
            'image_paths' => ['nullable', 'array'],
            'image_paths.*' => ['string', 'max:255'],
            'extra_attrs' => ['nullable', 'array'],
            'term_ids' => ['nullable', 'array'],
            'term_ids.*' => ['integer', 'exists:catalog_terms,id'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['active'] = $validated['active'] ?? true;

        $product = Product::create($validated);

        if (! empty($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        if ($request->has('image_paths')) {
            $this->syncProductImages($product, $request->input('image_paths', []));
        } elseif ($request->filled('cover_image_path')) {
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
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'shopee_url' => ['nullable', 'string', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'active' => ['boolean'],
            'type_id' => ['nullable', 'exists:product_types,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:product_categories,id'],
            'cover_image_path' => ['nullable', 'string', 'max:255'],
            'image_paths' => ['nullable', 'array'],
            'image_paths.*' => ['string', 'max:255'],
            'extra_attrs' => ['nullable', 'array'],
            'term_ids' => ['nullable', 'array'],
            'term_ids.*' => ['integer', 'exists:catalog_terms,id'],
        ]);

        $product->update($validated);

        if (isset($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        if ($request->has('image_paths')) {
            $this->syncProductImages($product, $request->input('image_paths', []));
        } elseif ($request->has('cover_image_path')) {
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

    private function syncProductImages(Product $product, array $paths): void
    {
        $paths = array_values(array_filter($paths));

        $product->images()->delete();

        foreach ($paths as $index => $path) {
            Image::create([
                'file_path' => $path,
                'disk' => 'public',
                'model_type' => Product::class,
                'model_id' => $product->id,
                'order' => $index,
            ]);
        }
    }

    private function syncTerms(Product $product, array $termIds): void
    {
        $termIds = array_filter(array_map('intval', $termIds));
        $product->terms()->sync($termIds);
    }
}
