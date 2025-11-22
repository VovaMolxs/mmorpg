<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveItemRequest extends FormRequest
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
            'item_instance_id' => [
                'required',
                'integer',
                'exists:item_instances,id',
            ],
            'target_location_type' => [
                'required',
                'string',
                'in:inventory,equipped,ground,container,vendor,auction',
            ],
            'target_location_id' => [
                'nullable',
                'integer',
            ],
            'position_x' => [
                'nullable',
                'integer',
            ],
            'position_y' => [
                'nullable',
                'integer',
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
            'item_instance_id.required' => 'Необходимо указать экземпляр предмета.',
            'item_instance_id.exists' => 'Указанный предмет не существует.',
            'target_location_type.required' => 'Необходимо указать тип расположения.',
            'target_location_type.in' => 'Недопустимый тип расположения.',
        ];
    }
}
