<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OutstandingGrRequestValidationIndex extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'  => 'nullable|string',
            'limit'   => 'nullable|integer|min:1|max:100',
            'offset'  => 'nullable|integer|min:0',
        ];
    }

    protected function prepareForValidation(): void
    {
        $allowed = array_keys($this->rules());
        $unknown = array_diff(array_keys($this->all()), $allowed);

        if (count($unknown) > 0) {
            throw new HttpResponseException(response()->json([
                'message' => 'Field tidak dikenali',
                'errors'  => collect($unknown)->mapWithKeys(fn($f) => [$f => ['Field ini tidak valid']])
            ], 422));
        }
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors()
        ], 422));
    }
}