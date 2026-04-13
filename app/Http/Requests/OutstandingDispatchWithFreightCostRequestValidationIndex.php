<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutstandingDispatchWithFreightCostRequestValidationIndex extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1'],
            'offset' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'limit' => $this->has('limit') ? (int) $this->input('limit') : 10,
            'offset' => $this->has('offset') ? (int) $this->input('offset') : 0,
        ]);
    }
}