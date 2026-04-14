<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OutstandingDoRequestValidationIndex extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limit'  => ['nullable', 'integer', 'min:1'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'limit.integer'  => 'Limit harus berupa angka.',
            'offset.integer' => 'Offset harus berupa angka.',
            'search.string'  => 'Search harus berupa text.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'limit'  => $this->has('limit') ? (int) $this->input('limit') : 10,
            'offset' => $this->has('offset') ? (int) $this->input('offset') : 0,
            'search' => $this->has('search') ? trim($this->input('search')) : null,
        ]);
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors()
        ], 422));
    }
}