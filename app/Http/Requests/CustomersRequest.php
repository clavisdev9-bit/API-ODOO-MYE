<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomersRequest extends FormRequest
{
     public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'ref'     => ['nullable', 'string', 'max:100'],  // DC Code
            'email'   => ['nullable', 'email'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'mobile'  => ['nullable', 'string', 'max:20'],
            'street'  => ['nullable', 'string', 'max:255'],
            'street2' => ['nullable', 'string', 'max:255'],  // Kecamatan
            'city'    => ['nullable', 'string', 'max:255'],  // Kabupaten
            'zip'     => ['nullable', 'string', 'max:10'],   // Kode Pos
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string'   => 'Nama harus berupa teks.',
            'name.max'      => 'Nama maksimal 255 karakter.',
            'email.email'   => 'Format email tidak valid.',
            'phone.max'     => 'Nomor telepon maksimal 20 karakter.',
            'mobile.max'    => 'Nomor mobile maksimal 20 karakter.',
            'zip.max'       => 'Kode pos maksimal 10 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'    => $this->has('name')    ? trim($this->input('name'))    : null,
            'ref'     => $this->has('ref')     ? trim($this->input('ref'))     : null,
            'email'   => $this->has('email')   ? trim($this->input('email'))   : null,
            'phone'   => $this->has('phone')   ? trim($this->input('phone'))   : null,
            'mobile'  => $this->has('mobile')  ? trim($this->input('mobile'))  : null,
            'street'  => $this->has('street')  ? trim($this->input('street'))  : null,
            'street2' => $this->has('street2') ? trim($this->input('street2')) : null,
            'city'    => $this->has('city')    ? trim($this->input('city'))    : null,
            'zip'     => $this->has('zip')     ? trim($this->input('zip'))     : null,
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
