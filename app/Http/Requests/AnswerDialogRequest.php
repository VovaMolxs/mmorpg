<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerDialogRequest extends FormRequest
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
            'dialog_id' => [
                'required',
                'integer',
                'exists:dialogs,id',
            ],
            'answer_id' => [
                'required',
                'integer',
                'exists:dialog_answers,id',
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
            'dialog_id.required' => 'ID диалога обязателен для указания.',
            'dialog_id.exists' => 'Указанный диалог не найден.',
            'answer_id.required' => 'ID ответа обязателен для указания.',
            'answer_id.exists' => 'Указанный ответ не найден.',
        ];
    }
}
