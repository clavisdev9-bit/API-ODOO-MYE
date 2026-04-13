<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutstandingPiResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'cust_po_no' => $data['cust_po_no'] ?? null,

            'po_date' => $data['po_date'] ?? null,

            'gr_no' => $data['gr_no'] ?? null,

            'gr_date' => $data['gr_date'] ?? null,

            'exp_date' => $data['exp_date'] ?? null,

            'customer' => $data['customer'] ?? null,

            'customer_alias' => $data['customer_alias'] ?? null,

            'dc_name' => $data['dc_name'] ?? null,

            'area' => $data['area'] ?? null,

            'total_sku' => $data['total_sku'] ?? null,

            'qty_so' => $data['qty_so'] ?? null,

            'qty_so_ctn' => $data['qty_so_ctn'] ?? null,

            'qty_gr' => $data['qty_gr'] ?? null,

            'qty_grn_ctn' => $data['qty_grn_ctn'] ?? null,

            'qty_do' => $data['qty_do'] ?? null,

            'qty_do_ctn' => $data['qty_do_ctn'] ?? null,

            'amt_bef_tax' => $data['amt_bef_tax'] ?? null,

            'last_status' => $data['last_status'] ?? null,

            'last_update' => $data['last_update'] ?? null,

            'messages' => $data['messages'] ?? null,
        ];
    }
}