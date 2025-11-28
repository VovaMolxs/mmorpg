<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMerchantInventoryRequest;
use App\Http\Requests\Admin\UpdateMerchantInventoryRequest;
use App\Models\Item;
use App\Models\MerchantInventory;
use App\Models\Npc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MerchantInventoryController extends Controller
{
    /**
     * Display a listing of merchant inventories for a specific NPC.
     */
    public function index(Request $request, Npc $npc): View
    {
        if (! $npc->is_merchant) {
            abort(404, 'Этот NPC не является торговцем');
        }

        $query = MerchantInventory::where('npc_id', $npc->id)
            ->with('item')
            ->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        if ($request->has('buyable')) {
            if ($request->boolean('buyable')) {
                $query->where('base_sell_price', '>', 0);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('base_sell_price')
                        ->orWhere('base_sell_price', '=', 0);
                });
            }
        }

        $inventories = $query->paginate(20);

        return view('admin.merchant-inventories.index', [
            'npc' => $npc,
            'inventories' => $inventories,
        ]);
    }

    /**
     * Show the form for creating a new merchant inventory item.
     */
    public function create(Npc $npc): View
    {
        if (! $npc->is_merchant) {
            abort(404, 'Этот NPC не является торговцем');
        }

        $items = Item::orderBy('name')->get();

        return view('admin.merchant-inventories.create', [
            'npc' => $npc,
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created merchant inventory item.
     */
    public function store(StoreMerchantInventoryRequest $request, Npc $npc): RedirectResponse
    {
        if (! $npc->is_merchant) {
            abort(404, 'Этот NPC не является торговцем');
        }

        try {
            DB::transaction(function () use ($request, $npc) {
                MerchantInventory::create([
                    'npc_id' => $npc->id,
                    'item_id' => $request->input('item_id'),
                    'quantity' => $request->input('quantity', 0),
                    'max_quantity' => $request->input('max_quantity', 0),
                    'base_price' => $request->input('base_price'),
                    'base_sell_price' => $request->input('base_sell_price', 0),
                    'price_multiplier' => $request->input('price_multiplier', 1.0),
                    'purchase_limit_per_day' => $request->input('purchase_limit_per_day'),
                    'is_available' => $request->boolean('is_available', true),
                ]);
            });

            return redirect()->route('admin.merchant-inventories.index', $npc)
                ->with('success', 'Предмет успешно добавлен в ассортимент торговца');
        } catch (\Exception $e) {
            Log::error('Merchant inventory store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'npc_id' => $npc->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при добавлении предмета: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified merchant inventory item.
     */
    public function edit(Npc $npc, MerchantInventory $merchantInventory): View
    {
        if (! $npc->is_merchant) {
            abort(404, 'Этот NPC не является торговцем');
        }

        if ($merchantInventory->npc_id !== $npc->id) {
            abort(404, 'Предмет не принадлежит этому торговцу');
        }

        $merchantInventory->load('item');

        return view('admin.merchant-inventories.edit', [
            'npc' => $npc,
            'inventory' => $merchantInventory,
        ]);
    }

    /**
     * Update the specified merchant inventory item.
     */
    public function update(UpdateMerchantInventoryRequest $request, Npc $npc, MerchantInventory $merchantInventory): RedirectResponse
    {
        if (! $npc->is_merchant) {
            abort(404, 'Этот NPC не является торговцем');
        }

        if ($merchantInventory->npc_id !== $npc->id) {
            abort(404, 'Предмет не принадлежит этому торговцу');
        }

        try {
            DB::transaction(function () use ($request, $merchantInventory) {
                $merchantInventory->update([
                    'quantity' => $request->input('quantity', $merchantInventory->quantity),
                    'max_quantity' => $request->input('max_quantity', $merchantInventory->max_quantity),
                    'base_price' => $request->input('base_price'),
                    'base_sell_price' => $request->input('base_sell_price', $merchantInventory->base_sell_price),
                    'price_multiplier' => $request->input('price_multiplier', $merchantInventory->price_multiplier),
                    'purchase_limit_per_day' => $request->input('purchase_limit_per_day'),
                    'is_available' => $request->boolean('is_available', $merchantInventory->is_available),
                ]);
            });

            return redirect()->route('admin.merchant-inventories.index', $npc)
                ->with('success', 'Предмет успешно обновлен');
        } catch (\Exception $e) {
            Log::error('Merchant inventory update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'merchant_inventory_id' => $merchantInventory->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении предмета: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified merchant inventory item.
     */
    public function destroy(Npc $npc, MerchantInventory $merchantInventory): RedirectResponse
    {
        if (! $npc->is_merchant) {
            abort(404, 'Этот NPC не является торговцем');
        }

        if ($merchantInventory->npc_id !== $npc->id) {
            abort(404, 'Предмет не принадлежит этому торговцу');
        }

        try {
            $merchantInventory->delete();

            return redirect()->route('admin.merchant-inventories.index', $npc)
                ->with('success', 'Предмет успешно удален из ассортимента');
        } catch (\Exception $e) {
            Log::error('Merchant inventory destroy failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'merchant_inventory_id' => $merchantInventory->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении предмета: '.$e->getMessage()]);
        }
    }

    /**
     * Restock merchant inventory (restore quantity to max_quantity).
     */
    public function restock(Npc $npc, MerchantInventory $merchantInventory): RedirectResponse
    {
        if (! $npc->is_merchant) {
            abort(404, 'Этот NPC не является торговцем');
        }

        if ($merchantInventory->npc_id !== $npc->id) {
            abort(404, 'Предмет не принадлежит этому торговцу');
        }

        try {
            if ($merchantInventory->max_quantity > 0) {
                $merchantInventory->quantity = $merchantInventory->max_quantity;
                $merchantInventory->restocked_at = now();
                $merchantInventory->price_multiplier = 1.0; // Сброс множителя цены
                $merchantInventory->save();
            }

            return redirect()->route('admin.merchant-inventories.index', $npc)
                ->with('success', 'Ассортимент пополнен');
        } catch (\Exception $e) {
            Log::error('Merchant inventory restock failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'merchant_inventory_id' => $merchantInventory->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при пополнении ассортимента: '.$e->getMessage()]);
        }
    }
}
