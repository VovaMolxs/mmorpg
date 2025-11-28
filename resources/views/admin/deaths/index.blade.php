@extends('layouts.app')

@section('title', 'История смертей')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">История смертей</h1>
        @include('admin.partials.menu')
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Всего смертей</div>
            <div class="text-2xl font-bold text-[#f53003] dark:text-[#FF4433]">{{ number_format($totalDeaths) }}</div>
        </div>
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Сегодня</div>
            <div class="text-2xl font-bold">{{ $todayDeaths }}</div>
        </div>
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">На этой неделе</div>
            <div class="text-2xl font-bold">{{ $thisWeekDeaths }}</div>
        </div>
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">В этом месяце</div>
            <div class="text-2xl font-bold">{{ $thisMonthDeaths }}</div>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.deaths.index') }}" class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Поиск по имени персонажа..."
                    class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                <select
                    name="killed_by_type"
                    class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                    <option value="">Все типы убийц</option>
                    <option value="npc" {{ request('killed_by_type') === 'npc' ? 'selected' : '' }}>NPC</option>
                    <option value="player" {{ request('killed_by_type') === 'player' ? 'selected' : '' }}>Игрок</option>
                    <option value="environment" {{ request('killed_by_type') === 'environment' ? 'selected' : '' }}>Окружение</option>
                </select>
                <select
                    name="death_cause"
                    class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                    <option value="">Все причины</option>
                    <option value="combat" {{ request('death_cause') === 'combat' ? 'selected' : '' }}>Бой</option>
                    <option value="fall" {{ request('death_cause') === 'fall' ? 'selected' : '' }}>Падение</option>
                    <option value="poison" {{ request('death_cause') === 'poison' ? 'selected' : '' }}>Яд</option>
                    <option value="simulation" {{ request('death_cause') === 'simulation' ? 'selected' : '' }}>Симуляция</option>
                </select>
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    placeholder="Дата от"
                    class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    placeholder="Дата до"
                    class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                <button
                    type="submit"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white whitespace-nowrap"
                >
                    Поиск
                </button>
                <a
                    href="{{ route('admin.deaths.index') }}"
                    class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] text-center whitespace-nowrap"
                >
                    Сбросить
                </a>
            </div>
        </form>
    </div>

    <!-- Таблица смертей -->
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
            <thead class="bg-gray-50 dark:bg-[#0a0a0a]">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Персонаж</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Локация</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Убит</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Причина</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Время смерти</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($deaths as $death)
                    <tr>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">{{ $death->id }}</td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <div class="font-medium">{{ $death->character->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Уровень {{ $death->character->level }}
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden sm:table-cell">
                            {{ $death->location->name ?? 'Неизвестно' }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            @if($death->killed_by_type)
                                <span class="px-2 py-1 rounded text-xs
                                    {{ $death->killed_by_type === 'npc' ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : '' }}
                                    {{ $death->killed_by_type === 'player' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : '' }}
                                    {{ $death->killed_by_type === 'environment' ? 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200' : '' }}
                                ">
                                    {{ $death->killed_by_type === 'npc' ? 'NPC' : ($death->killed_by_type === 'player' ? 'Игрок' : 'Окружение') }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                            {{ $death->death_cause ?? 'Неизвестно' }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <div>{{ $death->died_at->format('d.m.Y') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $death->died_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-col gap-1">
                                @if($death->ghostState)
                                    <span class="text-purple-600 dark:text-purple-400 text-xs">👻 Призрак</span>
                                @else
                                    <span class="text-green-600 dark:text-green-400 text-xs">✓ Воскрешен</span>
                                @endif
                                @if($death->corpse)
                                    @if($death->corpse->isExpired())
                                        <span class="text-gray-500 text-xs">Труп исчез</span>
                                    @elseif($death->corpse->is_looted)
                                        <span class="text-yellow-600 dark:text-yellow-400 text-xs">Труп разграблен</span>
                                    @else
                                        <span class="text-blue-600 dark:text-blue-400 text-xs">Труп активен</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <a
                                href="{{ route('admin.deaths.show', $death) }}"
                                class="text-[#f53003] dark:text-[#FF4433] hover:underline"
                            >
                                Просмотр
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Смерти не найдены
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $deaths->links() }}
    </div>
</div>
@endsection

