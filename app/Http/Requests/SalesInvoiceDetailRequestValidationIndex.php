<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SalesInvoiceDetailRequestValidationIndex extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'move_id' => ['required', 'integer'],
            'limit'   => ['nullable', 'integer', 'min:1'],
            'offset'  => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'move_id.required' => 'Move ID wajib diisi.',
            'move_id.integer'  => 'Move ID harus berupa angka.',
            'limit.integer'    => 'Limit harus berupa angka.',
            'offset.integer'   => 'Offset harus berupa angka.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'move_id' => $this->has('move_id') ? trim($this->input('move_id')) : null,
            'limit'   => $this->has('limit') ? trim($this->input('limit')) : null,
            'offset'  => $this->has('offset') ? trim($this->input('offset')) : null,
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