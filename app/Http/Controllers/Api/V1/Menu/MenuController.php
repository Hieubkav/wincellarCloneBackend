<?php

namespace App\Http\Controllers\Api\V1\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\Api\V1\Menu\MenuAssembler;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __construct(private readonly MenuAssembler $assembler)
    {
    }

    public function __invoke(): JsonResponse
    {
        $menus = Menu::query()
            ->with([
                'blocks' => function ($query) {
                    $query->active()->orderBy('order');
                },
                'blocks.items' => function ($query) {
                    $query->active()->orderBy('order');
                },
                'blocks.items.term.group',
                'term'
            ])
            ->active()
            ->orderBy('order')
            ->get();

        $payload = $this->assembler->build($menus);

        return response()->json([
            'data' => $payload,
        ]);
    }
}
