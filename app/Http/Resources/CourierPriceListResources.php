<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierPriceListResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'id'            => $data['id']   ?? null,
            'customer_name' => $data['name'] ?? null,
            'customer_code' => $data['ref']  ?? null,

            // DC info (kosong dulu sampai data diinput)
            'dc_code'       => $data['x_dc_code'] ?? null,

            // Alamat
            'address'       => $data['contact_address_complete'] ?? null,
            'city'          => $data['city']           ?? null,
            'zip'           => $data['zip']            ?? null,
            'state'         => $data['state_id'][1]    ?? null,
            'country'       => $data['country_id'][1]  ?? null,

            // Wilayah Indonesia
            'pulau'         => $data['x_pulau']     ?? null,
            'propinsi'      => $data['x_propinsi']  ?? null,
            'kecamatan'     => $data['x_kecamatan'] ?? null,
            'kelurahan'     => $data['x_kelurahan'] ?? null,

            // Pricing (kosong dulu sampai model x_courier.price.list ada datanya)
            'freight_type'   => null,
            'vendor'         => null,
            'service'        => null,
            'city_code'      => null,
            'base_vendor'    => null,
            'prev'           => [
                'effective_code' => null,
                'leadtime'       => null,
                'min_kgs'        => null,
                'price'          => null,
            ],
            'latest'         => [
                'effective_code' => null,
                'leadtime'       => null,
                'min_kgs'        => null,
                'price'          => null,
                'doc_leadtime'   => null,
                'doc_price'      => null,
            ],
            'lowest_min_kgs' => null,
            'lowest_price'   => null,
            'diff_price'     => null,
        ];
    }
}