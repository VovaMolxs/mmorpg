@extends('layouts.app')

@section('title', 'Управление NPC')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">Управление NPC</h1>
        @include('admin.partials.menu', [
            'additionalButtons' => [
                ['route' => route('admin.npcs.create'), 'label' => 'Создать NPC']
            ]
        ])
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.npcs.index') }}" class="flex flex-col sm:flex-row gap-4">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Поиск по названию или описанию..."
                class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
            <select
                name="type"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все типы</option>
                @foreach($types as $typeKey => $typeLabel)
                    <option value="{{ $typeKey }}" {{ request('type') === $typeKey ? 'selected' : '' }}>
                        {{ $typeLabel }}
                    </option>
                @endforeach
            </select>
            <select
                name="is_hostile"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все NPC</option>
                <option value="1" {{ request('is_hostile') === '1' ? 'selected' : '' }}>Враждебные</option>
                <option value="0" {{ request('is_hostile') === '0' ? 'selected' : '' }}>Мирные</option>
            </select>
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
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Название</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Тип</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Поведение</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Локация</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Уровень</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($npcs as $npc)
                    <tr>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">{{ $npc->id }}</td>
                        <td class="px-3 sm:px-6 py-4 text-sm font-medium">
                            <div class="flex flex-col">
                                <span>{{ $npc->name }}</span>
                                @if($npc->is_hostile)
                                    <span class="text-xs text-red-600 dark:text-red-400">Враждебный</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden sm:table-cell">
                            {{ $types[$npc->type] ?? $npc->type }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            {{ $aiBehaviors[$npc->ai_behavior] ?? $npc->ai_behavior }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                            {{ $npc->location?->name ?? '—' }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden lg:table-cell">
                            {{ $npc->stats?->level ?? '—' }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-2">
                                <a
                                    href="{{ route('admin.npcs.edit', $npc) }}"
                                    class="text-[#f53003] dark:text-[#FF4433] hover:underline"
                                >
                                    Редактировать
                                </a>
                                <form
                                    method="POST"
                                    action="{{ route('admin.npcs.destroy', $npc) }}"
                                    class="inline"
                                    onsubmit="return confirm('Вы уверены, что хотите удалить этого NPC?');"
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
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            NPC не найдены
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $npcs->links() }}
    </div>
</div>
@endsection

