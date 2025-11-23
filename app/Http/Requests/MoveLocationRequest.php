<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'direction' => [
                'required',
                'string',
                'in:north,south,east,west',
            ],
            'custom_name' => [
                'nullable',
                'string',
                'max:100',
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
            'direction.required' => 'Направление обязательно для указания.',
            'direction.in' => 'Неверное направление. Используйте: north, south, east, west.',
            'custom_name.max' => 'Кастомное название не должно превышать :max символов.',
        ];
    }
}
