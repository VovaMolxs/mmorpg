@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
        <h1 class="text-2xl font-semibold mb-6">Регистрация</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium mb-2">Имя пользователя</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    required
                    autofocus
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    placeholder="3-20 символов, буквы, цифры, подчеркивания"
                >
                @error('username')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium mb-2">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium mb-2">Пароль</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    placeholder="Минимум 8 символов"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium mb-2">Подтверждение пароля</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
            </div>

            <div class="mb-4">
                <label for="preferred_language" class="block text-sm font-medium mb-2">Предпочитаемый язык</label>
                <select
                    id="preferred_language"
                    name="preferred_language"
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                    <option value="en" {{ old('preferred_language', 'en') === 'en' ? 'selected' : '' }}>English</option>
                    <option value="ru" {{ old('preferred_language') === 'ru' ? 'selected' : '' }}>Русский</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="flex items-start">
                    <input
                        type="checkbox"
                        name="terms"
                        required
                        class="mt-1 mr-2"
                    >
                    <span class="text-sm">Я принимаю <a href="#" class="text-[#f53003] dark:text-[#FF4433] underline">пользовательское соглашение</a></span>
                </label>
                @error('terms')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white font-medium"
            >
                Зарегистрироваться
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm">
                Уже есть аккаунт?
                <a href="{{ route('login') }}" class="text-[#f53003] dark:text-[#FF4433] underline">Войти</a>
            </p>
        </div>
    </div>
</div>
@endsection

