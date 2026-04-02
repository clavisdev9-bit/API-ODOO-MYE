<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['nullable', 'email'],
            'phone'  => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'street' => ['nullable', 'string', 'max:255'],
            'city'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama siswa wajib diisi.',
            'name.string'   => 'Nama siswa harus berupa teks.',
            'name.max'      => 'Nama siswa maksimal 255 karakter.',
            'email.email'   => 'Format email tidak valid.',
            'phone.max'     => 'Nomor telepon maksimal 20 karakter.',
            'mobile.max'    => 'Nomor mobile maksimal 20 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'   => $this->has('name')   ? trim($this->input('name'))   : null,
            'email'  => $this->has('email')  ? trim($this->input('email'))  : null,
            'phone'  => $this->has('phone')  ? trim($this->input('phone'))  : null,
            'mobile' => $this->has('mobile') ? trim($this->input('mobile')) : null,
            'street' => $this->has('street') ? trim($this->input('street')) : null,
            'city'   => $this->has('city')   ? trim($this->input('city'))   : null,
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