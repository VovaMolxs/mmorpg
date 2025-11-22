<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Получить список всех предметов (шаблонов).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Item::query();

        // Фильтрация по типу
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Фильтрация по подтипу
        if ($request->has('subtype')) {
            $query->where('subtype', $request->subtype);
        }

        // Фильтрация по редкости
        if ($request->has('rarity')) {
            $query->where('rarity', $request->rarity);
        }

        // Фильтрация по уровню
        if ($request->has('level_required')) {
            $query->where('level_required', '<=', $request->level_required);
        }

        $items = $query->paginate($request->get('per_page', 15));

        return response()->json($items);
    }

    /**
     * Получить информацию о конкретном предмете.
     */
    public function show(Item $item): JsonResponse
    {
        return response()->json($item);
    }
}
