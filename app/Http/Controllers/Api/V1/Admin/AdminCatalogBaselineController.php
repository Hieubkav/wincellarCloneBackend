<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Database\Seeders\CatalogBaselineSeeder;
use Illuminate\Http\JsonResponse;

class AdminCatalogBaselineController extends Controller
{
    public function seed(): JsonResponse
    {
        (new CatalogBaselineSeeder())->run();

        return response()->json([
            'success' => true,
            'message' => 'Đã khôi phục baseline catalog attribute groups và product types.',
        ]);
    }
}
