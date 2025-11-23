@extends('layouts.app')

@section('title', 'Управление спавном предметов')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">Управление спавном предметов</h1>
        <div class="flex gap-2">
            <a
                href="{{ route('admin.characters.online') }}"
                class="px-4 py-2 bg-green-600 dark:bg-green-700 text-white rounded hover:bg-green-700 dark:hover:bg-green-600 whitespace-nowrap"
            >
                Онлайн персонажи
            </a>
            <a
                href="{{ route('admin.users.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap"
            >
                Пользователи
            </a>
            <a
                href="{{ route('admin.characters.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap"
            >
                Персонажи
            </a>
            <a
                href="{{ route('admin.items.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap"
            >
                Предметы
            </a>
            <a
                href="{{ route('admin.world-map.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap"
            >
                Карта мира
            </a>
            <a
                href="{{ route('admin.item-spawns.create') }}"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white whitespace-nowrap"
            >
                Создать спавн
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.item-spawns.index') }}" class="flex flex-col sm:flex-row gap-4">
            <select
                name="location_id"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все локации</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
            <select
                name="item_id"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все предметы</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
            <select
                name="is_active"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все статусы</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Активные</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Неактивные</option>
            </select>
            <button
                type="submit"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white whitespace-nowrap"
            >
                Поиск
            </button>
            @if(request()->hasAny(['location_id', 'item_id', 'is_active']))
                <a
                    href="{{ route('admin.item-spawns.index') }}"
                    class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap"
                >
                    Сбросить
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
            <thead class="bg-gray-50 dark:bg-[#0a0a0a]">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Локация</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Предмет</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Количество</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Респавн</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Макс. экз.</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Шанс</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($spawns as $spawn)
                    <tr>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">{{ $spawn->id }}</td>
                        <td class="px-3 sm:px-6 py-4 text-sm">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $spawn->location->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    ({{ $spawn->location->coordinate_x }}, {{ $spawn->location->coordinate_y }})
                                </span>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-sm">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $spawn->item->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $spawn->item->type }}</span>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                            {{ $spawn->min_quantity }}-{{ $spawn->max_quantity }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden lg:table-cell">
                            {{ $spawn->respawn_time_min }}-{{ $spawn->respawn_time_max }} мин
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden lg:table-cell">
                            {{ $spawn->max_instances }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                            {{ $spawn->spawn_chance }}%
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            @if($spawn->is_active)
                                <span class="px-2 py-1 text-xs rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                    Активен
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    Неактивен
                                </span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-2">
                                <a
                                    href="{{ route('admin.item-spawns.edit', $spawn) }}"
                                    class="text-[#f53003] dark:text-[#FF4433] hover:underline"
                                >
                                    Редактировать
                                </a>
                                <form
                                    method="POST"
                                    action="{{ route('admin.item-spawns.destroy', $spawn) }}"
                                    class="inline"
                                    onsubmit="return confirm('Вы уверены, что хотите удалить эту настройку спавна?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-red-600 dark:text-red-400 hover:underline"
                                    >
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Настройки спавна не найдены
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $spawns->links() }}
    </div>
</div>
@endsection

