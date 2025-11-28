<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpgradeStorageRequest extends FormRequest
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
            'additional_slots' => 'required|integer|min:1|max:50',
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
            'additional_slots.required' => 'Не указано количество дополнительных слотов',
            'additional_slots.min' => 'Количество дополнительных слотов должно быть не менее 1',
            'additional_slots.max' => 'Количество дополнительных слотов не может превышать 50',
        ];
    }
}
