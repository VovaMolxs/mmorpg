<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCharacterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->canCreateCharacter();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Zа-яА-Я0-9_\s]+$/u',
                'unique:characters,name',
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
            'name.required' => 'Имя персонажа обязательно для заполнения.',
            'name.min' => 'Имя персонажа должно содержать минимум :min символов.',
            'name.max' => 'Имя персонажа не должно превышать :max символов.',
            'name.regex' => 'Имя персонажа может содержать только буквы, цифры, подчеркивания и пробелы.',
            'name.unique' => 'Персонаж с таким именем уже существует.',
        ];
    }
}
