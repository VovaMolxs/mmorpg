<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteQuestRequest extends FormRequest
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
            'character_quest_id' => [
                'required',
                'integer',
                'exists:character_quests,id',
            ],
            'selected_rewards' => [
                'nullable',
                'array',
            ],
            'selected_rewards.*' => [
                'integer',
                'exists:quest_rewards,id',
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
            'character_quest_id.required' => 'ID квеста персонажа обязателен для указания.',
            'character_quest_id.exists' => 'Указанный квест персонажа не найден.',
            'selected_rewards.array' => 'Выбранные награды должны быть массивом.',
            'selected_rewards.*.exists' => 'Одна из выбранных наград не найдена.',
        ];
    }
}
