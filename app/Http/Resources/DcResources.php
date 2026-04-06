<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DcResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            // Identity
            'id'          => $data['id']          ?? null,
            'dc_code'     => $data['ref']   ?? null,
            'dc_name'     => $data['name']         ?? null,
            'customer_id' => $data['parent_id'][0] ?? null,
            'customer_name' => $data['parent_id'][1] ?? null,

            // Area
            'area'        => $data['x_dc_area']   ?? null,
            'city'        => $data['city']         ?? null,
            'zip'         => $data['zip']          ?? null,
            'state'       => $data['state_id'][1]  ?? null,
            'country'     => $data['country_id'][1] ?? null,

            // Wilayah Indonesia
            'pulau'       => $data['x_pulau']      ?? null,
            'propinsi'    => $data['x_propinsi']   ?? null,
            'kecamatan'   => $data['x_kecamatan']  ?? null,
            'kelurahan'   => $data['x_kelurahan']  ?? null,

            // Address
            'address'     => $data['contact_address_complete'] ?? null,

            // Contact
            'phone1'      => $data['phone']        ?? null,
            'phone2'      => $data['mobile']       ?? null,

            // Lead time
            'min_lead_day' => $data['x_min_lead_day'] ?? null,
            'max_lead_day' => $data['x_max_lead_day'] ?? null,

            // Approval
            'approved_by' => $data['x_approved_by'] ?? null,
            'approved_at' => $data['x_approved_at'] ?? null,

            // Status
            'active'      => $data['active']       ?? null,

            // Timestamp
            'created_at'  => $data['create_date']  ?? null,
            'updated_at'  => $data['write_date']   ?? null,
        ];
    }
}