@extends('layouts.app')

@section('title', 'Редактировать товар в ассортименте')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Редактировать товар: {{ $inventory->item->name }}</h1>
        <div class="flex gap-4">
            <a
                href="{{ route('admin.merchant-inventories.index', $npc) }}"
                class="text-[#f53003] dark:text-[#FF4433] hover:underline"
            >
                ← Назад к ассортименту
            </a>
            <span class="text-gray-400">|</span>
            <span class="text-gray-600 dark:text-gray-400">Торговец: {{ $npc->name }}</span>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.merchant-inventories.update', [$npc, $inventory]) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Информация о товаре</h2>

            <div class="bg-gray-50 dark:bg-[#0a0a0a] p-4 rounded">
                <p class="text-sm"><strong>Предмет:</strong> {{ $inventory->item->name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $inventory->item->type }}{{ $inventory->item->subtype ? ' / ' . $inventory->item->subtype : '' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="block text-sm font-medium mb-1">Текущее количество</label>
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        value="{{ old('quantity', $inventory->quantity) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">0 = безлимит (если max_quantity = 0)</p>
                </div>

                <div>
                    <label for="max_quantity" class="block text-sm font-medium mb-1">Максимальное количество</label>
                    <input
                        type="number"
                        id="max_quantity"
                        name="max_quantity"
                        value="{{ old('max_quantity', $inventory->max_quantity) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('max_quantity')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">0 = безлимит</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="base_price" class="block text-sm font-medium mb-1">Базовая цена покупки (золота) *</label>
                    <input
                        type="number"
                        id="base_price"
                        name="base_price"
                        value="{{ old('base_price', $inventory->base_price) }}"
                        min="0"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('base_price')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Текущая цена: {{ number_format($inventory->getCurrentBuyPrice()) }} золота</p>
                </div>

                <div>
                    <label for="base_sell_price" class="block text-sm font-medium mb-1">Базовая цена продажи (золота)</label>
                    <input
                        type="number"
                        id="base_sell_price"
                        name="base_sell_price"
                        value="{{ old('base_sell_price', $inventory->base_sell_price) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('base_sell_price')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @if($inventory->base_sell_price > 0)
                            Текущая цена: {{ number_format($inventory->getCurrentSellPrice()) }} золота
                        @else
                            0 = торговец не покупает этот предмет
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="price_multiplier" class="block text-sm font-medium mb-1">Множитель цены</label>
                    <input
                        type="number"
                        id="price_multiplier"
                        name="price_multiplier"
                        value="{{ old('price_multiplier', $inventory->price_multiplier) }}"
                        step="0.01"
                        min="0.1"
                        max="5.0"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('price_multiplier')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Используется для динамического ценообразования (0.1 - 5.0)</p>
                </div>

                <div>
                    <label for="purchase_limit_per_day" class="block text-sm font-medium mb-1">Лимит покупок в день (на игрока)</label>
                    <input
                        type="number"
                        id="purchase_limit_per_day"
                        name="purchase_limit_per_day"
                        value="{{ old('purchase_limit_per_day', $inventory->purchase_limit_per_day) }}"
                        min="1"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('purchase_limit_per_day')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Оставьте пустым для безлимита</p>
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        name="is_available"
                        value="1"
                        {{ old('is_available', $inventory->is_available) ? 'checked' : '' }}
                        class="rounded border-[#e3e3e0] dark:border-[#3E3E3A] text-[#f53003] dark:text-[#FF4433] focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    <span class="ml-2 text-sm">Доступен для покупки</span>
                </label>
                @error('is_available')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            @if($inventory->restocked_at)
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Последнее пополнение: {{ $inventory->restocked_at->format('d.m.Y H:i') }}
                </div>
            @endif
        </div>

        <div class="flex gap-4">
            <button
                type="submit"
                class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white font-medium"
            >
                Сохранить изменения
            </button>
            <a
                href="{{ route('admin.merchant-inventories.index', $npc) }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
        </div>
    </form>
</div>
@endsection

