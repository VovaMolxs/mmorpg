<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLocationRequest;
use App\Http\Requests\Admin\UpdateLocationRequest;
use App\Models\Location;
use App\Models\LocationExit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WorldMapController extends Controller
{
    /**
     * Display the world map editor.
     */
    public function index(): View
    {
        // Получаем все локации
        $locations = Location::orderBy('coordinate_y')
            ->orderBy('coordinate_x')
            ->get()
            ->keyBy(function ($location) {
                return "{$location->coordinate_x}_{$location->coordinate_y}";
            });

        return view('admin.world-map.index', [
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created location.
     */
    public function store(StoreLocationRequest $request): JsonResponse|RedirectResponse
    {
        try {
            // Проверяем, не существует ли уже локация на этих координатах
            $existingLocation = Location::where('coordinate_x', $request->coordinate_x)
                ->where('coordinate_y', $request->coordinate_y)
                ->first();

            if ($existingLocation) {
                return response()->json([
                    'error' => 'Локация на этих координатах уже существует',
                ], 422);
            }

            $location = DB::transaction(function () use ($request) {
                return Location::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'type' => $request->type,
                    'is_safe_zone' => $request->boolean('is_safe_zone', false),
                    'min_level' => $request->min_level,
                    'max_level' => $request->max_level,
                    'coordinate_x' => $request->coordinate_x,
                    'coordinate_y' => $request->coordinate_y,
                    'image_url' => $request->image_url,
                    'background_music' => $request->background_music,
                ]);
            });

            // Если переданы выходы, создаем их
            $exits = $request->input('exits', []);
            if (! empty($exits)) {
                $this->createExitsForLocation($location, $exits);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Локация успешно создана',
                    'location' => $location,
                ], 201);
            }

            return redirect()->route('admin.world-map.index')
                ->with('success', 'Локация успешно создана');
        } catch (\Exception $e) {
            Log::error('Failed to create location', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Произошла ошибка при создании локации: '.$e->getMessage(),
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании локации: '.$e->getMessage()]);
        }
    }

    /**
     * Update the specified location.
     */
    public function update(UpdateLocationRequest $request, Location $location): JsonResponse|RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $location) {
                $isSafeZone = false;
                if ($request->has('is_safe_zone')) {
                    $isSafeZone = $request->boolean('is_safe_zone') 
                        || $request->input('is_safe_zone') == 1 
                        || $request->input('is_safe_zone') === '1'
                        || $request->input('is_safe_zone') === true;
                }

                $location->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'type' => $request->type,
                    'is_safe_zone' => $isSafeZone,
                    'min_level' => $request->min_level,
                    'max_level' => $request->max_level,
                    'image_url' => $request->image_url,
                    'background_music' => $request->background_music,
                ]);
            });

            $location->refresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Локация успешно обновлена',
                    'location' => $location,
                ]);
            }

            return redirect()->route('admin.world-map.index')
                ->with('success', 'Локация успешно обновлена');
        } catch (\Exception $e) {
            Log::error('Failed to update location', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'location_id' => $location->id,
                'data' => $request->all(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Произошла ошибка при обновлении локации: '.$e->getMessage(),
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении локации: '.$e->getMessage()]);
        }
    }

    /**
     * Get location data for editing.
     */
    public function show(Location $location): JsonResponse
    {
        $location->load('exits.toLocation');

        return response()->json([
            'location' => $location,
            'exits' => $location->exits->map(function ($exit) {
                return [
                    'id' => $exit->id,
                    'direction' => $exit->direction,
                    'custom_name' => $exit->custom_name,
                    'description' => $exit->description,
                    'is_locked' => $exit->is_locked,
                    'to_location_id' => $exit->to_location_id,
                    'to_location' => $exit->toLocation ? [
                        'id' => $exit->toLocation->id,
                        'name' => $exit->toLocation->name,
                        'coordinate_x' => $exit->toLocation->coordinate_x,
                        'coordinate_y' => $exit->toLocation->coordinate_y,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Create exits for a location.
     */
    private function createExitsForLocation(Location $location, array $exits): void
    {
        foreach ($exits as $exitData) {
            if (empty($exitData['direction']) && empty($exitData['custom_name'])) {
                continue;
            }

            $toLocation = $this->findTargetLocation($location, $exitData);

            if (! $toLocation) {
                continue;
            }

            LocationExit::create([
                'from_location_id' => $location->id,
                'to_location_id' => $toLocation->id,
                'direction' => $exitData['direction'] ?? null,
                'custom_name' => $exitData['custom_name'] ?? null,
                'description' => $exitData['description'] ?? null,
                'is_locked' => $exitData['is_locked'] ?? false,
                'required_item_id' => $exitData['required_item_id'] ?? null,
                'required_skill' => $exitData['required_skill'] ?? null,
                'required_skill_level' => $exitData['required_skill_level'] ?? null,
            ]);
        }
    }

    /**
     * Find target location for exit.
     */
    private function findTargetLocation(Location $fromLocation, array $exitData): ?Location
    {
        // Используем выбранную локацию по ID
        if (isset($exitData['to_location_id']) && $exitData['to_location_id']) {
            return Location::find($exitData['to_location_id']);
        }

        // Ищем локацию по координатам
        if (isset($exitData['to_coordinate_x']) && isset($exitData['to_coordinate_y'])
            && $exitData['to_coordinate_x'] !== '' && $exitData['to_coordinate_y'] !== '') {
            return Location::where('coordinate_x', $exitData['to_coordinate_x'])
                ->where('coordinate_y', $exitData['to_coordinate_y'])
                ->first();
        }

        // Определяем по направлению (соседние клетки)
        $direction = $exitData['direction'] ?? null;
        if ($direction) {
            $x = $fromLocation->coordinate_x;
            $y = $fromLocation->coordinate_y;

            switch ($direction) {
                case 'north':
                    $y--;
                    break;
                case 'south':
                    $y++;
                    break;
                case 'east':
                    $x++;
                    break;
                case 'west':
                    $x--;
                    break;
            }

            return Location::where('coordinate_x', $x)
                ->where('coordinate_y', $y)
                ->first();
        }

        return null;
    }

    /**
     * Update location exits.
     */
    public function updateExits(Location $location): JsonResponse|RedirectResponse
    {
        try {
            $exits = request()->input('exits', []);

            DB::transaction(function () use ($location, $exits) {
                // Удаляем все существующие выходы
                $location->exits()->delete();

                // Создаем новые выходы
                $this->createExitsForLocation($location, $exits);
            });

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Выходы успешно обновлены',
                ]);
            }

            return redirect()->route('admin.world-map.index')
                ->with('success', 'Выходы успешно обновлены');
        } catch (\Exception $e) {
            Log::error('Failed to update location exits', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'location_id' => $location->id,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Произошла ошибка при обновлении выходов: '.$e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Произошла ошибка при обновлении выходов: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified location.
     */
    public function destroy(Location $location): JsonResponse|RedirectResponse
    {
        try {
            // Проверяем, есть ли персонажи в этой локации
            if ($location->characters()->count() > 0) {
                $message = 'Невозможно удалить локацию, в которой находятся персонажи';
                if (request()->expectsJson()) {
                    return response()->json(['error' => $message], 422);
                }

                return back()->withErrors(['error' => $message]);
            }

            $location->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Локация успешно удалена',
                ]);
            }

            return redirect()->route('admin.world-map.index')
                ->with('success', 'Локация успешно удалена');
        } catch (\Exception $e) {
            Log::error('Failed to delete location', [
                'error' => $e->getMessage(),
                'location_id' => $location->id,
            ]);

            $message = 'Произошла ошибка при удалении локации: '.$e->getMessage();
            if (request()->expectsJson()) {
                return response()->json(['error' => $message], 500);
            }

            return back()->withErrors(['error' => $message]);
        }
    }
}
