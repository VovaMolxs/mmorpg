@extends('layouts.app')

@section('title', 'Подтверждение Email')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
        <h1 class="text-2xl font-semibold mb-6">Подтверждение Email</h1>

        <div class="mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Спасибо за регистрацию! Прежде чем продолжить, пожалуйста, подтвердите ваш email адрес, перейдя по ссылке в письме, которое мы вам отправили.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
                Новая ссылка для подтверждения была отправлена на ваш email адрес.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button
                type="submit"
                class="w-full px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white font-medium"
            >
                Отправить письмо повторно
            </button>
        </form>

        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-[#f53003] dark:text-[#FF4433] underline">
                    Выйти
                </button>
            </form>
        </div>
    </div>
</div>
@endsection



