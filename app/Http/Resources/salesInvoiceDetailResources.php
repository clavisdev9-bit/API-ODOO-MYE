<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class salesInvoiceDetailResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'id' => $data['id'] ?? null,

            'move_id' => $data['move_id'][0] ?? null,
            'move_name' => $data['move_id'][1] ?? null,

            'line_label' => $data['name'] ?? null,

            'product_id' => $data['product_id'][0] ?? null,
            'product_name' => $data['product_id'][1] ?? null,

            'quantity' => $data['quantity'] ?? 0,

            'unit_price' => $data['price_unit'] ?? 0,

            'discount' => $data['discount'] ?? 0,

            'tax_ids' => $data['tax_ids'] ?? [],

            'line_total_excl_tax' => $data['price_subtotal'] ?? 0,

            'line_total_incl_tax' => $data['price_total'] ?? 0,
        ];
    }
}