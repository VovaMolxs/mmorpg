<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDialogRequest extends FormRequest
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
            'npc_id' => ['required', 'exists:npcs,id'],
            'parent_dialog_id' => ['nullable', 'exists:dialogs,id'],
            'text' => ['required', 'string', 'max:5000'],
            'is_initial' => ['nullable', 'boolean'],
            'min_level' => ['nullable', 'integer', 'min:1', 'max:100'],
            'required_quest_id' => ['nullable', 'exists:quests,id'],
            'required_quest_status' => ['nullable', Rule::in(['active', 'completed', 'failed', 'abandoned'])],
            'answers' => ['nullable', 'array'],
            'answers.*.text' => ['required_with:answers', 'string', 'max:500'],
            'answers.*.next_dialog_id' => ['nullable', 'exists:dialogs,id'],
            'answers.*.quest_trigger_id' => ['nullable', 'exists:quests,id'],
            'answers.*.item_required_id' => ['nullable', 'exists:items,id'],
            'answers.*.skill_required' => ['nullable', 'exists:skills,id'],
            'answers.*.skill_level_required' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'npc_id.required' => 'NPC обязателен для заполнения.',
            'npc_id.exists' => 'Выбранный NPC не найден.',
            'text.required' => 'Текст диалога обязателен для заполнения.',
            'parent_dialog_id.exists' => 'Родительский диалог не найден.',
            'required_quest_id.exists' => 'Требуемый квест не найден.',
        ];
    }
}
