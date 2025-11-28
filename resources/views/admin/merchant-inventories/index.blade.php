@extends('layouts.app')

@section('title', 'Ассортимент торговца: ' . $npc->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-semibold">Ассортимент торговца: {{ $npc->name }}</h1>
            <a
                href="{{ route('admin.npcs.index') }}"
                class="text-[#f53003] dark:text-[#FF4433] hover:underline mt-2 inline-block"
            >
                ← Назад к списку NPC
            </a>
        </div>
        @include('admin.partials.menu', [
            'additionalButtons' => [
                ['route' => route('admin.merchant-inventories.create', $npc), 'label' => 'Добавить товар']
            ]
        ])
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.merchant-inventories.index', $npc) }}" class="flex flex-col sm:flex-row gap-4">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Поиск по названию предмета..."
                class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
            <select
                name="is_available"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все товары</option>
                <option value="1" {{ request('is_available') === '1' ? 'selected' : '' }}>Доступные</option>
                <option value="0" {{ request('is_available') === '0' ? 'selected' : '' }}>Недоступные</option>
            </select>
            <select
                name="buyable"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все товары</option>
                <option value="1" {{ request('buyable') === '1' ? 'selected' : '' }}>Только покупаемые</option>
                <option value="0" {{ request('buyable') === '0' ? 'selected' : '' }}>Только продаваемые</option>
            </select>
            <button
                type="submit"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white whitespace-nowrap"
            >
                Поиск
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
            <thead class="bg-gray-50 dark:bg-[#0a0a0a]">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Предмет</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Количество</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Цена покупки</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Цена продажи</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Множитель</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($inventories as $inventory)
                    @php
                        $item = $inventory->item;
                        $rarityColors = [
                            'common' => 'text-gray-600 dark:text-gray-400',
                            'uncommon' => 'text-green-600 dark:text-green-400',
                            'rare' => 'text-blue-600 dark:text-blue-400',
                            'epic' => 'text-purple-600 dark:text-purple-400',
                            'legendary' => 'text-orange-600 dark:text-orange-400',
                        ];
                        $rarityColor = $rarityColors[$item->rarity] ?? $rarityColors['common'];
                    @endphp
                    <tr>
                        <td class="px-3 sm:px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-medium {{ $rarityColor }}">{{ $item->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item->type }}{{ $item->subtype ? ' / ' . $item->subtype : '' }}</span>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-sm">
                            @if($inventory->max_quantity > 0)
                                {{ $inventory->quantity }} / {{ $inventory->max_quantity }}
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Безлимит</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-sm hidden md:table-cell">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ number_format($inventory->getCurrentBuyPrice()) }} золота</span>
                                @if($inventory->price_multiplier != 1.0)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Базовая: {{ number_format($inventory->base_price) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-sm hidden lg:table-cell">
                            @if($inventory->base_sell_price > 0)
                                {{ number_format($inventory->getCurrentSellPrice()) }} золота
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Не покупает</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-sm hidden lg:table-cell">
                            <span class="{{ $inventory->price_multiplier > 1.0 ? 'text-red-600 dark:text-red-400' : ($inventory->price_multiplier < 1.0 ? 'text-green-600 dark:text-green-400' : '') }}">
                                {{ number_format($inventory->price_multiplier, 2) }}x
                            </span>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-sm">
                            <div class="flex flex-col gap-1">
                                @if($inventory->is_available)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Доступен</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Недоступен</span>
                                @endif
                                @if($inventory->base_price > 0)
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Продает</span>
                                @endif
                                @if($inventory->base_sell_price > 0)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Покупает</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-sm">
                            <div class="flex gap-2">
                                <a
                                    href="{{ route('admin.merchant-inventories.edit', [$npc, $inventory]) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:underline"
                                >
                                    Редактировать
                                </a>
                                @if($inventory->max_quantity > 0 && $inventory->quantity < $inventory->max_quantity)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.merchant-inventories.restock', [$npc, $inventory]) }}"
                                        class="inline"
                                        onsubmit="return confirm('Пополнить ассортимент до максимума и сбросить множитель цены?')"
                                    >
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="text-green-600 dark:text-green-400 hover:underline">
                                            Пополнить
                                        </button>
                                    </form>
                                @endif
                                <form
                                    method="POST"
                                    action="{{ route('admin.merchant-inventories.destroy', [$npc, $inventory]) }}"
                                    class="inline"
                                    onsubmit="return confirm('Удалить предмет из ассортимента?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 sm:px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            Нет товаров в ассортименте
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $inventories->links() }}
    </div>
</div>
@endsection

