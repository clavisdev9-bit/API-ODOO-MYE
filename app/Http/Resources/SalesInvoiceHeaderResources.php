<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesInvoiceHeaderResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'id' => $data['id'] ?? null,

            'invoice_date' => $data['create_date'] ?? null,

            'customer_id' => $data['partner_id'][0] ?? null,
            'customer_name' => $data['partner_id'][1] ?? null,

            'invoice_origin' => $data['invoice_origin'] ?? null,
            'invoice_number' => $data['name'] ?? null,
            'reference' => $data['ref'] ?? null,

            'currency_id' => $data['company_currency_id'][0] ?? null,
            'currency_name' => $data['company_currency_id'][1] ?? null,

            'subtotal' => $data['amount_untaxed_signed'] ?? 0,
            'tax' => $data['amount_tax'] ?? 0,
            'grand_total' => $data['amount_total_in_currency_signed'] ?? 0,

            'payment_status' => $data['payment_state'] ?? null,
            'invoice_status' => $data['state'] ?? null,
            'invoice_type' => $data['move_type'] ?? null,
        ];
    }
}