<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResurrectionStoneRequest extends FormRequest
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
            'location_id' => ['sometimes', 'required', 'integer', 'exists:locations,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'level_required' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'cooldown_minutes' => ['sometimes', 'required', 'integer', 'min:0', 'max:1440'],
            'visual_effect' => ['sometimes', 'required', 'string', 'in:glow,particles,aura'],
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
            'location_id.exists' => 'Выбранная локация не найдена.',
            'name.required' => 'Название камня обязательно для заполнения.',
            'name.max' => 'Название не должно превышать :max символов.',
            'level_required.min' => 'Минимальный уровень должен быть не менее 1.',
            'level_required.max' => 'Минимальный уровень не должен превышать 100.',
            'cooldown_minutes.min' => 'Время перезарядки не может быть отрицательным.',
            'cooldown_minutes.max' => 'Время перезарядки не должно превышать 1440 минут (24 часа).',
            'visual_effect.in' => 'Неверный тип визуального эффекта. Используйте: glow, particles, aura.',
        ];
    }
}
