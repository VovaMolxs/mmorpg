<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Location;
use App\Models\ResurrectionStone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResurrectionStoneController extends Controller
{
    /**
     * Получить камни воскрешения в локации.
     * GET /api/location/{id}/resurrection-stones
     */
    public function getStonesInLocation(Request $request, int $locationId): JsonResponse
    {
        try {
            $location = Location::find($locationId);

            if (! $location) {
                return response()->json([
                    'error' => 'Локация не найдена',
                ], 404);
            }

            $character = $this->getActiveCharacter($request);
            $isGhost = $character && $character->isGhost();

            $stones = ResurrectionStone::where('location_id', $locationId)
                ->get()
                ->map(function ($stone) use ($isGhost, $character) {
                    $isAvailable = $stone->is_active && $stone->isAvailable();
                    $canUse = $isGhost && $isAvailable && ($character ? $stone->canBeUsedByLevel($character->level) : false);

                    return [
                        'id' => $stone->id,
                        'name' => $stone->name,
                        'description' => $stone->description,
                        'level_required' => $stone->level_required,
                        'is_active' => $stone->is_active,
                        'is_available' => $isAvailable,
                        'can_use' => $canUse,
                        'cooldown_minutes' => $stone->cooldown_minutes,
                        'remaining_cooldown_minutes' => $stone->getRemainingCooldownMinutes(),
                        'visual_effect' => $stone->visual_effect,
                        'last_used_at' => $stone->last_used_at?->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'location_id' => $locationId,
                'stones' => $stones,
            ]);
        } catch (\Exception $e) {
            Log::error('Get resurrection stones in location failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'location_id' => $locationId,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении камней воскрешения: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить все камни воскрешения на карте мира.
     * GET /api/world/resurrection-stones
     */
    public function getAllStones(Request $request): JsonResponse
    {
        try {
            $stones = ResurrectionStone::with('location')
                ->where('is_active', true)
                ->get()
                ->map(function ($stone) {
                    return [
                        'id' => $stone->id,
                        'name' => $stone->name,
                        'description' => $stone->description,
                        'level_required' => $stone->level_required,
                        'visual_effect' => $stone->visual_effect,
                        'location' => [
                            'id' => $stone->location->id,
                            'name' => $stone->location->name,
                            'coordinate_x' => $stone->location->coordinate_x,
                            'coordinate_y' => $stone->location->coordinate_y,
                        ],
                    ];
                });

            return response()->json([
                'success' => true,
                'stones' => $stones,
            ]);
        } catch (\Exception $e) {
            Log::error('Get all resurrection stones failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении камней воскрешения: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить информацию о конкретном камне воскрешения.
     * GET /api/resurrection-stones/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $stone = ResurrectionStone::with('location')->find($id);

            if (! $stone) {
                return response()->json([
                    'error' => 'Камень воскрешения не найден',
                ], 404);
            }

            $character = $this->getActiveCharacter($request);
            $isGhost = $character && $character->isGhost();
            $isAvailable = $stone->is_active && $stone->isAvailable();
            $canUse = $isGhost && $isAvailable && ($character ? $stone->canBeUsedByLevel($character->level) : false);

            return response()->json([
                'success' => true,
                'stone' => [
                    'id' => $stone->id,
                    'name' => $stone->name,
                    'description' => $stone->description,
                    'level_required' => $stone->level_required,
                    'is_active' => $stone->is_active,
                    'is_available' => $isAvailable,
                    'can_use' => $canUse,
                    'cooldown_minutes' => $stone->cooldown_minutes,
                    'remaining_cooldown_minutes' => $stone->getRemainingCooldownMinutes(),
                    'visual_effect' => $stone->visual_effect,
                    'last_used_at' => $stone->last_used_at?->toIso8601String(),
                    'location' => [
                        'id' => $stone->location->id,
                        'name' => $stone->location->name,
                        'coordinate_x' => $stone->location->coordinate_x,
                        'coordinate_y' => $stone->location->coordinate_y,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get resurrection stone failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stone_id' => $id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении камня воскрешения: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить информацию о перезарядке камня.
     * GET /api/resurrection-stones/{id}/cooldown
     */
    public function getCooldown(Request $request, int $id): JsonResponse
    {
        try {
            $stone = ResurrectionStone::find($id);

            if (! $stone) {
                return response()->json([
                    'error' => 'Камень воскрешения не найден',
                ], 404);
            }

            $remainingMinutes = $stone->getRemainingCooldownMinutes();
            $isAvailable = $stone->isAvailable();

            return response()->json([
                'success' => true,
                'stone_id' => $stone->id,
                'is_available' => $isAvailable,
                'cooldown_minutes' => $stone->cooldown_minutes,
                'remaining_cooldown_minutes' => $remainingMinutes,
                'last_used_at' => $stone->last_used_at?->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Get resurrection stone cooldown failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stone_id' => $id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении информации о перезарядке: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить активного персонажа пользователя.
     */
    private function getActiveCharacter(Request $request): ?Character
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        return Character::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}
