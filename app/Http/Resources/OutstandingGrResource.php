<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutstandingGrResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'cust_po_no'    => $data['cust_po_no'] ?? null,
            'so_date'       => $data['so_date'] ?? null,
            'exp_date'       => $data['exp_date'] ?? null,
            'approved_date' => $data['approved_date'] ?? null,

            'customer'      => $data['customer'] ?? null,
            'customer_alias'      => $data['customer_alias'] ?? null,

            'dc_name'      => $data['dc_name'] ?? null,
            'area'      => $data['area'] ?? null,

            'cust_lead_time'      => $data['cust_lead_time'] ?? null,
            'product_code'  => $data['product_code'] ?? null,
            'std_pack'  => $data['std_pack'] ?? null,
            
            'qty_so'        => $data['qty_so'] ?? 0,

            'qty_ctn'       => $data['qty_ctn'] ?? null,
            'repack'       => $data['repack'] ?? null,
            'length'       => $data['length'] ?? null,
            'breadth'       => $data['breadth'] ?? null,
            'height'       => $data['height'] ?? null,
            'cm3'       => $data['cm3'] ?? null,
            'cbm'           => $data['cbm'] ?? null,
            'kgs'           => $data['kgs'] ?? null,
        ];
    }
}