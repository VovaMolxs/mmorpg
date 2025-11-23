@extends('layouts.app')

@section('title', 'Управление персонажами')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">Управление персонажами</h1>
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
                href="{{ route('admin.item-spawns.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap"
            >
                Спавн предметов
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.characters.index') }}" class="flex flex-col sm:flex-row gap-4">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Поиск по имени персонажа, пользователю или email..."
                class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
            <input
                type="number"
                name="level_min"
                value="{{ request('level_min') }}"
                placeholder="Мин. уровень"
                min="1"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433] w-full sm:w-32"
            >
            <input
                type="number"
                name="level_max"
                value="{{ request('level_max') }}"
                placeholder="Макс. уровень"
                min="1"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433] w-full sm:w-32"
            >
            <select
                name="is_active"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все статусы</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Активные</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Неактивные</option>
            </select>
            <select
                name="is_criminal"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все</option>
                <option value="1" {{ request('is_criminal') === '1' ? 'selected' : '' }}>Преступники</option>
                <option value="0" {{ request('is_criminal') === '0' ? 'selected' : '' }}>Законопослушные</option>
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
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Имя</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Пользователь</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Уровень</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Характеристики</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Здоровье</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($characters as $character)
                    <tr>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">{{ $character->id }}</td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $character->name }}</td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden sm:table-cell">
                            <a href="{{ route('admin.users.show', $character->user) }}" class="text-[#f53003] dark:text-[#FF4433] hover:underline">
                                {{ $character->user->username }}
                            </a>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">{{ $character->level }}</td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                            С:{{ $character->strength }} Л:{{ $character->agility }} И:{{ $character->intelligence }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden lg:table-cell">
                            {{ $character->health_current }}/{{ $character->health_max }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-col gap-1">
                                @if($character->is_active)
                                    <span class="text-green-600 dark:text-green-400">Активен</span>
                                @else
                                    <span class="text-gray-500">Неактивен</span>
                                @endif
                                @if($character->is_criminal)
                                    <span class="text-red-600 dark:text-red-400 text-xs">Преступник</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <a
                                href="{{ route('admin.characters.show', $character) }}"
                                class="text-[#f53003] dark:text-[#FF4433] hover:underline"
                            >
                                Просмотр
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Персонажи не найдены
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $characters->links() }}
    </div>
</div>
@endsection

