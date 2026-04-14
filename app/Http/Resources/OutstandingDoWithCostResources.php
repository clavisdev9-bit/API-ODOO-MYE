<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutstandingDoWithCostResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [

            'customer' => $data['partner_id'][1] ?? null,
            'cust_po_no' => $data['partner_ref'] ?? null,

            'dc' => $data['dest_address_id'][1] ?? null,

            'cust_lead_time' => null,
            'freight_lead_time' => null,

            'area' => null,
            'pulau' => null,
            'propinsi' => null,
            'kabupaten' => null,
            'kecamatan' => null,
            'kelurahan' => null,
            'kode_pos' => null,

            'po_date' => $data['date_order'] ?? null,
            'expired_date' => $data['date_planned'] ?? null,

            'gr_no' => $data['picking_ids'][0] ?? null,
            'gr_date' => !empty($data['effective_date']) ? $data['effective_date'] : null,

            'total_sku' => null,
            'total_ctn' => null,
            'total_cbm' => null,
            'total_kgs' => null,

            'amt_bef_tax' => $data['amount_untaxed'] ?? null,

            'service' => $data['incoterm_id'][1] ?? null,

            'freight_price' => null,
            'doc_price' => null,
            'ratio' => null,
            'volumetric' => null,
            'cw' => null,

            'freight_cost_bef_min' => null,
            'min_cw' => null,
            'cw_aft_min' => null,

        ];
    }
}