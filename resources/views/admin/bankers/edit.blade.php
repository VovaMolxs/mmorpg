@extends('layouts.app')

@section('title', 'Редактировать банкира')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Редактировать банкира</h1>
        <a
            href="{{ route('admin.bankers.index') }}"
            class="text-[#f53003] dark:text-[#FF4433] hover:underline"
        >
            ← Назад к списку
        </a>
    </div>

    <form method="POST" action="{{ route('admin.bankers.update', $banker) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>

            <div>
                <label for="npc_id" class="block text-sm font-medium mb-1">NPC *</label>
                <select
                    id="npc_id"
                    name="npc_id"
                    required
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                    <option value="">Выберите NPC</option>
                    @foreach($npcs as $npc)
                        <option value="{{ $npc->id }}" {{ (old('npc_id', $banker->npc_id) == $npc->id) ? 'selected' : '' }}>
                            {{ $npc->name }} @if($npc->location) ({{ $npc->location->name }}) @endif
                        </option>
                    @endforeach
                </select>
                @error('npc_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="location_id" class="block text-sm font-medium mb-1">Локация *</label>
                <select
                    id="location_id"
                    name="location_id"
                    required
                    class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                >
                    <option value="">Выберите локацию</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ (old('location_id', $banker->location_id) == $location->id) ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                @error('location_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="storage_slots" class="block text-sm font-medium mb-1">Базовые слоты *</label>
                    <input
                        type="number"
                        id="storage_slots"
                        name="storage_slots"
                        value="{{ old('storage_slots', $banker->storage_slots) }}"
                        min="1"
                        max="1000"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('storage_slots')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="max_upgrade_slots" class="block text-sm font-medium mb-1">Макс. улучшенных слотов *</label>
                    <input
                        type="number"
                        id="max_upgrade_slots"
                        name="max_upgrade_slots"
                        value="{{ old('max_upgrade_slots', $banker->max_upgrade_slots) }}"
                        min="1"
                        max="1000"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('max_upgrade_slots')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="base_fee" class="block text-sm font-medium mb-1">Базовая плата (золото) *</label>
                    <input
                        type="number"
                        id="base_fee"
                        name="base_fee"
                        value="{{ old('base_fee', $banker->base_fee) }}"
                        min="0"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('base_fee')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fee_per_slot" class="block text-sm font-medium mb-1">Плата за слот (золото) *</label>
                    <input
                        type="number"
                        id="fee_per_slot"
                        name="fee_per_slot"
                        value="{{ old('fee_per_slot', $banker->fee_per_slot) }}"
                        min="0"
                        required
                        class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#0a0a0a] focus:outline-none focus:ring-2 focus:ring-[#f53003] dark:focus:ring-[#FF4433]"
                    >
                    @error('fee_per_slot')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button
                type="submit"
                class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white"
            >
                Сохранить изменения
            </button>
            <a
                href="{{ route('admin.bankers.index') }}"
                class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:bg-gray-50 dark:hover:bg-[#0a0a0a]"
            >
                Отмена
            </a>
        </div>
    </form>
</div>
@endsection

