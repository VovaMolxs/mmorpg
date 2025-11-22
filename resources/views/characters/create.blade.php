@extends('layouts.app')

@section('title', 'Создание персонажа')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-semibold mb-6">Создание персонажа</h1>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
        <form method="POST" action="{{ route('characters.store') }}" id="character-form">
            @csrf

            <div class="mb-6">
                <label for="name" class="block text-sm font-medium mb-2">Имя персонажа</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    placeholder="3-20 символов"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                <p class="text-sm text-blue-800 dark:text-blue-200 mb-2">
                    <strong>Примечание:</strong> Персонаж будет создан с базовыми характеристиками (Сила: 1, Ловкость: 1, Интеллект: 1).
                </p>
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    Характеристики и навыки можно будет улучшить позже в игре за игровые деньги.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <button
                    type="submit"
                    class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white font-medium w-full sm:w-auto"
                >
                    Создать персонажа
                </button>
                <a
                    href="{{ route('characters.index') }}"
                    class="px-6 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b] text-center w-full sm:w-auto"
                >
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

