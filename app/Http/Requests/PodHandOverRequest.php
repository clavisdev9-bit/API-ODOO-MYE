<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PodHandOverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limit'  => ['nullable', 'integer'],
            'offset' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
        ];
    }
}