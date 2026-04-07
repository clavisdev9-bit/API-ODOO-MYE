<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryBalanceRequestValidationIndex extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'      => 'nullable|string|max:100',
            'location_id' => 'nullable|integer|min:1',
            'brand'       => 'nullable|string|max:100',
            'limit'       => 'nullable|integer|min:1|max:1000',
            'offset'      => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.integer' => 'Location ID harus berupa angka.',
            'limit.max'           => 'Limit maksimal 1000.',
            'offset.min'          => 'Offset tidak boleh negatif.',
        ];
    }
}