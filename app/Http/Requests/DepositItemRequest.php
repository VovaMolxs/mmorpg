<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'banker_id' => 'required|integer|exists:bankers,id',
            'item_instance_id' => 'required|integer|exists:item_instances,id',
            'quantity' => 'nullable|integer|min:1',
            'slot_number' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'banker_id.required' => 'Не указан банкир',
            'banker_id.exists' => 'Банкир не найден',
            'item_instance_id.required' => 'Не указан предмет для депозита',
            'item_instance_id.exists' => 'Предмет не найден',
            'quantity.min' => 'Количество должно быть не менее 1',
            'slot_number.min' => 'Номер ячейки должен быть не менее 1',
        ];
    }
}
