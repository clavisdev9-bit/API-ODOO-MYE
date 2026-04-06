<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourierPriceListRequestValidationIndex extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'       => 'nullable|string|max:100',
            'customer_id'  => 'nullable|integer|min:1',
            'freight_type' => 'nullable|string|in:LAND,SEA,AIR',
            'vendor'       => 'nullable|string|max:100',
            'limit'        => 'nullable|integer|min:1|max:100',
            'offset'       => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'freight_type.in'     => 'Freight type harus salah satu dari: LAND, SEA, AIR.',
            'customer_id.integer' => 'Customer ID harus berupa angka.',
            'limit.max'           => 'Limit maksimal 100.',
            'offset.min'          => 'Offset tidak boleh negatif.',
        ];
    }
}