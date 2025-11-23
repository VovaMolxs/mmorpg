<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveLocationRequest;
use App\Models\Character;
use App\Models\Location;
use App\Models\LocationExit;
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

        return response()->json([
            'location' => $this->formatLocation($location),
            'exits' => $this->formatExits($location->exits, $character),
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

            return response()->json([
                'success' => true,
                'message' => 'Вы успешно переместились',
                'location' => $this->formatLocation($newLocation),
                'exits' => $this->formatExits($newLocation->exits, $character),
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
}
