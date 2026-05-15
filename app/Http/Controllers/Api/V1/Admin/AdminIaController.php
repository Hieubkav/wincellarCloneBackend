<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Support\InformationArchitecture\NganIaTemplate;
use Illuminate\Http\JsonResponse;

class AdminIaController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'template_name' => 'IA chị Ngân',
                'groups' => NganIaTemplate::groups(),
                'compliance' => NganIaTemplate::compliance(),
            ],
        ]);
    }
}
