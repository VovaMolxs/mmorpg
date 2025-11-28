<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNpcRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', Rule::in(['trader', 'teacher', 'quest_giver', 'simple', 'hostile'])],
            'location_id' => ['nullable', 'exists:locations,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_merchant' => ['nullable', 'boolean'],
            'is_teacher' => ['nullable', 'boolean'],
            'is_quest_giver' => ['nullable', 'boolean'],
            'is_hostile' => ['nullable', 'boolean'],
            'faction_id' => ['nullable', 'integer'],
            'ai_behavior' => ['sometimes', 'required', 'string', Rule::in(['passive', 'neutral', 'aggressive'])],
            'respawn_time' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'merchant_buy_types' => ['nullable', 'array'],
            'merchant_buy_types.*' => ['string', Rule::in(['weapon', 'armor', 'jewelry', 'potion', 'resource', 'rune', 'scroll'])],

            // Статистика NPC
            'stats.level' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'stats.health_max' => ['sometimes', 'required', 'integer', 'min:1'],
            'stats.health_current' => ['nullable', 'integer', 'min:0'],
            'stats.mana_max' => ['nullable', 'integer', 'min:0'],
            'stats.mana_current' => ['nullable', 'integer', 'min:0'],
            'stats.strength' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'stats.agility' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'stats.intelligence' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'stats.attack_power' => ['nullable', 'integer', 'min:0'],
            'stats.defense' => ['nullable', 'integer', 'min:0'],
            'stats.magic_defense' => ['nullable', 'integer', 'min:0'],
            'stats.accuracy' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stats.dodge' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stats.critical_chance' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stats.critical_power' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'stats.experience_reward' => ['nullable', 'integer', 'min:0'],
            'stats.gold_reward_min' => ['nullable', 'integer', 'min:0'],
            'stats.gold_reward_max' => ['nullable', 'integer', 'min:0'],

            // Лут
            'loot.*.item_id' => ['nullable', 'exists:items,id'],
            'loot.*.min_quantity' => ['nullable', 'integer', 'min:1'],
            'loot.*.max_quantity' => ['nullable', 'integer', 'min:1'],
            'loot.*.drop_chance' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Навыки
            'skills.*.skill_id' => ['nullable', 'exists:skills,id'],
            'skills.*.level' => ['nullable', 'integer', 'min:1', 'max:100'],
            'skills.*.is_active' => ['nullable', 'boolean'],

            // Экипировка
            'equipment.*.item_id' => ['nullable', 'exists:items,id'],
            'equipment.*.slot' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название NPC обязательно для заполнения.',
            'type.required' => 'Тип NPC обязателен для заполнения.',
            'ai_behavior.required' => 'Поведение AI обязательно для заполнения.',
            'stats.level.required' => 'Уровень NPC обязателен для заполнения.',
            'stats.health_max.required' => 'Максимальное здоровье обязательно для заполнения.',
            'stats.strength.required' => 'Сила обязательна для заполнения.',
            'stats.agility.required' => 'Ловкость обязательна для заполнения.',
            'stats.intelligence.required' => 'Интеллект обязателен для заполнения.',
        ];
    }
}
