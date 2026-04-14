<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutstandingDoResource extends JsonResource
{
  public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [

            'customer' => $data['partner_id'][1] ?? null,
            'cust_po_no' => $data['origin'] ?? null,
            'dc_name' => null,
            'area' => null,
            'pulau' => null,
            'propinsi' => null,
            'kabupaten' => null,
            'kecamatan' => null,
            'kelurahan' => null,
            'kode_pos' => null,

            'po_date' => null,
            'expired_date' => null,

            'gr_no' => null,
            'gr_date' => $data['date_done'] ?? null,

            'location' => $data['location_id'][1] ?? null,

            'do_no' => $data['name'] ?? null,
            'do_date' => $data['scheduled_date'] ?? null,

            'total_sku_po' => null,
            'total_ctn_po' => null,

            'total_sku_gr' => null,
            'total_ctn_gr' => null,

            'total_m3_gr' => null,
            'total_kgs_gr' => null,

            'print_label_date' => null,
        ];
    }
}