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
            'amount_untaxed'                  => (float) ($data['amount_untaxed'] ?? 0),
            'amount_untaxed_signed'           => (float) ($data['amount_untaxed_signed'] ?? 0),
            'amount_tax'                      => (float) ($data['amount_tax'] ?? 0),
            'amount_total'                    => (float) ($data['amount_total'] ?? 0),
            'amount_total_in_currency_signed' => (float) ($data['amount_total_in_currency_signed'] ?? 0),
            'payment_state'                   => $data['payment_state'] ?? null,
            'state'                           => $data['state'] ?? null,
            'move_type'                       => $data['move_type'] ?? null,
            'invoice_line_ids'                => $data['invoice_line_ids'] ?? [],
            
            'lines' => collect($data['lines'] ?? [])->map(function($line) {
                return [
                    'move_id'        => $line['move_id'][0] ?? $line['move_id'] ?? null,
                    'name'           => $line['name'] ?? null,
                    'product_id'     => $line['product_id'][0] ?? null,
                    'product_name'   => $line['product_id'][1] ?? null,
                    'category_id'    => $line['product_category'][0] ?? null,
                    'category_name'  => $line['product_category'][1] ?? null,
                    'quantity'       => (float) ($line['quantity'] ?? 0),
                    'price_unit'     => (float) ($line['price_unit'] ?? 0),
                    'discount'       => (float) ($line['discount'] ?? 0),
                    'tax_ids'        => $line['tax_formatted'] ?? [], 
                    'price_subtotal' => (float) ($line['price_subtotal'] ?? 0),
                    'price_total'    => (float) ($line['price_total'] ?? 0),
                ];
            })->values(), // values() memastikan array index berurutan [0, 1, 2...]
        ];
    }
}