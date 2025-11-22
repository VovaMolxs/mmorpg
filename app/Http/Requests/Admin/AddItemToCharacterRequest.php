<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddItemToCharacterRequest extends FormRequest
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
            'item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
                'max:9999',
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
            'item_id.required' => 'Необходимо указать предмет.',
            'item_id.exists' => 'Указанный предмет не существует.',
            'quantity.min' => 'Количество должно быть больше 0.',
            'quantity.max' => 'Количество не может превышать 9999.',
        ];
    }
}
