@extends('layouts.app')

@section('title', 'Управление активными монстрами')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">Управление активными монстрами</h1>
        @include('admin.partials.menu', [
            'additionalButtons' => [
                ['route' => route('admin.monsters.index'), 'label' => 'Монстры'],
                ['route' => route('admin.monster-spawns.index'), 'label' => 'Спавны']
            ]
        ])
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.active-monsters.index') }}" class="flex flex-col sm:flex-row gap-4">
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
                name="monster_id"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все монстры</option>
                @foreach($monsters as $monster)
                    <option value="{{ $monster->id }}" {{ request('monster_id') == $monster->id ? 'selected' : '' }}>
                        {{ $monster->name }}
                    </option>
                @endforeach
            </select>
            <select
                name="is_active"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="1" {{ request('is_active', '1') === '1' ? 'selected' : '' }}>Активные</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Мертвые</option>
                <option value="" {{ request('is_active') === '' ? 'selected' : '' }}>Все</option>
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
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Монстр</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Локация</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Здоровье</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Мана</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Заспавнен</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($activeMonsters as $activeMonster)
                    @php
                        $monster = $activeMonster->monster;
                        $stats = $monster->stats;
                        $healthPercentage = $activeMonster->getHealthPercentage();
                        $manaPercentage = $activeMonster->getManaPercentage();
                    @endphp
                    <tr>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">{{ $activeMonster->id }}</td>
                        <td class="px-3 sm:px-6 py-4 text-sm font-medium">
                            <div class="flex flex-col">
                                <span>{{ $monster->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Уровень: {{ $monster->level }}</span>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-sm">
                            {{ $activeMonster->location->name }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden sm:table-cell">
                            <div class="flex flex-col">
                                <span>{{ $activeMonster->health_current }} / {{ $stats?->health_max ?? 'N/A' }}</span>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-1">
                                    <div
                                        class="h-2 rounded-full {{ $healthPercentage > 50 ? 'bg-green-600' : ($healthPercentage > 25 ? 'bg-yellow-600' : 'bg-red-600') }}"
                                        style="width: {{ $healthPercentage }}%"
                                    ></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                            <div class="flex flex-col">
                                <span>{{ $activeMonster->mana_current }} / {{ $stats?->mana_max ?? 'N/A' }}</span>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-1">
                                    <div
                                        class="h-2 rounded-full bg-blue-600"
                                        style="width: {{ $manaPercentage }}%"
                                    ></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden lg:table-cell">
                            {{ $activeMonster->spawned_at?->format('d.m.Y H:i') ?? 'N/A' }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            @if($activeMonster->is_active && !$activeMonster->isDead())
                                <span class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">Жив</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded">Мертв</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-col gap-1">
                                @if($activeMonster->is_active && !$activeMonster->isDead())
                                    <div class="flex gap-2">
                                        <form
                                            method="POST"
                                            action="{{ route('admin.active-monsters.heal', $activeMonster) }}"
                                            class="inline"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="text-green-600 dark:text-green-400 hover:underline text-xs"
                                            >
                                                Исцелить
                                            </button>
                                        </form>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.active-monsters.kill', $activeMonster) }}"
                                            class="inline"
                                            onsubmit="return confirm('Вы уверены, что хотите убить этого монстра?');"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="text-red-600 dark:text-red-400 hover:underline text-xs"
                                            >
                                                Убить
                                            </button>
                                        </form>
                                    </div>
                                @endif
                                <form
                                    method="POST"
                                    action="{{ route('admin.active-monsters.destroy', $activeMonster) }}"
                                    class="inline"
                                    onsubmit="return confirm('Вы уверены, что хотите удалить этого монстра?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-red-600 dark:text-red-400 hover:underline text-xs"
                                    >
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Активные монстры не найдены
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $activeMonsters->links() }}
    </div>
</div>
@endsection

