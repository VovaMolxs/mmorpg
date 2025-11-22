<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
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
        $type = $this->input('type');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', Rule::in([
                'weapon', 'armor', 'jewelry', 'resource', 'potion',
                'consumable', 'currency', 'rune', 'scroll',
            ])],
            'subtype' => ['nullable', 'string'],
            'rarity' => ['required', 'string', Rule::in(['common', 'uncommon', 'rare', 'epic', 'legendary'])],
            'level_required' => ['nullable', 'integer', 'min:1', 'max:100'],
            'stackable' => ['nullable', 'boolean'],
            'max_stack' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'value' => ['nullable', 'integer', 'min:0'],
        ];

        // Валидация требований
        $rules['requirements.level'] = ['nullable', 'integer', 'min:1'];
        $rules['requirements.attributes.strength'] = ['nullable', 'integer', 'min:1'];
        $rules['requirements.attributes.agility'] = ['nullable', 'integer', 'min:1'];
        $rules['requirements.attributes.intelligence'] = ['nullable', 'integer', 'min:1'];
        $rules['requirements.skills.*'] = ['nullable', 'integer', 'min:1'];

        // Условная валидация в зависимости от типа
        if ($type === 'weapon') {
            $rules['weapon_data.damage_min'] = ['required', 'integer', 'min:1'];
            $rules['weapon_data.damage_max'] = ['required', 'integer', 'min:1'];
            $rules['weapon_data.attack_speed'] = ['nullable', 'numeric', 'min:0.1'];
            $rules['weapon_data.range'] = ['nullable', 'integer', 'min:1'];
            $rules['weapon_data.durability_max'] = ['nullable', 'integer', 'min:1'];
        }

        if ($type === 'armor') {
            $rules['armor_data.defense'] = ['required', 'integer', 'min:0'];
            $rules['armor_data.durability_max'] = ['nullable', 'integer', 'min:1'];
        }

        if ($type === 'jewelry') {
            $rules['jewelry_data.attributes_bonus.strength'] = ['nullable', 'integer', 'min:1'];
            $rules['jewelry_data.attributes_bonus.agility'] = ['nullable', 'integer', 'min:1'];
            $rules['jewelry_data.attributes_bonus.intelligence'] = ['nullable', 'integer', 'min:1'];
        }

        if ($type === 'potion') {
            $rules['potion_data.effect_type'] = ['required', 'string', Rule::in(['health', 'mana', 'buff'])];
            $rules['potion_data.effect_power'] = ['required', 'integer', 'min:1'];
            $rules['potion_data.duration'] = ['nullable', 'integer', 'min:0'];
            $rules['potion_data.cooldown'] = ['nullable', 'integer', 'min:0'];
        }

        if ($type === 'rune') {
            $rules['rune_data.charges'] = ['nullable', 'integer', 'min:1'];
            $rules['rune_data.recharge_time'] = ['nullable', 'integer', 'min:0'];
            $rules['rune_data.mana_cost_per_use'] = ['nullable', 'integer', 'min:0'];
        }

        if ($type === 'scroll') {
            $rules['scroll_data.is_consumable'] = ['nullable', 'boolean'];
            $rules['scroll_data.skill_level_required'] = ['nullable', 'integer', 'min:1'];
        }

        if ($type === 'resource') {
            $rules['resource_data.quality'] = ['nullable', 'integer', 'min:1', 'max:10'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название предмета обязательно для заполнения.',
            'type.required' => 'Тип предмета обязателен для заполнения.',
            'rarity.required' => 'Редкость предмета обязательна для заполнения.',
            'weapon_data.damage_min.required' => 'Минимальный урон обязателен для оружия.',
            'weapon_data.damage_max.required' => 'Максимальный урон обязателен для оружия.',
            'armor_data.defense.required' => 'Защита обязательна для брони.',
            'potion_data.effect_type.required' => 'Тип эффекта обязателен для зелья.',
            'potion_data.effect_power.required' => 'Сила эффекта обязательна для зелья.',
        ];
    }
}
