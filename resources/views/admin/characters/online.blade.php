@extends('layouts.app')

@section('title', 'Онлайн персонажи')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">Персонажи в игре</h1>
        <div class="flex gap-2">
            <a
                href="{{ route('admin.characters.index') }}"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a] whitespace-nowrap"
            >
                Все персонажи
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
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-sm font-medium">Онлайн: {{ $sessions->count() }} персонажей</span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
            <thead class="bg-gray-50 dark:bg-[#0a0a0a]">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Персонаж</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Пользователь</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Локация</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Уровень</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Вход в игру</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Последняя активность</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($sessions as $session)
                    @php
                        $character = $session->character;
                        $presence = $presences[$character->id] ?? null;
                        $location = $presence?->location ?? $session->location ?? $character->location;
                    @endphp
                    <tr>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">{{ $character->id }}</td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                <span class="text-sm font-medium">{{ $character->name }}</span>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden sm:table-cell">
                            <a href="{{ route('admin.users.show', $character->user) }}" class="text-[#f53003] dark:text-[#FF4433] hover:underline">
                                {{ $character->user->username }}
                            </a>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-sm">
                            @if($location)
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $location->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $location->type }}
                                        @if($location->is_safe_zone)
                                            <span class="text-green-600 dark:text-green-400">(Безопасная зона)</span>
                                        @endif
                                    </span>
                                    @if($location->coordinate_x !== null && $location->coordinate_y !== null)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">
                                            Координаты: ({{ $location->coordinate_x }}, {{ $location->coordinate_y }})
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Не указана</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                            {{ $character->level }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden lg:table-cell">
                            <div class="flex flex-col">
                                <span>{{ $session->login_at->format('d.m.Y') }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $session->login_at->format('H:i:s') }}</span>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-col">
                                <span>{{ $session->last_activity_at->format('H:i:s') }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $session->last_activity_at->diffForHumans() }}
                                </span>
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
                            В данный момент нет персонажей в игре
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

