<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PodHandOverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'do_sent'        => $this['do_sent'] ?? null,
            'do_received'    => $this['do_received'] ?? null,
            'cancel_status'  => $this['cancel_status'] ?? null,

            'cust_po_no'     => $this['cust_po_no'] ?? null,
            'awb_no'         => $this['awb_no'] ?? null,

            'cust_po_date'   => $this['cust_po_date'] ?? null,
            'customer_name'  => $this['customer_name'] ?? null,

            'dc_name'        => $this['dc_name'] ?? null,
            'do_no'          => $this['do_no'] ?? null,
            'do_date'        => $this['do_date'] ?? null,

            'shipped_by'     => $this['shipped_by'] ?? null,

            'qty_shipped'    => $this['qty_shipped'] ?? null,
            'qty_box'        => $this['qty_box'] ?? null,

            'do_sent_by'     => $this['do_sent_by'] ?? null,
            'do_sent_at'     => $this['do_sent_at'] ?? null,
            'do_received_by' => $this['do_received_by'] ?? null,
            'do_received_at' => $this['do_received_at'] ?? null,
        ];
    }
}