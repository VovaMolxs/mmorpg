<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreResurrectionStoneRequest;
use App\Http\Requests\Admin\UpdateResurrectionStoneRequest;
use App\Models\Location;
use App\Models\ResurrectionStone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ResurrectionStoneController extends Controller
{
    /**
     * Display a listing of resurrection stones.
     */
    public function index(Request $request): View
    {
        $query = ResurrectionStone::with('location')->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('location', function ($locationQuery) use ($search) {
                        $locationQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $stones = $query->paginate(20);
        $locations = Location::orderBy('name')->get();

        return view('admin.resurrection-stones.index', [
            'stones' => $stones,
            'locations' => $locations,
        ]);
    }

    /**
     * Show the form for creating a new resurrection stone.
     */
    public function create(): View
    {
        $locations = Location::orderBy('name')->get();

        return view('admin.resurrection-stones.create', [
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created resurrection stone.
     */
    public function store(StoreResurrectionStoneRequest $request): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();

                // Обработка checkbox is_active
                $isActive = isset($validated['is_active']) ? (bool) $validated['is_active'] : true;

                $stone = ResurrectionStone::create([
                    'location_id' => $validated['location_id'],
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'level_required' => $validated['level_required'],
                    'is_active' => $isActive,
                    'cooldown_minutes' => $validated['cooldown_minutes'],
                    'visual_effect' => $validated['visual_effect'],
                ]);

                return redirect()->route('admin.resurrection-stones.index')
                    ->with('success', 'Камень воскрешения успешно создан!');
            });
        } catch (\Exception $e) {
            Log::error('Resurrection stone creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании камня воскрешения: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resurrection stone.
     */
    public function edit(ResurrectionStone $resurrectionStone): View
    {
        $resurrectionStone->load('location');
        $locations = Location::orderBy('name')->get();

        return view('admin.resurrection-stones.edit', [
            'stone' => $resurrectionStone,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified resurrection stone.
     */
    public function update(UpdateResurrectionStoneRequest $request, ResurrectionStone $resurrectionStone): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request, $resurrectionStone) {
                $validated = $request->validated();

                // Обработка checkbox is_active
                if (isset($validated['is_active'])) {
                    $validated['is_active'] = (bool) $validated['is_active'];
                } else {
                    $validated['is_active'] = false;
                }

                $resurrectionStone->update(array_filter($validated, function ($value) {
                    return $value !== null;
                }));

                return redirect()->route('admin.resurrection-stones.index')
                    ->with('success', 'Камень воскрешения успешно обновлен!');
            });
        } catch (\Exception $e) {
            Log::error('Resurrection stone update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stone_id' => $resurrectionStone->id,
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении камня воскрешения: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified resurrection stone.
     */
    public function destroy(ResurrectionStone $resurrectionStone): RedirectResponse
    {
        try {
            $resurrectionStone->delete();

            return redirect()->route('admin.resurrection-stones.index')
                ->with('success', 'Камень воскрешения успешно удален!');
        } catch (\Exception $e) {
            Log::error('Resurrection stone deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stone_id' => $resurrectionStone->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении камня воскрешения: '.$e->getMessage()]);
        }
    }
}
