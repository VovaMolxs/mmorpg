<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMerchantInventoryRequest extends FormRequest
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
        $npcId = $this->route('npc')->id;

        return [
            'item_id' => [
                'required',
                'integer',
                'exists:items,id',
                Rule::unique('merchant_inventories')->where(function ($query) use ($npcId) {
                    return $query->where('npc_id', $npcId);
                }),
            ],
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
            'item_id.required' => 'Не указан предмет',
            'item_id.exists' => 'Предмет не найден',
            'item_id.unique' => 'Этот предмет уже есть в ассортименте торговца',
            'base_price.required' => 'Не указана базовая цена покупки',
            'base_price.min' => 'Цена не может быть отрицательной',
        ];
    }
}
