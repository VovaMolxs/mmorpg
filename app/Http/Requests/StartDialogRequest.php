<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartDialogRequest extends FormRequest
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
            'npc_id' => [
                'required',
                'integer',
                'exists:npcs,id',
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
            'npc_id.required' => 'ID NPC обязателен для указания.',
            'npc_id.exists' => 'Указанный NPC не найден.',
        ];
    }
}
