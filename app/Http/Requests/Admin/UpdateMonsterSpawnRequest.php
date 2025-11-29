<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMonsterSpawnRequest extends FormRequest
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
            'monster_id' => ['sometimes', 'required', 'exists:monsters,id'],
            'location_id' => ['sometimes', 'required', 'exists:locations,id'],
            'min_instances' => ['sometimes', 'required', 'integer', 'min:0'],
            'max_instances' => ['sometimes', 'required', 'integer', 'min:1', 'gte:min_instances'],
            'respawn_time_min' => ['sometimes', 'required', 'integer', 'min:1'],
            'respawn_time_max' => ['sometimes', 'required', 'integer', 'min:1', 'gte:respawn_time_min'],
            'spawn_chance' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'spawn_radius' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'spawn_schedule.type' => ['nullable', 'string', 'in:always,day,night,custom'],
            'spawn_schedule.hours' => ['nullable', 'array'],
            'spawn_schedule.hours.*' => ['integer', 'min:0', 'max:23'],
        ];
    }
}
