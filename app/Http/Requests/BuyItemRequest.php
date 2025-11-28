<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuyItemRequest extends FormRequest
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
            'npc_id' => 'required|integer|exists:npcs,id',
            'merchant_inventory_id' => 'required|integer|exists:merchant_inventories,id',
            'quantity' => 'required|integer|min:1|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'npc_id.required' => 'Не указан NPC торговец',
            'npc_id.exists' => 'NPC торговец не найден',
            'merchant_inventory_id.required' => 'Не указан предмет для покупки',
            'merchant_inventory_id.exists' => 'Предмет не найден в ассортименте торговца',
            'quantity.required' => 'Не указано количество',
            'quantity.min' => 'Количество должно быть не менее 1',
            'quantity.max' => 'Количество не может превышать 1000',
        ];
    }
}
