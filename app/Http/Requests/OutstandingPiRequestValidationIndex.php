<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OutstandingPiRequestValidationIndex extends FormRequest
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

    public function messages(): array
    {
        return [
            'limit.integer' => 'Limit harus berupa angka.',
            'limit.min' => 'Limit minimal 1.',

            'offset.integer' => 'Offset harus berupa angka.',
            'offset.min' => 'Offset minimal 0.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'limit' => $this->has('limit') ? (int) $this->input('limit') : 10,
            'offset' => $this->has('offset') ? (int) $this->input('offset') : 0,
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validasi gagal',
            'errors' => $validator->errors(),
        ], 422));
    }
}