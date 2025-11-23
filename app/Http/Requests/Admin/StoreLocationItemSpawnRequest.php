<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationItemSpawnRequest extends FormRequest
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
            'location_id' => [
                'required',
                'integer',
                Rule::exists('locations', 'id'),
            ],
            'item_id' => [
                'required',
                'integer',
                Rule::exists('items', 'id'),
            ],
            'min_quantity' => [
                'required',
                'integer',
                'min:1',
                'max:1000',
            ],
            'max_quantity' => [
                'required',
                'integer',
                'min:1',
                'max:1000',
                'gte:min_quantity',
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
            'max_instances' => [
                'required',
                'integer',
                'min:1',
                'max:100',
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
            'location_id.required' => 'Локация обязательна.',
            'location_id.exists' => 'Выбранная локация не существует.',
            'item_id.required' => 'Предмет обязателен.',
            'item_id.exists' => 'Выбранный предмет не существует.',
            'min_quantity.required' => 'Минимальное количество обязательно.',
            'min_quantity.min' => 'Минимальное количество должно быть не менее 1.',
            'max_quantity.required' => 'Максимальное количество обязательно.',
            'max_quantity.gte' => 'Максимальное количество должно быть больше или равно минимальному.',
            'respawn_time_min.required' => 'Минимальное время респавна обязательно.',
            'respawn_time_max.gte' => 'Максимальное время респавна должно быть больше или равно минимальному.',
            'spawn_chance.required' => 'Шанс спавна обязателен.',
            'spawn_chance.min' => 'Шанс спавна должен быть от 1 до 100.',
            'spawn_chance.max' => 'Шанс спавна должен быть от 1 до 100.',
        ];
    }
}
