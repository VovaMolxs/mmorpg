<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBankerRequest;
use App\Http\Requests\Admin\UpdateBankerRequest;
use App\Models\Banker;
use App\Models\Location;
use App\Models\Npc;
use App\Models\NpcSpawn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BankerController extends Controller
{
    /**
     * Display a listing of bankers.
     */
    public function index(Request $request): View
    {
        $query = Banker::with(['npc', 'location'])->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->whereHas('npc', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        $bankers = $query->paginate(20);
        $locations = Location::orderBy('name')->get();

        return view('admin.bankers.index', [
            'bankers' => $bankers,
            'locations' => $locations,
        ]);
    }

    /**
     * Show the form for creating a new banker.
     */
    public function create(): View
    {
        $npcs = Npc::whereDoesntHave('banker')
            ->orderBy('name')
            ->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.bankers.create', [
            'npcs' => $npcs,
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created banker.
     */
    public function store(StoreBankerRequest $request): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();

                // Создаем банкира
                $banker = Banker::create([
                    'npc_id' => $validated['npc_id'],
                    'location_id' => $validated['location_id'],
                    'storage_slots' => $validated['storage_slots'],
                    'base_fee' => $validated['base_fee'],
                    'fee_per_slot' => $validated['fee_per_slot'],
                    'max_upgrade_slots' => $validated['max_upgrade_slots'],
                ]);

                // Создаем спавн для банкира (банкиры всегда должны быть активны)
                NpcSpawn::firstOrCreate(
                    [
                        'npc_id' => $validated['npc_id'],
                        'location_id' => $validated['location_id'],
                    ],
                    [
                        'min_instances' => 1,
                        'max_instances' => 1,
                        'respawn_time_min' => 1,
                        'respawn_time_max' => 5,
                        'spawn_chance' => 100,
                        'is_active' => true,
                        'spawn_radius' => 0,
                        'spawn_schedule' => ['type' => 'always'],
                    ]
                );

                return redirect()->route('admin.bankers.index')
                    ->with('success', 'Банкир успешно создан!');
            });
        } catch (\Exception $e) {
            Log::error('Banker creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании банкира: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified banker.
     */
    public function edit(Banker $banker): View
    {
        $banker->load(['npc', 'location']);
        $npcs = Npc::whereDoesntHave('banker')
            ->orWhere('id', $banker->npc_id)
            ->orderBy('name')
            ->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.bankers.edit', [
            'banker' => $banker,
            'npcs' => $npcs,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified banker.
     */
    public function update(UpdateBankerRequest $request, Banker $banker): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request, $banker) {
                $validated = $request->validated();
                $oldNpcId = $banker->npc_id;
                $oldLocationId = $banker->location_id;

                // Обновляем банкира
                $banker->update([
                    'npc_id' => $validated['npc_id'],
                    'location_id' => $validated['location_id'],
                    'storage_slots' => $validated['storage_slots'],
                    'base_fee' => $validated['base_fee'],
                    'fee_per_slot' => $validated['fee_per_slot'],
                    'max_upgrade_slots' => $validated['max_upgrade_slots'],
                ]);

                // Если изменились NPC или локация, обновляем спавн
                if ($oldNpcId != $validated['npc_id'] || $oldLocationId != $validated['location_id']) {
                    // Удаляем старый спавн, если он был только для этого банкира
                    $oldSpawn = NpcSpawn::where('npc_id', $oldNpcId)
                        ->where('location_id', $oldLocationId)
                        ->first();

                    if ($oldSpawn) {
                        // Проверяем, есть ли другие банкиры с таким же NPC и локацией
                        $otherBankers = Banker::where('npc_id', $oldNpcId)
                            ->where('location_id', $oldLocationId)
                            ->where('id', '!=', $banker->id)
                            ->exists();

                        if (! $otherBankers) {
                            $oldSpawn->delete();
                        }
                    }

                    // Создаем или обновляем новый спавн
                    NpcSpawn::firstOrCreate(
                        [
                            'npc_id' => $validated['npc_id'],
                            'location_id' => $validated['location_id'],
                        ],
                        [
                            'min_instances' => 1,
                            'max_instances' => 1,
                            'respawn_time_min' => 1,
                            'respawn_time_max' => 5,
                            'spawn_chance' => 100,
                            'is_active' => true,
                            'spawn_radius' => 0,
                            'spawn_schedule' => ['type' => 'always'],
                        ]
                    );
                } else {
                    // Обновляем существующий спавн, чтобы убедиться, что он активен
                    $spawn = NpcSpawn::where('npc_id', $validated['npc_id'])
                        ->where('location_id', $validated['location_id'])
                        ->first();

                    if ($spawn) {
                        $spawn->update([
                            'min_instances' => 1,
                            'max_instances' => 1,
                            'is_active' => true,
                        ]);
                    }
                }

                return redirect()->route('admin.bankers.index')
                    ->with('success', 'Банкир успешно обновлен!');
            });
        } catch (\Exception $e) {
            Log::error('Banker update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'banker_id' => $banker->id,
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении банкира: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified banker.
     */
    public function destroy(Banker $banker): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($banker) {
                $npcId = $banker->npc_id;
                $locationId = $banker->location_id;

                // Удаляем банкира
                $banker->delete();

                // Проверяем, есть ли другие банкиры с таким же NPC и локацией
                $otherBankers = Banker::where('npc_id', $npcId)
                    ->where('location_id', $locationId)
                    ->exists();

                // Если нет других банкиров, удаляем или деактивируем спавн
                if (! $otherBankers) {
                    $spawn = NpcSpawn::where('npc_id', $npcId)
                        ->where('location_id', $locationId)
                        ->first();

                    if ($spawn) {
                        // Проверяем, есть ли активные NPC от этого спавна
                        $activeCount = $spawn->activeNpcs()->count();
                        if ($activeCount > 0) {
                            // Деактивируем спавн вместо удаления
                            $spawn->update(['is_active' => false]);
                        } else {
                            // Удаляем спавн, если нет активных NPC
                            $spawn->delete();
                        }
                    }
                }

                return redirect()->route('admin.bankers.index')
                    ->with('success', 'Банкир успешно удален!');
            });
        } catch (\Exception $e) {
            Log::error('Banker deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'banker_id' => $banker->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении банкира: '.$e->getMessage()]);
        }
    }
}
