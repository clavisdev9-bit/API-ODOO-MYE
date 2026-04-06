<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomersResources extends JsonResource
{
     public function toArray(Request $request): array
    {
        $data = $this->resource;

       return [
    // Identity
    'id'            => $data['id'] ?? null,
    'customer_name' => $data['name'] ?? null,
    'customer_code' => $data['ref'] ?? null,
    'alias_name'    => $data['x_alias_name'] ?? null, // custom kalau ada

    // Address
    'billing_address'  => $data['contact_address_complete'] ?? null,
    'delivery_address' => $data['contact_address_complete'] ?? null,

    'city'    => $data['city'] ?? null,
    'zip'     => $data['zip'] ?? null,
    'state'   => $data['state_id'][1] ?? null,
    'country' => $data['country_id'][1] ?? null,

    // Contact
    'phone1'  => $data['phone'] ?? null,
    'phone2'  => $data['mobile'] ?? null,
    'fax' => $data['x_fax'] ?? null,
    'email'   => $data['email'] ?? null,
    'website' => $data['website'] ?? null,

    'attention' => $data['function'] ?? null,
    'contact'   => $data['x_studio_id'] ?? null,

    // Business
    'tax_no'       => $data['vat'] ?? null,
    'company_reg'  => $data['company_registry'] ?? null,
    'currency'     => $data['currency_id'][1] ?? null,

    // Notes
    'note' => $data['comment'] ?? null,

    // Status
    'active' => $data['active'] ?? null,
    'remark_1' => $data['remark_1'] ?? null,
    'remark_2' => $data['remark_2'] ?? null,
    'remark_3' => $data['remark_3'] ?? null,
    'remark_4' => $data['remark_4'] ?? null,
    'remark_5' => $data['remark_5'] ?? null,

    // Timestamp
    'created_at' => $data['create_date'] ?? null,
    'updated_at' => $data['write_date'] ?? null,
];
    }
}
