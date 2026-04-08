<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DcResources extends JsonResource
{
    private function val($value)
    {
        return $value === false ? null : $value;
    }

    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            // Identity
            'id'      => $data['id'] ?? null,
            'dc_code' => $this->val($data['ref'] ?? null),
            'dc_name' => $this->val($data['name'] ?? null),

            // Customer (relasi)
            'customer' => [
                'id'   => $data['parent_id'][0] ?? null,
                'name' => $data['parent_id'][1] ?? null,
            ],

            // Location
            'city'    => $this->val($data['city'] ?? null),
            'zip'     => $this->val($data['zip'] ?? null),
            'state'   => $this->val($data['state_id'][1] ?? null),
            'country' => $this->val($data['country_id'][1] ?? null),

            // Address
            'address' => $this->val($data['contact_address_complete'] ?? null),

            // Contact
            'phone'   => $this->val($data['phone'] ?? null),

            // Status
            'active'  => $data['active'] ?? true,

            'area' => $this->val($data['x_area'] ?? null),

            'min_lead_day' => $this->val($data['x_min_lead_day'] ?? null),
            'max_lead_day' => $this->val($data['x_max_lead_day'] ?? null),

            'phone2' => $this->val($data['x_phone_2'] ?? null),

            'pulau' => $this->val($data['x_pulau'] ?? null),
            'propinsi' => $this->val($data['x_propinsi'] ?? null),
            'kabupaten' => $this->val($data['x_kabupaten'] ?? null),
            'kecamatan' => $this->val($data['x_kecamatan'] ?? null),
            'kelurahan' => $this->val($data['x_kelurahan'] ?? null),

            'approved_by' => $this->val($data['x_approved_by'][1] ?? $data['x_approved_by'] ?? null),
            'approved_at' => $this->val($data['x_approved_at'] ?? null),

            // Timestamp
            'created_at' => $data['create_date'] ?? null,
            'updated_at' => $data['write_date'] ?? null,
        ];
    }
}