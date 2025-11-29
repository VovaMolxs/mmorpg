<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMonsterSpawnRequest;
use App\Http\Requests\Admin\UpdateMonsterSpawnRequest;
use App\Models\Location;
use App\Models\Monster;
use App\Models\MonsterSpawn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MonsterSpawnController extends Controller
{
    /**
     * Display a listing of monster spawns.
     */
    public function index(Request $request): View
    {
        $query = MonsterSpawn::with(['monster', 'location', 'activeMonsters'])->latest();

        if ($request->has('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->has('monster_id')) {
            $query->where('monster_id', $request->get('monster_id'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $spawns = $query->paginate(20);

        $locations = Location::orderBy('name')->get();
        $monsters = Monster::with('stats')->orderBy('name')->get();

        return view('admin.monster-spawns.index', [
            'spawns' => $spawns,
            'locations' => $locations,
            'monsters' => $monsters,
        ]);
    }

    /**
     * Show the form for creating a new monster spawn.
     */
    public function create(): View
    {
        $locations = Location::orderBy('name')->get();
        $monsters = Monster::with('stats')->orderBy('name')->get();

        $ranks = [
            'normal' => 'Обычный',
            'elite' => 'Элитный',
            'boss' => 'Босс',
            'world_boss' => 'Мировой босс',
        ];

        return view('admin.monster-spawns.create', [
            'locations' => $locations,
            'monsters' => $monsters,
            'ranks' => $ranks,
        ]);
    }

    /**
     * Store a newly created monster spawn.
     */
    public function store(StoreMonsterSpawnRequest $request): RedirectResponse
    {
        try {
            $spawn = DB::transaction(function () use ($request) {
                $data = [
                    'monster_id' => $request->monster_id,
                    'location_id' => $request->location_id,
                    'min_instances' => $request->min_instances,
                    'max_instances' => $request->max_instances,
                    'respawn_time_min' => $request->respawn_time_min,
                    'respawn_time_max' => $request->respawn_time_max,
                    'spawn_chance' => $request->spawn_chance,
                    'is_active' => $request->boolean('is_active', true),
                    'spawn_radius' => $request->spawn_radius ?? 0,
                ];

                // Обработка расписания спавна
                if ($request->has('spawn_schedule')) {
                    $schedule = $request->input('spawn_schedule');
                    if (! empty($schedule['type'])) {
                        $scheduleData = ['type' => $schedule['type']];
                        if ($schedule['type'] === 'custom' && isset($schedule['hours'])) {
                            $scheduleData['hours'] = array_map('intval', $schedule['hours']);
                        }
                        $data['spawn_schedule'] = $scheduleData;
                    }
                }

                return MonsterSpawn::create($data);
            });

            return redirect()->route('admin.monster-spawns.index')
                ->with('success', 'Настройка спавна монстра успешно создана!');
        } catch (\Exception $e) {
            Log::error('Monster spawn creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании настройки спавна: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified monster spawn.
     */
    public function edit(int $id): View
    {
        $monsterSpawn = MonsterSpawn::with(['monster', 'location', 'activeMonsters'])->findOrFail($id);

        $locations = Location::orderBy('name')->get();
        $monsters = Monster::with('stats')->orderBy('name')->get();

        $ranks = [
            'normal' => 'Обычный',
            'elite' => 'Элитный',
            'boss' => 'Босс',
            'world_boss' => 'Мировой босс',
        ];

        return view('admin.monster-spawns.edit', [
            'spawn' => $monsterSpawn,
            'locations' => $locations,
            'monsters' => $monsters,
            'ranks' => $ranks,
        ]);
    }

    /**
     * Update the specified monster spawn.
     */
    public function update(UpdateMonsterSpawnRequest $request, int $id): RedirectResponse
    {
        try {
            $monsterSpawn = MonsterSpawn::findOrFail($id);

            DB::transaction(function () use ($request, $monsterSpawn) {
                $data = [];

                if ($request->has('monster_id')) {
                    $data['monster_id'] = $request->monster_id;
                }
                if ($request->has('location_id')) {
                    $data['location_id'] = $request->location_id;
                }
                if ($request->has('min_instances')) {
                    $data['min_instances'] = $request->min_instances;
                }
                if ($request->has('max_instances')) {
                    $data['max_instances'] = $request->max_instances;
                }
                if ($request->has('respawn_time_min')) {
                    $data['respawn_time_min'] = $request->respawn_time_min;
                }
                if ($request->has('respawn_time_max')) {
                    $data['respawn_time_max'] = $request->respawn_time_max;
                }
                if ($request->has('spawn_chance')) {
                    $data['spawn_chance'] = $request->spawn_chance;
                }
                if ($request->has('is_active')) {
                    $data['is_active'] = $request->boolean('is_active');
                }
                if ($request->has('spawn_radius')) {
                    $data['spawn_radius'] = $request->spawn_radius ?? 0;
                }

                // Обработка расписания спавна
                if ($request->has('spawn_schedule')) {
                    $schedule = $request->input('spawn_schedule');
                    if (! empty($schedule['type'])) {
                        $scheduleData = ['type' => $schedule['type']];
                        if ($schedule['type'] === 'custom' && isset($schedule['hours'])) {
                            $scheduleData['hours'] = array_map('intval', $schedule['hours']);
                        }
                        $data['spawn_schedule'] = $scheduleData;
                    } else {
                        $data['spawn_schedule'] = null;
                    }
                }

                $monsterSpawn->update($data);
            });

            return redirect()->route('admin.monster-spawns.index')
                ->with('success', 'Настройка спавна монстра успешно обновлена!');
        } catch (\Exception $e) {
            Log::error('Monster spawn update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'spawn_id' => $monsterSpawn->id,
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении настройки спавна: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified monster spawn.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $monsterSpawn = MonsterSpawn::findOrFail($id);

            $activeCount = 0;
            DB::transaction(function () use ($monsterSpawn, &$activeCount) {
                // Получаем все активные монстры от этого спавна
                $activeMonsters = $monsterSpawn->allActiveMonsters()->get();
                $activeCount = $activeMonsters->count();

                // Деактивируем всех активных монстров от этого спавна
                if ($activeCount > 0) {
                    foreach ($activeMonsters as $monster) {
                        // Деактивируем монстра
                        $monster->setDead();
                        $monster->save();
                    }
                }

                // Удаляем спавн
                $monsterSpawn->delete();
            });

            $message = 'Настройка спавна монстра успешно удалена!';
            if ($activeCount > 0) {
                $message .= " Деактивировано {$activeCount} активных монстров.";
            }

            return redirect()->route('admin.monster-spawns.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Monster spawn deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'spawn_id' => $id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении настройки спавна: '.$e->getMessage()]);
        }
    }
}
