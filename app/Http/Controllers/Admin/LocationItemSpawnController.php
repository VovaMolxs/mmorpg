<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLocationItemSpawnRequest;
use App\Http\Requests\Admin\UpdateLocationItemSpawnRequest;
use App\Models\Item;
use App\Models\Location;
use App\Models\LocationItemSpawn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LocationItemSpawnController extends Controller
{
    /**
     * Display a listing of location item spawns.
     */
    public function index(Request $request): View
    {
        $query = LocationItemSpawn::with(['location', 'item'])->latest();

        if ($request->has('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->has('item_id')) {
            $query->where('item_id', $request->get('item_id'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $spawns = $query->paginate(20);

        $locations = Location::orderBy('name')->get();
        $items = Item::orderBy('name')->get();

        return view('admin.item-spawns.index', [
            'spawns' => $spawns,
            'locations' => $locations,
            'items' => $items,
        ]);
    }

    /**
     * Show the form for creating a new location item spawn.
     */
    public function create(): View
    {
        $locations = Location::orderBy('name')->get();
        $items = Item::orderBy('name')->get();

        return view('admin.item-spawns.create', [
            'locations' => $locations,
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created location item spawn.
     */
    public function store(StoreLocationItemSpawnRequest $request): RedirectResponse
    {
        try {
            $spawn = DB::transaction(function () use ($request) {
                return LocationItemSpawn::create([
                    'location_id' => $request->location_id,
                    'item_id' => $request->item_id,
                    'min_quantity' => $request->min_quantity,
                    'max_quantity' => $request->max_quantity,
                    'respawn_time_min' => $request->respawn_time_min,
                    'respawn_time_max' => $request->respawn_time_max,
                    'max_instances' => $request->max_instances,
                    'spawn_chance' => $request->spawn_chance,
                    'is_active' => $request->boolean('is_active', true),
                ]);
            });

            return redirect()->route('admin.item-spawns.index')
                ->with('success', 'Настройка спавна успешно создана!');
        } catch (\Exception $e) {
            Log::error('Location item spawn creation failed', [
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
     * Show the form for editing the specified location item spawn.
     */
    public function edit(LocationItemSpawn $item_spawn): View
    {
        $locations = Location::orderBy('name')->get();
        $items = Item::orderBy('name')->get();

        return view('admin.item-spawns.edit', [
            'spawn' => $item_spawn,
            'locations' => $locations,
            'items' => $items,
        ]);
    }

    /**
     * Update the specified location item spawn.
     */
    public function update(UpdateLocationItemSpawnRequest $request, LocationItemSpawn $item_spawn): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $item_spawn) {
                $item_spawn->update([
                    'location_id' => $request->location_id,
                    'item_id' => $request->item_id,
                    'min_quantity' => $request->min_quantity,
                    'max_quantity' => $request->max_quantity,
                    'respawn_time_min' => $request->respawn_time_min,
                    'respawn_time_max' => $request->respawn_time_max,
                    'max_instances' => $request->max_instances,
                    'spawn_chance' => $request->spawn_chance,
                    'is_active' => $request->boolean('is_active', true),
                ]);
            });

            return redirect()->route('admin.item-spawns.index')
                ->with('success', 'Настройка спавна успешно обновлена!');
        } catch (\Exception $e) {
            Log::error('Location item spawn update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'spawn_id' => $item_spawn->id,
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении настройки спавна: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified location item spawn.
     */
    public function destroy(LocationItemSpawn $item_spawn): RedirectResponse
    {
        try {
            $item_spawn->delete();

            return redirect()->route('admin.item-spawns.index')
                ->with('success', 'Настройка спавна успешно удалена!');
        } catch (\Exception $e) {
            Log::error('Location item spawn deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'spawn_id' => $item_spawn->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении настройки спавна: '.$e->getMessage()]);
        }
    }
}
