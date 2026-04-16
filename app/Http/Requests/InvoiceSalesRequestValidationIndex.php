<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class InvoiceSalesRequestValidationIndex extends FormRequest
{
    public function authorize(): bool { return true; }

    // public function rules(): array
    // {
    //     return [
    //         'limit'  => ['nullable', 'integer'],
    //         'offset' => ['nullable', 'integer'],
    //         'search' => ['nullable', 'string'],
    //     ];
    // }


//     public function rules()
// {
//     return [
//         // Validasi pagination yang sudah ada
//         'limit'      => 'nullable|numeric',
//         'offset'     => 'nullable|numeric',

//         // TAMBAHKAN INI: Agar start_date dan end_date diizinkan masuk ke Controller
//         'start_date' => 'nullable|date_format:Y-m-d',
//         'end_date'   => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
//     ];
// }


// Di dalam InvoiceSalesRequestValidationIndex.php
public function rules(): array
{
    return [
        'limit'      => 'nullable|integer',
        'offset'     => 'nullable|integer',
        'start_date' => 'nullable|date_format:Y-m-d',
        'end_date'   => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
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
