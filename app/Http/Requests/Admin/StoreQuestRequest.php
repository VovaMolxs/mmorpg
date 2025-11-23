<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'min_level' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_level' => ['nullable', 'integer', 'min:1', 'max:100'],
            'quest_giver_npc_id' => ['nullable', 'exists:npcs,id'],
            'turn_in_npc_id' => ['nullable', 'exists:npcs,id'],
            'previous_quest_id' => ['nullable', 'exists:quests,id'],
            'faction_required_id' => ['nullable', 'integer'],
            'reputation_required' => ['nullable', 'integer'],
            'objectives' => ['required', 'array', 'min:1'],
            'objectives.*.type' => ['required', Rule::in(['kill', 'collect', 'talk', 'explore', 'craft'])],
            'objectives.*.target_id' => ['nullable', 'integer'],
            'objectives.*.target_name' => ['nullable', 'string', 'max:255'],
            'objectives.*.required_count' => ['required', 'integer', 'min:1'],
            'objectives.*.description' => ['nullable', 'string', 'max:1000'],
            'rewards' => ['nullable', 'array'],
            'rewards.*.type' => ['required_with:rewards', Rule::in(['experience', 'gold', 'item', 'reputation', 'skill'])],
            'rewards.*.reward_id' => ['nullable', 'integer'],
            'rewards.*.quantity' => ['required_with:rewards', 'integer', 'min:1'],
            'rewards.*.is_choice' => ['nullable', 'boolean'],
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
            'name.required' => 'Название квеста обязательно для заполнения.',
            'description.required' => 'Описание квеста обязательно для заполнения.',
            'objectives.required' => 'Необходимо добавить хотя бы одну цель квеста.',
            'objectives.min' => 'Необходимо добавить хотя бы одну цель квеста.',
            'quest_giver_npc_id.exists' => 'NPC, выдающий квест, не найден.',
            'turn_in_npc_id.exists' => 'NPC, которому сдается квест, не найден.',
            'previous_quest_id.exists' => 'Предыдущий квест не найден.',
        ];
    }
}
