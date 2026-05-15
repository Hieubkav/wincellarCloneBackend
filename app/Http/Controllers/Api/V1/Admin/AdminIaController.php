<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Support\InformationArchitecture\WebsitePageTemplate;
use Illuminate\Http\JsonResponse;

class AdminIaController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'template_name' => 'Sơ đồ trang đề xuất',
                'groups' => WebsitePageTemplate::groups(),
                'compliance' => WebsitePageTemplate::compliance(),
            ],
        ]);
    }
}
