<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\GhostState;
use App\Models\ResurrectionStone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResurrectionController extends Controller
{
    /**
     * Воскрешение призрака другим игроком.
     * POST /api/character/resurrect/{ghost_id}
     */
    public function resurrectGhost(Request $request, int $ghostId): JsonResponse
    {
        try {
            $resurrector = $this->getActiveCharacter($request);

            if (! $resurrector) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            // Проверка, что воскрешающий не является призраком
            if ($resurrector->isGhost()) {
                return response()->json([
                    'error' => 'Призраки не могут воскрешать других',
                ], 400);
            }

            // Получаем призрака
            $ghostState = GhostState::with('character', 'location')
                ->where('character_id', $ghostId)
                ->first();

            if (! $ghostState) {
                return response()->json([
                    'error' => 'Призрак не найден',
                ], 404);
            }

            $ghost = $ghostState->character;

            // Проверка, что призрак находится в той же локации
            if ($resurrector->location_id !== $ghost->location_id) {
                return response()->json([
                    'error' => 'Призрак находится в другой локации',
                ], 403);
            }

            // Проверка наличия способности "Воскрешение" у игрока
            $resurrectionSkillLevel = $resurrector->getSkillLevel('resurrection');
            if ($resurrectionSkillLevel < 1) {
                return response()->json([
                    'error' => 'У вас нет способности "Воскрешение"',
                ], 403);
            }

            DB::beginTransaction();

            try {
                // Воскрешаем персонажа
                $ghost->resurrect();
                $ghost->save();

                // Удаляем состояние призрака
                $ghostState->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Персонаж {$ghost->name} был воскрешен",
                    'character' => [
                        'id' => $ghost->id,
                        'name' => $ghost->name,
                        'health_current' => $ghost->health_current,
                        'mana_current' => $ghost->mana_current,
                    ],
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Ghost resurrection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'ghost_id' => $ghostId,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при воскрешении: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Само-воскрешение у камня воскрешения.
     * POST /api/ghost/resurrection-stones/{id}/interact
     */
    public function selfResurrect(Request $request, int $stoneId): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            // Проверка, что персонаж является призраком
            if (! $character->isGhost()) {
                return response()->json([
                    'error' => 'Только призраки могут использовать камни воскрешения',
                ], 400);
            }

            // Получаем камень воскрешения
            $stone = ResurrectionStone::with('location')
                ->where('id', $stoneId)
                ->first();

            if (! $stone) {
                return response()->json([
                    'error' => 'Камень воскрешения не найден',
                ], 404);
            }

            // Проверка активности камня
            if (! $stone->is_active) {
                return response()->json([
                    'error' => 'Камень воскрешения неактивен',
                ], 403);
            }

            // Проверка уровня персонажа
            if (! $stone->canBeUsedByLevel($character->level)) {
                return response()->json([
                    'error' => "Для использования этого камня требуется уровень {$stone->level_required}",
                ], 403);
            }

            // Проверка, что призрак находится в той же локации, что и камень
            if ($character->location_id !== $stone->location_id) {
                return response()->json([
                    'error' => 'Вы находитесь не у камня воскрешения',
                ], 403);
            }

            // Проверка перезарядки камня
            if (! $stone->isAvailable()) {
                $remainingMinutes = $stone->getRemainingCooldownMinutes();

                return response()->json([
                    'error' => "Камень воскрешения на перезарядке. Осталось минут: {$remainingMinutes}",
                ], 403);
            }

            DB::beginTransaction();

            try {
                // Воскрешаем персонажа (1 HP / 1 MP)
                $character->resurrect();
                $character->save();

                // Удаляем состояние призрака
                $ghostState = $character->ghostState;
                if ($ghostState) {
                    $ghostState->delete();
                }

                // Обновляем время использования камня
                $stone->markAsUsed();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Вы были воскрешены у камня '{$stone->name}'",
                    'character' => [
                        'id' => $character->id,
                        'name' => $character->name,
                        'health_current' => $character->health_current,
                        'mana_current' => $character->mana_current,
                        'location_id' => $character->location_id,
                    ],
                    'stone' => [
                        'id' => $stone->id,
                        'name' => $stone->name,
                        'cooldown_minutes' => $stone->cooldown_minutes,
                    ],
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Self-resurrection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'stone_id' => $stoneId,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при воскрешении: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить список камней воскрешения.
     * GET /api/world/resurrection-stones
     */
    public function getResurrectionStones(Request $request): JsonResponse
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
            Log::error('Get resurrection stones failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при получении камней воскрешения: '.$e->getMessage(),
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
