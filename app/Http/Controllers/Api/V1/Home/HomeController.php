<?php

namespace App\Http\Controllers\Api\V1\Home;

use App\Http\Controllers\Controller;
use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentAssembler;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __construct(private readonly HomeComponentAssembler $assembler)
    {
    }

    public function __invoke(): JsonResponse
    {
        $components = HomeComponent::query()
            ->active()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $payload = $this->assembler->build($components);

        return response()->json([
            'data' => $payload,
        ]);
    }
}
