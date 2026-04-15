<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class InvoiceSalesRequestValidationIndex extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'limit'  => ['nullable', 'integer'],
            'offset' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors()
        ], 422));
    }
}
