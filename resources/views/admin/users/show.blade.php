@extends('layouts.app')

@section('title', 'Пользователь: ' . $user->username)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold">Пользователь: {{ $user->username }}</h1>
        <a
            href="{{ route('admin.users.index') }}"
            class="px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b]"
        >
            Назад к списку
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium">ID:</span> {{ $user->id }}</p>
                <p><span class="font-medium">Имя пользователя:</span> {{ $user->username }}</p>
                <p><span class="font-medium">Email:</span> {{ $user->email }}</p>
                <p><span class="font-medium">Email верифицирован:</span> {{ $user->isVerified() ? 'Да' : 'Нет' }}</p>
                <p><span class="font-medium">Администратор:</span> {{ $user->is_admin ? 'Да' : 'Нет' }}</p>
                <p><span class="font-medium">Дата регистрации:</span> {{ $user->created_at->format('d.m.Y H:i') }}</p>
                @if($user->last_login_at)
                    <p><span class="font-medium">Последний вход:</span> {{ $user->last_login_at->format('d.m.Y H:i') }}</p>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Игровая информация</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium">Статус аккаунта:</span> <span class="capitalize">{{ $user->account_status->value }}</span></p>
                <p><span class="font-medium">Максимум персонажей:</span> {{ $user->max_characters }}</p>
                <p><span class="font-medium">Язык:</span> {{ $user->preferred_language }}</p>
                <p><span class="font-medium">Время в игре:</span> {{ gmdate('H:i:s', $user->time_played_total) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Управление статусом</h2>
        <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="mb-4">
            @csrf
            @method('PATCH')
            <div class="flex gap-4 items-end">
                <div class="flex-1">
                    <label for="account_status" class="block text-sm font-medium mb-2">Статус аккаунта</label>
                    <select
                        id="account_status"
                        name="account_status"
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                        @foreach(\App\AccountStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ $user->account_status === $status ? 'selected' : '' }}>
                                {{ ucfirst($status->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button
                    type="submit"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
                >
                    Обновить статус
                </button>
            </div>
        </form>

        <div class="flex gap-4">
            @if($user->isBanned())
                <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                    @csrf
                    <button
                        type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                    >
                        Разблокировать
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.users.ban', $user) }}">
                    @csrf
                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Заблокировать
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Лимит персонажей</h2>
        <form method="POST" action="{{ route('admin.users.update-max-characters', $user) }}">
            @csrf
            @method('PATCH')
            <div class="flex gap-4 items-end">
                <div class="flex-1">
                    <label for="max_characters" class="block text-sm font-medium mb-2">Максимум персонажей</label>
                    <input
                        type="number"
                        id="max_characters"
                        name="max_characters"
                        value="{{ $user->max_characters }}"
                        min="1"
                        max="10"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                </div>
                <button
                    type="submit"
                    class="px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
                >
                    Обновить лимит
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

