<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryBalanceResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'id'           => $data['id']                 ?? null,

            // Product
            'product_id'   => $data['product_tmpl_id'][0] ?? null,
            'product_name' => $data['product_tmpl_id'][1] ?? null,
            'product_code' => $data['default_code']       ?? null,
            'brand'        => $data['x_brand']            ?? null,
            'std_pack'     => $data['x_std_pack']         ?? null,
            'category'     => $data['categ_id'][1]        ?? null,

            // Location
            'location_id'  => $data['location_id'][0]     ?? null,
            'location'     => $data['location_id'][1]     ?? null,

            // Quantity
            'balance_qty'  => $data['quantity']           ?? null,
            'reserved_qty' => $data['reserved_quantity']  ?? null,
            'available_qty'=> $data['available_quantity'] ?? null,
        ];
    }
}