<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RestoreCharacterRequest extends FormRequest
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
            'health_amount' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'mana_amount' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'full_restore' => [
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
            'health_amount.min' => 'Количество здоровья не может быть отрицательным.',
            'mana_amount.min' => 'Количество маны не может быть отрицательным.',
        ];
    }
}
