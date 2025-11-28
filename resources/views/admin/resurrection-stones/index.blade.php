@extends('layouts.app')

@section('title', 'Управление камнями воскрешения')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">Управление камнями воскрешения</h1>
        @include('admin.partials.menu', [
            'additionalButtons' => [
                ['route' => route('admin.resurrection-stones.create'), 'label' => 'Создать камень']
            ]
        ])
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.resurrection-stones.index') }}" class="flex flex-col sm:flex-row gap-4">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Поиск по названию или описанию..."
                class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
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
        </form>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
            <thead class="bg-gray-50 dark:bg-[#0a0a0a]">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Название</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Локация</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Уровень</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Перезарядка</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Эффект</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#161615] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($stones as $stone)
                    <tr>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">{{ $stone->id }}</td>
                        <td class="px-3 sm:px-6 py-4 text-sm font-medium">
                            {{ $stone->name }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            {{ $stone->location->name }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden sm:table-cell">
                            {{ $stone->level_required }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                            @if($stone->cooldown_minutes > 0)
                                {{ $stone->cooldown_minutes }} мин
                            @else
                                <span class="text-gray-400">Без перезарядки</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm hidden lg:table-cell">
                            {{ $stone->visual_effect }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            @if($stone->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded">Активен</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded">Неактивен</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-col gap-1">
                                <a
                                    href="{{ route('admin.resurrection-stones.edit', $stone) }}"
                                    class="text-[#f53003] dark:text-[#FF4433] hover:underline"
                                >
                                    Редактировать
                                </a>
                                <form
                                    method="POST"
                                    action="{{ route('admin.resurrection-stones.destroy', $stone) }}"
                                    class="inline"
                                    onsubmit="return confirm('Вы уверены, что хотите удалить этот камень воскрешения?');"
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
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Камни воскрешения не найдены
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $stones->links() }}
    </div>
</div>
@endsection

