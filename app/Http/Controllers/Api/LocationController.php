<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveLocationRequest;
use App\Models\ActiveNpc;
use App\Models\Character;
use App\Models\CorpseContainer;
use App\Models\GhostState;
use App\Models\ItemInstance;
use App\Models\Location;
use App\Models\LocationExit;
use App\Models\ResurrectionStone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    /**
     * Получить текущую локацию персонажа.
     */
    public function current(Request $request): JsonResponse
    {
        $character = $this->getActiveCharacter($request);

        if (! $character) {
            return response()->json([
                'error' => 'Активный персонаж не найден',
            ], 404);
        }

        $location = $character->location;

        if (! $location) {
            return response()->json([
                'error' => 'Персонаж не находится ни в одной локации',
            ], 404);
        }

        $location->load('exits.toLocation');

        $items = ItemInstance::where('location_type', 'ground')
            ->where('location_id', $location->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('item')
            ->get();

        $npcs = ActiveNpc::where('location_id', $location->id)
            ->where('is_active', true)
            ->with(['npc.stats', 'npc.equipment.item'])
            ->get();

        $corpses = CorpseContainer::where('location_id', $location->id)
            ->where('expires_at', '>', now())
            ->with('character:id,name')
            ->get();

        $ghosts = GhostState::where('location_id', $location->id)
            ->where('is_visible', true)
            ->with('character:id,name,level')
            ->get();

        // Получаем обычных игроков (не призраков) в локации
        $onlinePlayers = $location->onlinePlayers()
            ->with('character:id,name,level,location_id')
            ->get();

        // Получаем камни воскрешения в локации
        $resurrectionStones = ResurrectionStone::where('location_id', $location->id)
            ->get();

        return response()->json([
            'location' => $this->formatLocation($location),
            'exits' => $this->formatExits($location->exits, $character),
            'items' => $this->formatItems($items),
            'npcs' => $this->formatNpcs($npcs),
            'corpses' => $this->formatCorpses($corpses, $character),
            'ghosts' => $this->formatGhosts($ghosts),
            'players' => $this->formatPlayers($onlinePlayers, $character),
            'resurrection_stones' => $this->formatResurrectionStones($resurrectionStones, $character),
        ]);
    }

    /**
     * Перемещение персонажа в указанном направлении.
     */
    public function move(MoveLocationRequest $request): JsonResponse
    {
        try {
            $character = $this->getActiveCharacter($request);

            if (! $character) {
                return response()->json([
                    'error' => 'Активный персонаж не найден',
                ], 404);
            }

            $currentLocation = $character->location;

            if (! $currentLocation) {
                return response()->json([
                    'error' => 'Персонаж не находится ни в одной локации',
                ], 404);
            }

            $direction = $request->input('direction');
            $customName = $request->input('custom_name');

            // Поиск выхода по направлению или кастомному имени
            // Если указано кастомное имя, ищем по нему, иначе по направлению
            $searchTerm = $customName ?? $direction;
            $exit = LocationExit::findExit($currentLocation, $searchTerm);

            if (! $exit) {
                return response()->json([
                    'error' => 'Выход в указанном направлении не найден',
                ], 404);
            }

            // Проверка доступности перехода
            $accessibility = $exit->isAccessible($character);
            if (! $accessibility['accessible']) {
                return response()->json([
                    'error' => 'Переход недоступен',
                    'errors' => $accessibility['errors'],
                ], 403);
            }

            // Перемещение персонажа
            $newLocation = $exit->toLocation;

            DB::transaction(function () use ($character, $newLocation) {
                $character->location_id = $newLocation->id;
                $character->save();
            });

            $newLocation->load('exits.toLocation');

            $items = ItemInstance::where('location_type', 'ground')
                ->where('location_id', $newLocation->id)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->with('item')
                ->get();

            $npcs = ActiveNpc::where('location_id', $newLocation->id)
                ->where('is_active', true)
                ->with(['npc.stats', 'npc.equipment.item'])
                ->get();

            $corpses = CorpseContainer::where('location_id', $newLocation->id)
                ->where('expires_at', '>', now())
                ->with('character:id,name')
                ->get();

            $ghosts = GhostState::where('location_id', $newLocation->id)
                ->where('is_visible', true)
                ->with('character:id,name,level')
                ->get();

            // Получаем обычных игроков (не призраков) в локации
            $onlinePlayers = $newLocation->onlinePlayers()
                ->with('character:id,name,level,location_id')
                ->get();

            // Получаем камни воскрешения в новой локации
            $resurrectionStones = ResurrectionStone::where('location_id', $newLocation->id)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Вы успешно переместились',
                'location' => $this->formatLocation($newLocation),
                'exits' => $this->formatExits($newLocation->exits, $character),
                'items' => $this->formatItems($items),
                'npcs' => $this->formatNpcs($npcs),
                'corpses' => $this->formatCorpses($corpses, $character),
                'ghosts' => $this->formatGhosts($ghosts),
                'players' => $this->formatPlayers($onlinePlayers, $character),
                'resurrection_stones' => $this->formatResurrectionStones($resurrectionStones, $character),
            ]);
        } catch (\Exception $e) {
            Log::error('Location move failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'character_id' => $character->id ?? null,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при перемещении: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить список доступных переходов из локации.
     */
    public function exits(Request $request, Location $location): JsonResponse
    {
        $character = $this->getActiveCharacter($request);

        if (! $character) {
            return response()->json([
                'error' => 'Активный персонаж не найден',
            ], 404);
        }

        $exits = $location->exits()->with('toLocation')->get();

        return response()->json([
            'location' => $this->formatLocation($location),
            'exits' => $this->formatExits($exits, $character),
        ]);
    }

    /**
     * Получить карту мира вокруг текущей позиции.
     */
    public function map(Request $request): JsonResponse
    {
        $character = $this->getActiveCharacter($request);

        if (! $character) {
            return response()->json([
                'error' => 'Активный персонаж не найден',
            ], 404);
        }

        $currentLocation = $character->location;

        if (! $currentLocation) {
            return response()->json([
                'error' => 'Персонаж не находится ни в одной локации',
            ], 404);
        }

        // Получаем радиус из query параметра, по умолчанию 2
        $radius = (int) $request->query('radius', 2);
        // Ограничиваем радиус от 1 до 5
        $radius = max(1, min(5, $radius));

        $nearbyLocations = $currentLocation->getNearbyLocations($radius);

        $map = [];
        $centerX = $currentLocation->coordinate_x;
        $centerY = $currentLocation->coordinate_y;

        // Создаем сетку карты
        for ($y = $centerY - $radius; $y <= $centerY + $radius; $y++) {
            for ($x = $centerX - $radius; $x <= $centerX + $radius; $x++) {
                $location = $nearbyLocations->first(function ($loc) use ($x, $y) {
                    return $loc->coordinate_x === $x && $loc->coordinate_y === $y;
                });

                if ($location) {
                    $map[$y][$x] = [
                        'id' => $location->id,
                        'name' => $location->name,
                        'type' => $location->type,
                        'is_safe_zone' => $location->is_safe_zone,
                    ];
                } elseif ($x === $centerX && $y === $centerY) {
                    // Текущая локация всегда должна быть на карте
                    $map[$y][$x] = [
                        'id' => $currentLocation->id,
                        'name' => $currentLocation->name,
                        'type' => $currentLocation->type,
                        'is_safe_zone' => $currentLocation->is_safe_zone,
                        'is_current' => true,
                    ];
                } else {
                    $map[$y][$x] = null;
                }
            }
        }

        return response()->json([
            'center' => [
                'x' => $centerX,
                'y' => $centerY,
            ],
            'radius' => $radius,
            'map' => $map,
            'current_location' => $this->formatLocation($currentLocation),
        ]);
    }

    /**
     * Получить список предметов в локации.
     */
    public function items(Request $request, Location $location): JsonResponse
    {
        $items = ItemInstance::where('location_type', 'ground')
            ->where('location_id', $location->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('item')
            ->get();

        return response()->json([
            'location' => $this->formatLocation($location),
            'items' => $this->formatItems($items),
            'count' => $items->count(),
        ]);
    }

    /**
     * Получить список онлайн игроков в локации.
     */
    public function players(Request $request, Location $location): JsonResponse
    {
        $onlinePlayers = $location->onlinePlayers()
            ->with('character:id,name,level,location_id')
            ->get();

        $players = $onlinePlayers->map(function ($presence) {
            $character = $presence->character;

            return [
                'id' => $character->id,
                'name' => $character->name,
                'level' => $character->level,
                'status' => $presence->status,
                'is_visible' => $presence->is_visible,
                'last_action_at' => $presence->last_action_at?->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
            ],
            'players' => $players,
            'count' => $players->count(),
        ]);
    }

    /**
     * Получить список призраков в локации.
     * GET /api/location/{id}/ghosts
     */
    public function ghosts(Request $request, Location $location): JsonResponse
    {
        $ghosts = GhostState::where('location_id', $location->id)
            ->where('is_visible', true)
            ->with('character:id,name,level,location_id')
            ->get();

        $ghostsList = $ghosts->map(function ($ghostState) {
            $character = $ghostState->character;

            return [
                'id' => $ghostState->id,
                'character_id' => $character->id,
                'character_name' => $character->name,
                'is_visible' => $ghostState->is_visible,
                'created_at' => $ghostState->created_at->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
            ],
            'ghosts' => $ghostsList,
            'count' => $ghostsList->count(),
        ]);
    }

    /**
     * Получить список трупов в локации.
     * GET /api/location/{id}/corpses
     */
    public function corpses(Request $request, Location $location): JsonResponse
    {
        $character = $this->getActiveCharacter($request);

        $corpses = CorpseContainer::where('location_id', $location->id)
            ->where('expires_at', '>', now())
            ->with('character:id,name')
            ->get();

        $corpsesList = $corpses->map(function ($corpse) use ($character) {
            $itemsData = $corpse->items_data ?? [];
            $totalItems = count($itemsData['inventory'] ?? []) + count($itemsData['equipment'] ?? []);

            return [
                'id' => $corpse->id,
                'character_id' => $corpse->character_id,
                'character_name' => $corpse->character->name,
                'corpse_type' => $corpse->corpse_type,
                'expires_at' => $corpse->expires_at->toIso8601String(),
                'remaining_minutes' => $corpse->getRemainingMinutes(),
                'is_looted' => $corpse->is_looted,
                'total_items' => $totalItems,
                'belongs_to_me' => $character ? $corpse->belongsToCharacter($character) : false,
            ];
        })->values();

        return response()->json([
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
            ],
            'corpses' => $corpsesList,
            'count' => $corpsesList->count(),
        ]);
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

    /**
     * Форматировать данные локации для ответа.
     */
    private function formatLocation(Location $location): array
    {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'description' => $location->description,
            'type' => $location->type,
            'is_safe_zone' => $location->is_safe_zone,
            'min_level' => $location->min_level,
            'max_level' => $location->max_level,
            'coordinate_x' => $location->coordinate_x,
            'coordinate_y' => $location->coordinate_y,
            'image_url' => $location->image_url,
            'background_music' => $location->background_music,
        ];
    }

    /**
     * Форматировать данные выходов для ответа.
     */
    private function formatExits($exits, Character $character): array
    {
        return $exits->map(function (LocationExit $exit) use ($character) {
            $accessibility = $exit->isAccessible($character);
            $toLocation = $exit->toLocation;

            return [
                'id' => $exit->id,
                'direction' => $exit->direction,
                'custom_name' => $exit->custom_name,
                'description' => $exit->description,
                'is_locked' => $exit->is_locked,
                'is_accessible' => $accessibility['accessible'],
                'accessibility_errors' => $accessibility['errors'] ?? [],
                'to_location' => $toLocation ? [
                    'id' => $toLocation->id,
                    'name' => $toLocation->name,
                    'type' => $toLocation->type,
                    'is_safe_zone' => $toLocation->is_safe_zone,
                ] : null,
            ];
        })->values()->toArray();
    }

    /**
     * Форматировать данные предметов для ответа.
     */
    private function formatItems($items): array
    {
        return $items->map(function ($itemInstance) {
            $item = $itemInstance->item;

            if (! $item) {
                return null;
            }

            return [
                'id' => $itemInstance->id,
                'item_id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'type' => $item->type,
                'subtype' => $item->subtype,
                'rarity' => $item->rarity,
                'stackable' => $item->stackable,
                'quantity' => $itemInstance->quantity,
                'durability_current' => $itemInstance->durability_current,
                'position_x' => $itemInstance->position_x,
                'position_y' => $itemInstance->position_y,
                'expires_at' => $itemInstance->expires_at?->toIso8601String(),
                'expires_in' => $itemInstance->expires_at ? now()->diffInSeconds($itemInstance->expires_at) : null,
            ];
        })->filter()->values()->toArray();
    }

    /**
     * Форматировать данные NPC для ответа.
     */
    private function formatNpcs($npcs): array
    {
        return $npcs->map(function (ActiveNpc $activeNpc) {
            $npc = $activeNpc->npc;
            $stats = $npc->stats;

            if (! $npc) {
                return null;
            }

            $healthPercentage = $activeNpc->getHealthPercentage();
            $manaPercentage = $activeNpc->getManaPercentage();

            return [
                'id' => $activeNpc->id,
                'npc_id' => $npc->id,
                'name' => $npc->name,
                'description' => $npc->description,
                'type' => $npc->type,
                'is_hostile' => $npc->is_hostile,
                'is_merchant' => $npc->is_merchant,
                'is_teacher' => $npc->is_teacher,
                'is_quest_giver' => $npc->is_quest_giver,
                'ai_behavior' => $npc->ai_behavior,
                'level' => $stats?->level ?? 1,
                'health_current' => $activeNpc->health_current,
                'health_max' => $stats?->health_max ?? 100,
                'health_percentage' => round($healthPercentage, 1),
                'mana_current' => $activeNpc->mana_current,
                'mana_max' => $stats?->mana_max ?? 50,
                'mana_percentage' => round($manaPercentage, 1),
                'spawned_at' => $activeNpc->spawned_at?->toIso8601String(),
            ];
        })->filter()->values()->toArray();
    }

    /**
     * Форматировать данные трупов для ответа.
     */
    private function formatCorpses($corpses, ?Character $character = null): array
    {
        return $corpses->map(function (CorpseContainer $corpse) use ($character) {
            $itemsData = $corpse->items_data ?? [];
            $totalItems = count($itemsData['inventory'] ?? []) + count($itemsData['equipment'] ?? []);

            return [
                'id' => $corpse->id,
                'character_id' => $corpse->character_id,
                'character_name' => $corpse->character->name,
                'corpse_type' => $corpse->corpse_type,
                'expires_at' => $corpse->expires_at->toIso8601String(),
                'remaining_minutes' => $corpse->getRemainingMinutes(),
                'is_looted' => $corpse->is_looted,
                'total_items' => $totalItems,
                'belongs_to_me' => $character ? $corpse->belongsToCharacter($character) : false,
            ];
        })->values()->toArray();
    }

    /**
     * Форматировать данные призраков для ответа.
     */
    private function formatGhosts($ghosts): array
    {
        return $ghosts->map(function (GhostState $ghostState) {
            $character = $ghostState->character;

            return [
                'id' => $ghostState->id,
                'character_id' => $character->id,
                'character_name' => $character->name,
                'level' => $character->level,
                'is_visible' => $ghostState->is_visible,
                'created_at' => $ghostState->created_at->toIso8601String(),
            ];
        })->values()->toArray();
    }

    /**
     * Форматировать данные игроков для ответа.
     */
    private function formatPlayers($players, ?Character $currentCharacter = null): array
    {
        return $players->map(function ($presence) use ($currentCharacter) {
            $character = $presence->character;
            $isCurrentPlayer = $currentCharacter && $character->id === $currentCharacter->id;

            return [
                'id' => $character->id,
                'name' => $character->name,
                'level' => $character->level,
                'status' => $presence->status,
                'is_visible' => $presence->is_visible,
                'is_current_player' => $isCurrentPlayer,
                'last_action_at' => $presence->last_action_at?->toIso8601String(),
            ];
        })->filter(function ($player) {
            // Исключаем текущего игрока из списка
            return ! $player['is_current_player'];
        })->values()->toArray();
    }

    /**
     * Форматировать данные камней воскрешения для ответа.
     */
    private function formatResurrectionStones($stones, ?Character $character = null): array
    {
        $isGhost = $character && $character->isGhost();

        return $stones->map(function (ResurrectionStone $stone) use ($isGhost, $character) {
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
        })->values()->toArray();
    }
}
