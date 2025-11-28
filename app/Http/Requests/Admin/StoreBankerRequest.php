<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankerRequest extends FormRequest
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
            'npc_id' => ['required', 'integer', 'exists:npcs,id', 'unique:bankers,npc_id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'storage_slots' => ['required', 'integer', 'min:1', 'max:1000'],
            'base_fee' => ['required', 'integer', 'min:0'],
            'fee_per_slot' => ['required', 'integer', 'min:0'],
            'max_upgrade_slots' => ['required', 'integer', 'min:1', 'max:1000'],
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
            'npc_id.required' => 'NPC обязателен для заполнения.',
            'npc_id.exists' => 'Выбранный NPC не найден.',
            'npc_id.unique' => 'Этот NPC уже является банкиром.',
            'location_id.required' => 'Локация обязательна для заполнения.',
            'location_id.exists' => 'Выбранная локация не найдена.',
            'storage_slots.required' => 'Количество базовых слотов обязательно для заполнения.',
            'storage_slots.min' => 'Количество базовых слотов должно быть не менее 1.',
            'base_fee.required' => 'Базовая плата обязательна для заполнения.',
            'base_fee.min' => 'Базовая плата не может быть отрицательной.',
            'fee_per_slot.required' => 'Плата за слот обязательна для заполнения.',
            'fee_per_slot.min' => 'Плата за слот не может быть отрицательной.',
            'max_upgrade_slots.required' => 'Максимальное количество улучшенных слотов обязательно для заполнения.',
            'max_upgrade_slots.min' => 'Максимальное количество улучшенных слотов должно быть не менее 1.',
        ];
    }
}
