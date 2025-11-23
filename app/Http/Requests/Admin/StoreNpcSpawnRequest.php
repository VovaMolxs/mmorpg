<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNpcSpawnRequest extends FormRequest
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
            'npc_id' => [
                'required',
                'integer',
                Rule::exists('npcs', 'id'),
            ],
            'location_id' => [
                'required',
                'integer',
                Rule::exists('locations', 'id'),
            ],
            'min_instances' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],
            'max_instances' => [
                'required',
                'integer',
                'min:1',
                'max:100',
                'gte:min_instances',
            ],
            'respawn_time_min' => [
                'required',
                'integer',
                'min:1',
                'max:1440',
            ],
            'respawn_time_max' => [
                'required',
                'integer',
                'min:1',
                'max:1440',
                'gte:respawn_time_min',
            ],
            'spawn_chance' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'spawn_radius' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000',
            ],
            'spawn_schedule.type' => [
                'nullable',
                'string',
                Rule::in(['always', 'day', 'night', 'custom']),
            ],
            'spawn_schedule.hours' => [
                'nullable',
                'array',
            ],
            'spawn_schedule.hours.*' => [
                'integer',
                'min:0',
                'max:23',
            ],
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
            'npc_id.required' => 'NPC обязателен.',
            'npc_id.exists' => 'Выбранный NPC не существует.',
            'location_id.required' => 'Локация обязательна.',
            'location_id.exists' => 'Выбранная локация не существует.',
            'min_instances.required' => 'Минимальное количество экземпляров обязательно.',
            'max_instances.required' => 'Максимальное количество экземпляров обязательно.',
            'max_instances.gte' => 'Максимальное количество должно быть больше или равно минимальному.',
            'respawn_time_min.required' => 'Минимальное время респавна обязательно.',
            'respawn_time_max.gte' => 'Максимальное время респавна должно быть больше или равно минимальному.',
            'spawn_chance.required' => 'Шанс спавна обязателен.',
        ];
    }
}
