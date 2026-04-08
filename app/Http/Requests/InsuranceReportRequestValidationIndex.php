<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InsuranceReportRequestValidationIndex extends FormRequest
{
   public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'      => 'nullable|string|max:100',
            'partner_id' => 'nullable|integer|min:1',
            'limit'       => 'nullable|integer|min:1|max:100',
            'offset'      => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'partner_id.integer' => 'Partner ID harus berupa angka.',
            'limit.max'           => 'Limit maksimal 100.',
            'offset.min'          => 'Offset tidak boleh negatif.',
        ];
    }
}
