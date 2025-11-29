<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMonsterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['beast', 'humanoid', 'undead', 'elemental', 'demon', 'magical'])],
            'level' => ['required', 'integer', 'min:1', 'max:100'],
            'rank' => ['required', 'string', Rule::in(['normal', 'elite', 'boss', 'world_boss'])],
            'faction_id' => ['nullable', 'integer'],
            'ai_behavior' => ['required', 'string', Rule::in(['passive', 'neutral', 'aggressive'])],
            'respawn_time' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],

            // Статистика монстра
            'stats.health_max' => ['required', 'integer', 'min:1'],
            'stats.mana_max' => ['required', 'integer', 'min:0'],
            'stats.attack_power' => ['required', 'integer', 'min:0'],
            'stats.magic_power' => ['required', 'integer', 'min:0'],
            'stats.defense' => ['required', 'integer', 'min:0'],
            'stats.magic_defense' => ['required', 'integer', 'min:0'],
            'stats.accuracy' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stats.magic_accuracy' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stats.dodge' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stats.critical_chance' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stats.critical_power' => ['nullable', 'numeric', 'min:0'],
            'stats.experience_reward' => ['required', 'integer', 'min:0'],
            'stats.attack_type' => ['required', 'string', Rule::in(['physical', 'magical', 'hybrid'])],
        ];
    }
}
