<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierPriceListsResources extends JsonResource
{
   public function toArray(Request $request): array
{
    $data = $this->resource;

    return [
        // Identity
        'id'       => $data['id'] ?? null,
        'customer' => $data['name'] ?? null,
        'dc_code'  => $data['dc_code'] ?? $data['ref'] ?? null,
        'dc_name'  => $data['company_name'] ?? null,

        // Address
        'street'          => $data['street'] ?? null,
        'street2'         => $data['street2'] ?? null,
        'city'            => $data['city'] ?? null,
        'zip'             => $data['zip'] ?? null,
        'state'           => $data['state_id'][1] ?? null,
        'country'         => $data['country_id'][1] ?? null,
        'contact_address' => $data['contact_address_complete'] ?? null,

        // Contact
        'email'   => $data['email'] ?? null,
        'phone'   => $data['phone'] ?? null,
        'mobile'  => $data['mobile'] ?? null,
        'website' => $data['website'] ?? null,

        // Optional (kalau ada di Odoo / custom field)
        'freight_type'        => $data['freight_type'] ?? null,
        'vendor'              => $data['vendor'] ?? null,
        'services'            => $data['services'] ?? null,
        'prev_effective_code' => $data['prev_effective_code'] ?? null,
        'prev_leadtime'       => $data['prev_leadtime'] ?? null,
        'prev_min_kgs'        => $data['prev_min_kgs'] ?? null,
        'prev_price'          => $data['prev_price'] ?? null,
        'latest_effective_code'=> $data['latest_effective_code'] ?? null,
        'latest_leadtime'     => $data['latest_leadtime'] ?? null,
        'latest_min_kgs'      => $data['latest_min_kgs'] ?? null,
        'latest_price'        => $data['latest_price'] ?? null,
        'diff_price'          => $data['diff_price'] ?? null,
        'latest_doc_leadtime' => $data['latest_doc_leadtime'] ?? null,
        'latest_doc_price'    => $data['latest_doc_price'] ?? null,
        'lowest_min_kgs'      => $data['lowest_min_kgs'] ?? null,
        'lowest_price'        => $data['lowest_price'] ?? null,
        'base_vendor'         => $data['base_vendor'] ?? null,

        // Timestamp
        'created_at' => $data['create_date'] ?? null,
        'updated_at' => $data['write_date'] ?? null,
    ];
}
}