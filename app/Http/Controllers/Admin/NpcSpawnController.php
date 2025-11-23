<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNpcSpawnRequest;
use App\Http\Requests\Admin\UpdateNpcSpawnRequest;
use App\Models\Location;
use App\Models\Npc;
use App\Models\NpcSpawn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class NpcSpawnController extends Controller
{
    /**
     * Display a listing of NPC spawns.
     */
    public function index(Request $request): View
    {
        $query = NpcSpawn::with(['npc', 'location', 'activeNpcs'])->latest();

        if ($request->has('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->has('npc_id')) {
            $query->where('npc_id', $request->get('npc_id'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $spawns = $query->paginate(20);

        $locations = Location::orderBy('name')->get();
        $npcs = Npc::with('stats')->orderBy('name')->get();

        return view('admin.npc-spawns.index', [
            'spawns' => $spawns,
            'locations' => $locations,
            'npcs' => $npcs,
        ]);
    }

    /**
     * Show the form for creating a new NPC spawn.
     */
    public function create(): View
    {
        $locations = Location::orderBy('name')->get();
        $npcs = Npc::with('stats')->orderBy('name')->get();

        return view('admin.npc-spawns.create', [
            'locations' => $locations,
            'npcs' => $npcs,
        ]);
    }

    /**
     * Store a newly created NPC spawn.
     */
    public function store(StoreNpcSpawnRequest $request): RedirectResponse
    {
        try {
            $spawn = DB::transaction(function () use ($request) {
                $data = [
                    'npc_id' => $request->npc_id,
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

                return NpcSpawn::create($data);
            });

            return redirect()->route('admin.npc-spawns.index')
                ->with('success', 'Настройка спавна NPC успешно создана!');
        } catch (\Exception $e) {
            Log::error('NPC spawn creation failed', [
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
     * Show the form for editing the specified NPC spawn.
     */
    public function edit(NpcSpawn $npcSpawn): View
    {
        $npcSpawn->load(['npc', 'location', 'activeNpcs']);

        $locations = Location::orderBy('name')->get();
        $npcs = Npc::with('stats')->orderBy('name')->get();

        return view('admin.npc-spawns.edit', [
            'spawn' => $npcSpawn,
            'locations' => $locations,
            'npcs' => $npcs,
        ]);
    }

    /**
     * Update the specified NPC spawn.
     */
    public function update(UpdateNpcSpawnRequest $request, NpcSpawn $npcSpawn): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $npcSpawn) {
                $data = [];

                if ($request->has('npc_id')) {
                    $data['npc_id'] = $request->npc_id;
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

                $npcSpawn->update($data);
            });

            return redirect()->route('admin.npc-spawns.index')
                ->with('success', 'Настройка спавна NPC успешно обновлена!');
        } catch (\Exception $e) {
            Log::error('NPC spawn update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'spawn_id' => $npcSpawn->id,
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении настройки спавна: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified NPC spawn.
     */
    public function destroy(NpcSpawn $npcSpawn): RedirectResponse
    {
        try {
            // Проверяем, есть ли активные NPC от этого спавна
            $activeCount = $npcSpawn->activeNpcs()->count();

            if ($activeCount > 0) {
                return redirect()->route('admin.npc-spawns.index')
                    ->withErrors(['error' => "Невозможно удалить спавн: существует {$activeCount} активных NPC от этого спавна."]);
            }

            $npcSpawn->delete();

            return redirect()->route('admin.npc-spawns.index')
                ->with('success', 'Настройка спавна NPC успешно удалена!');
        } catch (\Exception $e) {
            Log::error('NPC spawn deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'spawn_id' => $npcSpawn->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении настройки спавна: '.$e->getMessage()]);
        }
    }
}
