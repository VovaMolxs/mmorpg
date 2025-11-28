@extends('layouts.app')

@section('title', 'Склад персонажа: ' . $character->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Склад персонажа: {{ $character->name }}</h1>
        <div class="flex gap-4">
            <a
                href="{{ route('admin.bank-storages.index') }}"
                class="text-[#f53003] dark:text-[#FF4433] hover:underline"
            >
                ← Назад к списку
            </a>
            <a
                href="{{ route('admin.characters.show', $character) }}"
                class="text-blue-600 dark:text-blue-400 hover:underline"
            >
                Профиль персонажа
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.bank-storages.show', $character) }}" class="flex flex-col sm:flex-row gap-4">
            <select
                name="banker_id"
                class="px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
            >
                <option value="">Все банкиры</option>
                @foreach($bankers as $banker)
                    <option value="{{ $banker->id }}" {{ request('banker_id') == $banker->id ? 'selected' : '' }}>
                        {{ $banker->npc->name }} ({{ $banker->location->name }})
                    </option>
                @endforeach
            </select>
            <button
                type="submit"
                class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white whitespace-nowrap"
            >
                Фильтровать
            </button>
        </form>
    </div>

    @foreach($storages as $bankerId => $bankerStorages)
        @php
            $banker = $bankers->firstWhere('id', $bankerId);
        @endphp
        @if($banker)
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">
                        {{ $banker->npc->name }} ({{ $banker->location->name }})
                    </h2>
                    <form
                        method="POST"
                        action="{{ route('admin.bank-storages.clear', $character) }}"
                        class="inline"
                        onsubmit="return confirm('Вы уверены, что хотите очистить все склады этого персонажа у банкира {{ $banker->npc->name }}?');"
                    >
                        @csrf
                        <input type="hidden" name="banker_id" value="{{ $banker->id }}">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-red-600 dark:bg-red-700 text-white rounded hover:bg-red-700 dark:hover:bg-red-600"
                        >
                            Очистить все
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                    @foreach($bankerStorages as $storage)
                        <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded p-2 {{ $storage->is_locked ? 'bg-yellow-100 dark:bg-yellow-900' : '' }}">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                Ячейка #{{ $storage->slot_number }}
                            </div>
                            @if($storage->itemInstance && $storage->itemInstance->item)
                                <div class="text-sm font-medium mb-1">
                                    {{ $storage->itemInstance->item->name }}
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                    x{{ $storage->itemInstance->quantity }}
                                </div>
                                @if($storage->is_locked)
                                    <div class="text-xs text-yellow-600 dark:text-yellow-400 mb-2">
                                        Заблокировано
                                    </div>
                                @endif
                                <form
                                    method="POST"
                                    action="{{ route('admin.bank-storages.destroy', $storage) }}"
                                    onsubmit="return confirm('Изъять предмет из ячейки #{{ $storage->slot_number }}?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="w-full text-xs px-2 py-1 bg-red-600 dark:bg-red-700 text-white rounded hover:bg-red-700 dark:hover:bg-red-600"
                                    >
                                        Изъять
                                    </button>
                                </form>
                            @else
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Пусто
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    @if($storages->isEmpty())
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 text-center">
            <p class="text-gray-500 dark:text-gray-400">У персонажа нет предметов в банковских складах</p>
        </div>
    @endif
</div>
@endsection

