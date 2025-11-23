<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'type' => [
                'required',
                'string',
                Rule::in([
                    'city',
                    'forest',
                    'mountain',
                    'dungeon',
                    'river',
                    'road',
                    'field',
                    'cave',
                    'village',
                    'castle',
                    'building',
                    'street',
                    'square',
                    'bank',
                    'shop',
                ]),
            ],
            'is_safe_zone' => [
                'nullable',
                'boolean',
            ],
            'min_level' => [
                'nullable',
                'integer',
                'min:1',
                'max:255',
            ],
            'max_level' => [
                'nullable',
                'integer',
                'min:1',
                'max:255',
            ],
            'coordinate_x' => [
                'required',
                'integer',
                'min:-100',
                'max:100',
            ],
            'coordinate_y' => [
                'required',
                'integer',
                'min:-100',
                'max:100',
            ],
            'image_url' => [
                'nullable',
                'string',
                'max:255',
            ],
            'background_music' => [
                'nullable',
                'string',
                'max:255',
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
            'name.required' => 'Название локации обязательно.',
            'type.required' => 'Тип локации обязателен.',
            'coordinate_x.required' => 'Координата X обязательна.',
            'coordinate_y.required' => 'Координата Y обязательна.',
        ];
    }
}
