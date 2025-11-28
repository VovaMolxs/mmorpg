<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreResurrectionStoneRequest extends FormRequest
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
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'level_required' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'cooldown_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'visual_effect' => ['required', 'string', 'in:glow,particles,aura'],
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
            'location_id.required' => 'Локация обязательна для заполнения.',
            'location_id.exists' => 'Выбранная локация не найдена.',
            'name.required' => 'Название камня обязательно для заполнения.',
            'name.max' => 'Название не должно превышать :max символов.',
            'level_required.required' => 'Минимальный уровень обязателен для заполнения.',
            'level_required.min' => 'Минимальный уровень должен быть не менее 1.',
            'level_required.max' => 'Минимальный уровень не должен превышать 100.',
            'cooldown_minutes.required' => 'Время перезарядки обязательно для заполнения.',
            'cooldown_minutes.min' => 'Время перезарядки не может быть отрицательным.',
            'cooldown_minutes.max' => 'Время перезарядки не должно превышать 1440 минут (24 часа).',
            'visual_effect.required' => 'Визуальный эффект обязателен для заполнения.',
            'visual_effect.in' => 'Неверный тип визуального эффекта. Используйте: glow, particles, aura.',
        ];
    }
}
