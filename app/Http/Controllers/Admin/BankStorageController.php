<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankStorage;
use App\Models\Character;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BankStorageController extends Controller
{
    /**
     * Display a listing of bank storages.
     */
    public function index(Request $request): View
    {
        $query = BankStorage::with(['character', 'banker.npc', 'itemInstance.item'])
            ->latest();

        if ($request->has('character_id')) {
            $query->where('character_id', $request->get('character_id'));
        }

        if ($request->has('banker_id')) {
            $query->where('banker_id', $request->get('banker_id'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->whereHas('character', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $storages = $query->paginate(50);
        $characters = Character::orderBy('name')->get();

        return view('admin.bank-storages.index', [
            'storages' => $storages,
            'characters' => $characters,
        ]);
    }

    /**
     * Display bank storage for a specific character.
     */
    public function show(Character $character, Request $request): View
    {
        $bankerId = $request->get('banker_id');

        $query = BankStorage::where('character_id', $character->id)
            ->with(['banker.npc', 'banker.location', 'itemInstance.item']);

        if ($bankerId) {
            $query->where('banker_id', $bankerId);
        }

        $storages = $query->orderBy('banker_id')
            ->orderBy('slot_number')
            ->get()
            ->groupBy('banker_id');

        $bankers = \App\Models\Banker::with(['npc', 'location'])->get();

        return view('admin.bank-storages.show', [
            'character' => $character,
            'storages' => $storages,
            'bankers' => $bankers,
            'selectedBankerId' => $bankerId,
        ]);
    }

    /**
     * Remove item from bank storage.
     */
    public function destroy(BankStorage $bankStorage): RedirectResponse
    {
        try {
            DB::transaction(function () use ($bankStorage) {
                $character = $bankStorage->character;
                $itemInstance = $bankStorage->itemInstance;

                if ($itemInstance) {
                    // Перемещаем предмет в инвентарь персонажа
                    $itemInstance->location_type = 'inventory';
                    $itemInstance->location_id = $character->id;
                    $itemInstance->save();
                }

                // Очищаем ячейку хранилища
                $bankStorage->item_instance_id = null;
                $bankStorage->withdrawn_at = now();
                $bankStorage->save();
            });

            return redirect()->back()
                ->with('success', 'Предмет успешно изъят из хранилища!');
        } catch (\Exception $e) {
            Log::error('Bank storage item removal failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'storage_id' => $bankStorage->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при изъятии предмета: '.$e->getMessage()]);
        }
    }

    /**
     * Clear all items from bank storage for a character.
     */
    public function clear(Character $character, Request $request): RedirectResponse
    {
        try {
            $bankerId = $request->get('banker_id');

            DB::transaction(function () use ($character, $bankerId) {
                $query = BankStorage::where('character_id', $character->id)
                    ->whereNotNull('item_instance_id');

                if ($bankerId) {
                    $query->where('banker_id', $bankerId);
                }

                $storages = $query->with('itemInstance')->get();

                foreach ($storages as $storage) {
                    if ($storage->itemInstance) {
                        // Перемещаем предмет в инвентарь персонажа
                        $storage->itemInstance->location_type = 'inventory';
                        $storage->itemInstance->location_id = $character->id;
                        $storage->itemInstance->save();
                    }

                    // Очищаем ячейку хранилища
                    $storage->item_instance_id = null;
                    $storage->withdrawn_at = now();
                    $storage->save();
                }
            });

            $message = $bankerId
                ? 'Все предметы успешно изъяты из хранилища!'
                : 'Все предметы успешно изъяты из всех хранилищ персонажа!';

            return redirect()->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Bank storage clear failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'character_id' => $character->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при очистке хранилища: '.$e->getMessage()]);
        }
    }
}
