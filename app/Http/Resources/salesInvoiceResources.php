<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class salesInvoiceResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'id'                              => $data['id'] ?? null,
            'name'                            => $data['name'] ?? null,
            'invoice_partner_display_name'    => $data['invoice_partner_display_name'] ?? null,
            'partner_id'                      => $data['partner_id'] ?? null,
            'invoice_date'                    => $data['invoice_date'] ?? null,
            'invoice_origin'                  => $data['invoice_origin'] ?? null,
            'ref'                             => $data['ref'] ?? null,
            'company_currency_id'             => $data['company_currency_id'] ?? null,
            'currency_id'                     => $data['currency_id'] ?? null,
            'amount_untaxed'                  => $data['amount_untaxed'] ?? 0,
            'amount_untaxed_signed'           => $data['amount_untaxed_signed'] ?? 0,
            'amount_tax'                      => $data['amount_tax'] ?? 0,
            'amount_total'                    => $data['amount_total'] ?? 0,
            'amount_total_in_currency_signed' => $data['amount_total_in_currency_signed'] ?? 0,
            'payment_state'                   => $data['payment_state'] ?? null,
            'state'                           => $data['state'] ?? null,
            'move_type'                       => $data['move_type'] ?? null,
            'invoice_line_ids'                => $data['invoice_line_ids'] ?? [],
            // Bagian Detail Lines
            'lines'                           => $data['lines'] ?? [],
        ];
    }
}