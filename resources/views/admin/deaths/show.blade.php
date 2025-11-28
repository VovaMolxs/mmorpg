@extends('layouts.app')

@section('title', 'Детали смерти #' . $death->id)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">Детали смерти #{{ $death->id }}</h1>
        <a
            href="{{ route('admin.deaths.index') }}"
            class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
        >
            ← Назад к списку
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Основная информация -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Персонаж:</span>
                    <div class="mt-1">
                        <a href="{{ route('admin.characters.show', $death->character) }}" class="text-[#f53003] dark:text-[#FF4433] hover:underline font-medium">
                            {{ $death->character->name }}
                        </a>
                        <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                            (Уровень {{ $death->character->level }})
                        </span>
                    </div>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Пользователь:</span>
                    <div class="mt-1">
                        <a href="{{ route('admin.users.show', $death->character->user) }}" class="text-[#f53003] dark:text-[#FF4433] hover:underline">
                            {{ $death->character->user->username }}
                        </a>
                    </div>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Локация:</span>
                    <div class="mt-1 font-medium">{{ $death->location->name ?? 'Неизвестно' }}</div>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Время смерти:</span>
                    <div class="mt-1 font-medium">
                        {{ $death->died_at->format('d.m.Y H:i:s') }}
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            ({{ $death->died_at->diffForHumans() }})
                        </span>
                    </div>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Убит типом:</span>
                    <div class="mt-1">
                        @if($death->killed_by_type)
                            <span class="px-2 py-1 rounded text-sm
                                {{ $death->killed_by_type === 'npc' ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : '' }}
                                {{ $death->killed_by_type === 'player' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : '' }}
                                {{ $death->killed_by_type === 'environment' ? 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200' : '' }}
                            ">
                                {{ $death->killed_by_type === 'npc' ? 'NPC' : ($death->killed_by_type === 'player' ? 'Игрок' : 'Окружение') }}
                            </span>
                        @else
                            <span class="text-gray-400">Не указано</span>
                        @endif
                    </div>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Причина смерти:</span>
                    <div class="mt-1 font-medium">{{ $death->death_cause ?? 'Неизвестно' }}</div>
                </div>
                @if($death->killed_by_id)
                    <div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">ID убийцы:</span>
                        <div class="mt-1 font-medium">{{ $death->killed_by_id }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Статус призрака и трупа -->
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Текущий статус</h2>
            <div class="space-y-4">
                <!-- Статус призрака -->
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Статус призрака:</span>
                    <div class="mt-1">
                        @if($death->ghostState)
                            <span class="px-2 py-1 rounded text-sm bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                                👻 Призрак активен
                            </span>
                            <div class="mt-2 text-sm">
                                <div>Видим для других: {{ $death->ghostState->is_visible ? 'Да' : 'Нет' }}</div>
                                <div>Локация: {{ $death->ghostState->location->name ?? 'Неизвестно' }}</div>
                                <div>Создан: {{ $death->ghostState->created_at->format('d.m.Y H:i:s') }}</div>
                            </div>
                        @else
                            <span class="px-2 py-1 rounded text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                ✓ Воскрешен
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Статус трупа -->
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Статус трупа:</span>
                    <div class="mt-1">
                        @if($death->corpse)
                            @if($death->corpse->isExpired())
                                <span class="px-2 py-1 rounded text-sm bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    Труп исчез
                                </span>
                            @elseif($death->corpse->is_looted)
                                <span class="px-2 py-1 rounded text-sm bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                    Труп разграблен
                                </span>
                            @else
                                <span class="px-2 py-1 rounded text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                    Труп активен
                                </span>
                            @endif
                            <div class="mt-2 text-sm space-y-1">
                                <div>Тип трупа: 
                                    <span class="font-medium
                                        {{ $death->corpse->corpse_type === 'innocent' ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}
                                    ">
                                        {{ $death->corpse->corpse_type === 'innocent' ? 'Мирный' : ($death->corpse->corpse_type === 'criminal' ? 'Преступник' : $death->corpse->corpse_type) }}
                                    </span>
                                </div>
                                <div>Локация: {{ $death->corpse->location->name ?? 'Неизвестно' }}</div>
                                @if(!$death->corpse->isExpired())
                                    <div>Исчезнет через: {{ $death->corpse->getRemainingMinutes() }} минут</div>
                                @else
                                    <div>Исчез: {{ $death->corpse->expires_at->format('d.m.Y H:i:s') }}</div>
                                @endif
                                @php
                                    $itemsData = $death->corpse->items_data ?? [];
                                    $inventoryCount = count($itemsData['inventory'] ?? []);
                                    $equipmentCount = count($itemsData['equipment'] ?? []);
                                    $totalItems = $inventoryCount + $equipmentCount;
                                @endphp
                                <div>Предметов в трупе: {{ $totalItems }} 
                                    ({{ $inventoryCount }} в инвентаре, {{ $equipmentCount }} в экипировке)
                                </div>
                            </div>
                        @else
                            <span class="text-gray-400">Труп не найден</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Предметы в трупе -->
    @if($death->corpse && !empty($death->corpse->items_data))
        <div class="mt-6 bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Предметы в трупе</h2>
            @php
                $itemsData = $death->corpse->items_data ?? [];
                $inventoryItems = $itemsData['inventory'] ?? [];
                $equipmentItems = $itemsData['equipment'] ?? [];
            @endphp

            @if(!empty($inventoryItems))
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-3">Инвентарь ({{ count($inventoryItems) }})</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($inventoryItems as $item)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <div class="font-medium">{{ $item['item_data']['name'] ?? 'Неизвестный предмет' }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Количество: {{ $item['quantity'] ?? 1 }}
                                </div>
                                @if(isset($item['durability_current']))
                                    <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                        Прочность: {{ $item['durability_current'] }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($equipmentItems))
                <div>
                    <h3 class="text-lg font-medium mb-3">Экипировка ({{ count($equipmentItems) }})</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($equipmentItems as $item)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <div class="font-medium">{{ $item['item_data']['name'] ?? 'Неизвестный предмет' }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Количество: {{ $item['quantity'] ?? 1 }}
                                </div>
                                @if(isset($item['durability_current']))
                                    <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                        Прочность: {{ $item['durability_current'] }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

