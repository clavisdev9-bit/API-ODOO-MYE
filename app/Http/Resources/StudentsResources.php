<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentsResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $this->resource adalah array dari Odoo
        $data = $this->resource;

        return [
            'id'     => $data['id']     ?? null,
            'name'   => $data['name']   ?? null,
            'email'  => $data['email']  ?? null,
            'phone'  => $data['phone']  ?? null,
            'mobile' => $data['mobile'] ?? null,
            'street' => $data['street'] ?? null,
            'city'   => $data['city']   ?? null,
            'active' => $data['active'] ?? null,
        ];
    }
}