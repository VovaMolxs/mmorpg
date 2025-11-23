@extends('layouts.app')

@section('title', 'Мои персонажи')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-semibold">Мои персонажи</h1>
        @if(count($characters) < $maxCharacters)
            <a
                href="{{ route('characters.create') }}"
                class="px-3 sm:px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white font-medium whitespace-nowrap text-sm sm:text-base w-full sm:w-auto text-center"
            >
                Создать персонажа
            </a>
        @endif
    </div>

    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Персонажей: {{ count($characters) }} / {{ $maxCharacters }}
    </p>

    @if(count($characters) === 0)
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-8 text-center">
            <p class="text-gray-600 dark:text-gray-400 mb-4">У вас пока нет персонажей</p>
            <a
                href="{{ route('characters.create') }}"
                class="inline-block px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Создать первого персонажа
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($characters as $character)
                @php
                    $session = $sessions[$character->id] ?? null;
                    $presence = $presences[$character->id] ?? null;
                    $isInGame = $session || ($presence && $presence->isOnline());
                @endphp
                <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h2 class="text-xl font-semibold">{{ $character->name }}</h2>
                        @if($isInGame)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                В игре
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $character->description }}</p>
                    
                    <div class="space-y-2 text-sm mb-4">
                        <p><span class="font-medium">Уровень:</span> {{ $character->level }}</p>
                        <p><span class="font-medium">Сила:</span> {{ $character->strength }}</p>
                        <p><span class="font-medium">Ловкость:</span> {{ $character->agility }}</p>
                        <p><span class="font-medium">Интеллект:</span> {{ $character->intelligence }}</p>
                        <p><span class="font-medium">Здоровье:</span> {{ $character->health_current }} / {{ $character->health_max }}</p>
                        <p><span class="font-medium">Мана:</span> {{ $character->mana_current }} / {{ $character->mana_max }}</p>
                        @if($character->location)
                            <p><span class="font-medium">Локация:</span> {{ $character->location->name }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col gap-2">
                        @if($isInGame)
                            <a
                                href="{{ route('game') }}"
                                class="w-full text-center px-4 py-2 bg-green-600 dark:bg-green-700 text-white rounded hover:bg-green-700 dark:hover:bg-green-600 font-medium"
                            >
                                Вернуться в игру
                            </a>
                        @else
                            <button
                                onclick="enterWorld({{ $character->id }})"
                                class="w-full px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white font-medium"
                                id="enter-world-btn-{{ $character->id }}"
                            >
                                Войти в игру
                            </button>
                        @endif
                        <a
                            href="{{ route('characters.show', $character) }}"
                            class="block w-full text-center px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded hover:border-[#1915014a] dark:hover:border-[#62605b]"
                        >
                            Подробнее
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    async function enterWorld(characterId) {
        const btn = document.getElementById(`enter-world-btn-${characterId}`);
        const originalText = btn.textContent;
        
        btn.disabled = true;
        btn.textContent = 'Вход...';
        
        try {
            const response = await fetch('{{ route("api.game.enter-world") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    character_id: characterId
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Сохраняем токен сессии
                if (data.session && data.session.token) {
                    localStorage.setItem('game_session_token', data.session.token);
                }
                
                alert('Вы успешно вошли в игровой мир!');
                // Можно перенаправить на игровую страницу или обновить интерфейс
                window.location.href = '/game';
            } else {
                alert(data.error || 'Ошибка при входе в игру');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Произошла ошибка при входе в игру');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }
</script>
@endpush
@endsection

