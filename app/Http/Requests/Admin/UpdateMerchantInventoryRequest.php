<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMerchantInventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantity' => 'nullable|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'base_price' => 'required|integer|min:0',
            'base_sell_price' => 'nullable|integer|min:0',
            'price_multiplier' => 'nullable|numeric|min:0.1|max:5.0',
            'purchase_limit_per_day' => 'nullable|integer|min:1',
            'is_available' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'base_price.required' => 'Не указана базовая цена покупки',
            'base_price.min' => 'Цена не может быть отрицательной',
        ];
    }
}
