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
            'id'              => $data['id']                       ?? null,
            'customer'        => $data['name']                     ?? null,  // Customer
            // 'dc_code'         => $data['ref']                   ?? "Field Di Oddo Belum Ada",  // DC Code
            'dc_code'         => $data['dc_code']                  ?? "Field Di Oddo Belum Ada",  // DC Code
            'dc_name'         => $data['company_name']             ?? null,  // DC Name

            // Address
            'street'          => $data['street']                   ?? null,
            'street2'         => $data['street2']                  ?? null,  // Kecamatan
            'city'            => $data['city']                     ?? null,  // Kabupaten
            'zip'             => $data['zip']                      ?? null,  // Kode Pos
            'state'           => $data['state_id'][1]              ?? null,  // Propinsi
            'country'         => $data['country_id'][1]            ?? null,
            'contact_address' => $data['contact_address_complete'] ?? null,  // Address lengkap

            // Contact
            'email'           => $data['email']                    ?? null,
            'phone'           => $data['phone']                    ?? null,
            'mobile'          => $data['mobile']                   ?? null,
            'website'         => $data['website']                  ?? null,

             // list yang belum diketahui isinya (di clavis report ada tapi engga ada di dokumentasi Odoo)
            'freight_type'     => $data['freight_type']    ?? "Field Di Oddo Belum Ada",
            'vendor'           => $data['vendor']    ?? "Field Di Oddo Belum Ada",
            'services'         => $data['services']    ?? "Field Di Oddo Belum Ada",
            'prev_effective_code'         => $data['prev_effective_code']    ?? "Field Di Oddo Belum Ada",
            'prev_leadtime'         => $data['prev_leadtime']    ?? "Field Di Oddo Belum Ada",
            'prev_min_kgs'         => $data['prev_min_kgs']    ?? "Field Di Oddo Belum Ada",
            'prev_price'         => $data['prev_price']    ?? "Field Di Oddo Belum Ada",
            'latest_effective_code'         => $data['latest_effective_code']    ?? "Field Di Oddo Belum Ada",
            'latest_leadtime'         => $data['latest_leadtime']    ?? "Field Di Oddo Belum Ada",
            'latest_min_kgs'         => $data['latest_min_kgs']    ?? "Field Di Oddo Belum Ada",
            'latest_price'         => $data['latest_price']    ?? "Field Di Oddo Belum Ada",
            'diff_price'         => $data['diff_price']    ?? "Field Di Oddo Belum Ada",
            'latest_doc_leadtime'         => $data['latest_doc_leadtime']    ?? "Field Di Oddo Belum Ada",
            'latest_doc_price'         => $data['latest_doc_price']    ?? "Field Di Oddo Belum Ada",
            'lowest_min_kgs'         => $data['lowest_min_kgs']    ?? "Field Di Oddo Belum Ada",
            'lowest_price'         => $data['lowest_price']    ?? "Field Di Oddo Belum Ada",
            'base_vendor'         => $data['base_vendor']    ?? "Field Di Oddo Belum Ada",

            // Status
            // 'is_company'      => $data['is_company']               ?? null,
            // 'supplier_rank'   => $data['supplier_rank']            ?? null,
            // 'customer_rank'   => $data['customer_rank']            ?? null,
            // 'active'          => $data['active']                   ?? null,

            // Custom fields (x_studio) - belum diketahui isinya
            // 'x_studio_tes'    => $data['x_studio_tes']             ?? null,
            // 'x_studio_id'     => $data['x_studio_id']              ?? null,

            // Timestamps
            'created_at'      => $data['create_date']              ?? null,
            'updated_at'      => $data['write_date']               ?? null,
        ];
    }
}