@extends('layouts.app')

@section('title', 'Панель управления')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-semibold mb-6">Добро пожаловать, {{ $user->username }}!</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Статус аккаунта</h2>
            <p class="text-sm mb-2">
                <span class="font-medium">Статус:</span>
                <span class="capitalize">{{ $user->account_status->value }}</span>
            </p>
            <p class="text-sm mb-2">
                <span class="font-medium">Email:</span>
                {{ $user->email }}
            </p>
            <p class="text-sm mb-2">
                <span class="font-medium">Верифицирован:</span>
                {{ $user->isVerified() ? 'Да' : 'Нет' }}
            </p>
            @if($user->last_login_at)
                <p class="text-sm">
                    <span class="font-medium">Последний вход:</span>
                    {{ $user->last_login_at->format('d.m.Y H:i') }}
                </p>
            @endif
        </div>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Игровая статистика</h2>
            <p class="text-sm mb-2">
                <span class="font-medium">Максимум персонажей:</span>
                {{ $user->max_characters }}
            </p>
            <p class="text-sm mb-2">
                <span class="font-medium">Всего времени в игре:</span>
                {{ gmdate('H:i:s', $user->time_played_total) }}
            </p>
            <p class="text-sm">
                <span class="font-medium">Язык:</span>
                {{ $user->preferred_language }}
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Система персонажей</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
            Система создания персонажей будет доступна в ближайшее время.
        </p>
        <div class="bg-gray-100 dark:bg-[#0a0a0a] rounded p-4 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Здесь будет отображаться список ваших персонажей
            </p>
        </div>
    </div>

    @if(!$user->isVerified())
        <div class="mt-6 bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 rounded-lg p-4">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                Пожалуйста, подтвердите ваш email адрес. Проверьте почту для получения ссылки подтверждения.
            </p>
        </div>
    @endif
</div>
@endsection

