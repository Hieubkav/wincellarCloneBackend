<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminProductController extends Controller
{
    public function filters(Request $request): JsonResponse
    {
        $controllerStart = microtime(true);
        $typeId = $request->integer('type_id');
        $cacheKey = 'admin:products:filters:'.($typeId ?? 'all');

        $queryStart = microtime(true);
        $payload = Cache::remember($cacheKey, 60, function () use ($typeId) {
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
                $categoriesQuery->where('type_id', $typeId);
            }

            $categories = $categoriesQuery->get(['id', 'name', 'slug', 'type_id']);

            return [
                'types' => $types->map(fn (ProductType $type) => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                ])->values(),
                'categories' => $categories->map(fn (ProductCategory $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'type_id' => $category->type_id,
                ])->values(),
            ];
        });
        $queryMs = (microtime(true) - $queryStart) * 1000;

        $transformStart = microtime(true);
        $transformMs = (microtime(true) - $transformStart) * 1000;

        $audit = $this->buildAudit($request, $controllerStart, $queryMs, $transformMs);
        $response = ['data' => $payload];
        if ($audit) {
            $response['meta'] = ['audit' => $audit];
        }

        return response()->json($response);
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
        $controllerStart = microtime(true);
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
            ->with([
                'coverImage:id,file_path,alt,disk,model_id,model_type,order',
                'type:id,name',
                'categories' => fn ($query) => $query
                    ->select('product_categories.id', 'product_categories.name')
                    ->orderBy('product_categories.order')
                    ->orderBy('product_categories.id'),
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
        Image::primeProxyCacheVersion();
        $queryStart = microtime(true);
        $products = $query->paginate($perPage);
        $queryMs = (microtime(true) - $queryStart) * 1000;

        $transformStart = microtime(true);
        $items = $products->map(function (Product $product) {
            $categoryNames = $product->categories->pluck('name')->values();
            $categoryIds = $product->categories->pluck('id')->values();

            return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'original_price' => $product->original_price,
            'active' => $product->active,
            'type_id' => $product->type_id,
            'type_name' => $product->type?->name,
            'category_name' => $categoryNames->first(),
            'category_names' => $categoryNames,
            'category_ids' => $categoryIds,
            'categories' => $product->categories->map(fn (ProductCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])->values(),
            'cover_image_url' => $product->coverImage?->proxy_url ?? $product->coverImage?->url,
            'cover_image_canonical_url' => $product->coverImage?->canonical_url,
            'created_at' => $product->created_at?->toIso8601String(),
        ];
        });
        $transformMs = (microtime(true) - $transformStart) * 1000;

        $meta = [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ];

        $audit = $this->buildAudit($request, $controllerStart, $queryMs, $transformMs);
        if ($audit) {
            $meta['audit'] = $audit;
        }

        return response()->json([
            'data' => $items,
            'meta' => $meta,
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

    public function show(Request $request, int $id): JsonResponse
    {
        $controllerStart = microtime(true);
        $queryStart = microtime(true);
        $product = Product::with(['coverImage', 'images', 'categories', 'type', 'terms.group'])
            ->findOrFail($id);
        $queryMs = (microtime(true) - $queryStart) * 1000;

        $transformStart = microtime(true);
        $payload = [
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
            'cover_image_canonical_url' => $product->coverImage?->canonical_url,
            'images' => $product->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->absolute_url,
                'canonical_url' => $img->canonical_url,
                'canonical_key' => $img->canonical_key,
                'semantic_type' => $img->semantic_type,
                'path' => $img->file_path,
            ]),
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
        ];
        $transformMs = (microtime(true) - $transformStart) * 1000;

        $audit = $this->buildAudit($request, $controllerStart, $queryMs, $transformMs);
        $response = ['data' => $payload];
        if ($audit) {
            $response['meta'] = ['audit' => $audit];
        }

        return response()->json($response);
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

        $typeId = $validated['type_id'] ?? null;
        if (! empty($validated['category_ids'])) {
            $this->assertCategoriesMatchType($typeId, $validated['category_ids']);
        }

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

        $typeId = array_key_exists('type_id', $validated) ? $validated['type_id'] : $product->type_id;
        if (array_key_exists('category_ids', $validated)) {
            $this->assertCategoriesMatchType($typeId, $validated['category_ids']);
        } elseif (array_key_exists('type_id', $validated)) {
            $this->assertCategoriesMatchType($typeId, $product->categories->pluck('id')->all());
        }

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

    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => ['string', Rule::in(['active', 'type_id', 'category_ids'])],
            'changes' => ['required', 'array'],
            'changes.active' => ['boolean'],
            'changes.type_id' => ['nullable', 'exists:product_types,id'],
            'changes.category_ids' => ['array'],
            'changes.category_ids.*' => ['integer', 'exists:product_categories,id'],
        ]);

        $fields = collect($validated['fields'])->unique()->values();
        $changes = $validated['changes'];

        $missingFields = $fields->filter(fn (string $field) => ! array_key_exists($field, $changes));
        if ($missingFields->isNotEmpty()) {
            throw ValidationException::withMessages([
                'changes' => ['Thiếu dữ liệu cho trường: '.$missingFields->implode(', ')],
            ]);
        }

        $updates = [];
        if ($fields->contains('active')) {
            $updates['active'] = (bool) $changes['active'];
        }
        if ($fields->contains('type_id')) {
            $updates['type_id'] = $changes['type_id'];
        }

        $ids = $validated['ids'];
        if ($fields->contains('type_id') && ! $fields->contains('category_ids')) {
            $products = Product::with(['categories:id,type_id'])->whereIn('id', $ids)->get(['id']);
            foreach ($products as $product) {
                $invalid = $product->categories->filter(function (ProductCategory $category) use ($changes) {
                    if ($changes['type_id'] === null) {
                        return $category->type_id !== null;
                    }

                    return $category->type_id !== $changes['type_id'];
                });

                if ($invalid->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'changes.type_id' => ['Danh mục phải cùng phân loại với sản phẩm.'],
                    ]);
                }
            }
        }

        if ($fields->contains('category_ids') && ! empty($changes['category_ids'])) {
            $categoryTypeMap = ProductCategory::whereIn('id', $changes['category_ids'])
                ->pluck('type_id', 'id');
            $products = Product::whereIn('id', $ids)->get(['id', 'type_id']);

            foreach ($products as $product) {
                $effectiveTypeId = $fields->contains('type_id') ? $changes['type_id'] : $product->type_id;
                $invalid = $categoryTypeMap->filter(function ($categoryTypeId) use ($effectiveTypeId) {
                    if ($effectiveTypeId === null) {
                        return $categoryTypeId !== null;
                    }

                    return $categoryTypeId !== $effectiveTypeId;
                });

                if ($invalid->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'changes.category_ids' => ['Danh mục phải cùng phân loại với sản phẩm.'],
                    ]);
                }
            }
        }

        DB::transaction(function () use ($ids, $fields, $updates, $changes): void {
            if (! empty($updates)) {
                Product::whereIn('id', $ids)->update($updates);
            }

            if ($fields->contains('category_ids')) {
                $products = Product::whereIn('id', $ids)->get(['id']);
                foreach ($products as $product) {
                    $product->categories()->sync($changes['category_ids']);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hàng loạt thành công',
            'count' => count($ids),
        ]);
    }

    private function assertCategoriesMatchType(?int $typeId, array $categoryIds): void
    {
        if (empty($categoryIds)) {
            return;
        }

        $categories = ProductCategory::whereIn('id', $categoryIds)->get(['id', 'type_id']);
        $invalid = $categories->filter(function (ProductCategory $category) use ($typeId) {
            if ($typeId === null) {
                return $category->type_id !== null;
            }

            return $category->type_id !== $typeId;
        });

        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'category_ids' => ['Danh mục phải cùng phân loại với sản phẩm.'],
            ]);
        }
    }

    private function attachCoverImage(Product $product, string $filePath): void
    {
        $filePath = trim($filePath);
        if ($filePath === '') {
            return;
        }

        DB::transaction(function () use ($product, $filePath) {
            $images = $product->images()->orderBy('order')->get();

            if ($images->isEmpty()) {
                Image::create([
                    'file_path' => $filePath,
                    'disk' => 'public',
                    'model_type' => Product::class,
                    'model_id' => $product->id,
                    'order' => 0,
                ]);

                return;
            }

            $existing = $images->firstWhere('file_path', $filePath);
            $ordered = collect();

            if ($existing instanceof Image) {
                $ordered->push($existing);
                foreach ($images as $image) {
                    if ($image->id !== $existing->id) {
                        $ordered->push($image);
                    }
                }
            } else {
                $newImage = Image::create([
                    'file_path' => $filePath,
                    'disk' => 'public',
                    'model_type' => Product::class,
                    'model_id' => $product->id,
                    'order' => 0,
                ]);
                $ordered->push($newImage);
                foreach ($images as $image) {
                    $ordered->push($image);
                }
            }

            $ordered->values()->each(function (Image $image, int $index) {
                if ($image->order !== $index) {
                    $image->order = $index;
                    $image->saveQuietly();
                }
            });
        });
    }

    private function syncProductImages(Product $product, array $paths): void
    {
        $paths = array_values(array_filter($paths));

        DB::transaction(function () use ($product, $paths) {
            $existingImages = $product->images()->orderBy('order')->get();

            if (empty($paths)) {
                foreach ($existingImages as $image) {
                    $image->delete();
                }

                return;
            }

            $imagesByPath = [];
            $imagesByKey = [];
            $imagesById = [];
            foreach ($existingImages as $image) {
                $imagesByPath[$image->file_path][] = $image;
                if (! empty($image->canonical_key)) {
                    $imagesByKey[$image->canonical_key][] = $image;
                }
                $imagesById[$image->id] = $image;
            }

            $usedIds = [];
            $ordered = [];

            foreach ($paths as $index => $path) {
                $image = $this->resolveExistingProductImage($imagesByPath, $imagesByKey, $imagesById, $path, $usedIds);
                $normalizedPath = $this->normalizeImagePathInput($path);

                if (! $image && $normalizedPath) {
                    $image = Image::create([
                        'file_path' => $normalizedPath,
                        'disk' => 'public',
                        'model_type' => Product::class,
                        'model_id' => $product->id,
                        'order' => $index,
                    ]);
                }

                if ($image instanceof Image) {
                    $usedIds[] = $image->id;
                }

                if ($image instanceof Image) {
                    $ordered[] = [$image, $index];
                }
            }

            foreach ($existingImages as $image) {
                if (! in_array($image->id, $usedIds, true)) {
                    $image->delete();
                }
            }

            foreach ($ordered as [$image, $index]) {
                if ($image->order !== $index) {
                    $image->order = $index;
                    $image->saveQuietly();
                }
            }
        });
    }

    /**
     * @param  array<string, array<int, Image>>  $imagesByPath
     * @param  array<string, array<int, Image>>  $imagesByKey
     * @param  array<int, Image>  $imagesById
     */
    private function resolveExistingProductImage(
        array &$imagesByPath,
        array &$imagesByKey,
        array &$imagesById,
        string $rawPath,
        array $usedIds
    ): ?Image {
        $rawPath = trim($rawPath);
        if ($rawPath === '') {
            return null;
        }

        if (ctype_digit($rawPath)) {
            $id = (int) $rawPath;
            if (isset($imagesById[$id]) && ! in_array($id, $usedIds, true)) {
                return $imagesById[$id];
            }
        }

        $canonicalKey = $this->extractCanonicalKey($rawPath);
        if ($canonicalKey && ! empty($imagesByKey[$canonicalKey])) {
            $candidate = array_shift($imagesByKey[$canonicalKey]);
            if ($candidate instanceof Image && ! in_array($candidate->id, $usedIds, true)) {
                return $candidate;
            }
        }

        $normalizedPath = $this->normalizeImagePathInput($rawPath);
        if ($normalizedPath && ! empty($imagesByPath[$normalizedPath])) {
            $candidate = array_shift($imagesByPath[$normalizedPath]);
            if ($candidate instanceof Image && ! in_array($candidate->id, $usedIds, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function extractCanonicalKey(string $value): ?string
    {
        $path = $value;
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
        }

        $path = ltrim($path, '/');

        if (preg_match('#^media/[^/]+/([^/]+)/#', $path, $matches)) {
            return $matches[1] ?? null;
        }

        return null;
    }

    private function normalizeImagePathInput(string $value): ?string
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        if (filter_var($normalized, FILTER_VALIDATE_URL)) {
            $normalized = parse_url($normalized, PHP_URL_PATH) ?: $normalized;
        }

        $normalized = ltrim($normalized, '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        if ($normalized === '' || str_starts_with($normalized, 'media/')) {
            return null;
        }

        return $normalized;
    }

    private function syncTerms(Product $product, array $termIds): void
    {
        $termIds = array_filter(array_map('intval', $termIds));
        $product->terms()->sync($termIds);
    }

    private function buildAudit(Request $request, float $controllerStart, float $queryMs, float $transformMs): ?array
    {
        if (! $request->boolean('audit')) {
            return null;
        }

        $audit = $request->attributes->get('audit', []);
        $audit['query_ms'] = (int) round($queryMs);
        $audit['transform_ms'] = (int) round($transformMs);
        $audit['controller_ms'] = (int) round((microtime(true) - $controllerStart) * 1000);

        return $audit;
    }
}
