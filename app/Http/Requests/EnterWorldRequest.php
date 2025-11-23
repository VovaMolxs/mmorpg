<?php

namespace App\Http\Requests;

use App\Models\Character;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnterWorldRequest extends FormRequest
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
            'character_id' => [
                'required',
                'integer',
                Rule::exists('characters', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id)
                        ->where('is_active', true);
                }),
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
            'character_id.required' => 'Необходимо выбрать персонажа.',
            'character_id.exists' => 'Выбранный персонаж не существует или недоступен.',
        ];
    }

    /**
     * Get the validated character instance.
     */
    public function getCharacter(): Character
    {
        return Character::findOrFail($this->validated()['character_id']);
    }
}
