<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCharacterSkillRequest extends FormRequest
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
            'skill_id' => [
                'required',
                'integer',
                'exists:skills,id',
            ],
            'level' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'experience' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'is_active' => [
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
            'skill_id.required' => 'Необходимо указать навык.',
            'skill_id.exists' => 'Указанный навык не существует.',
            'level.min' => 'Уровень должен быть больше 0.',
            'level.max' => 'Уровень не может превышать 100.',
            'experience.min' => 'Опыт не может быть отрицательным.',
        ];
    }
}
