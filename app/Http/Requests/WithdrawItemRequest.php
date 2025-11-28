<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawItemRequest extends FormRequest
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
            'storage_id' => 'required|integer|exists:bank_storages,id',
            'quantity' => 'nullable|integer|min:1',
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
            'storage_id.required' => 'Не указана ячейка хранилища',
            'storage_id.exists' => 'Ячейка хранилища не найдена',
            'quantity.min' => 'Количество должно быть не менее 1',
        ];
    }
}
