<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutstandingDispatchWithFreightCostResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'dc' => $this['dc'] ?? null,
            'cbm' => $this['cbm'] ?? 0,
            'kgs' => $this['kgs'] ?? 0,
            'min_kgs' => $this['min_kgs'] ?? 1,
            'lead_time' => $this['lead_time'] ?? 3,
            'ratio' => $this['ratio'] ?? 6000,
            'vm' => $this['vm'] ?? 0,
            'cw' => $this['cw'] ?? 0,
            'price' => $this['price'] ?? 0,
            'est_courier_cost' => $this['est_courier_cost'] ?? 0,
            'cheapest' => $this['cheapest'] ?? 1,
            'vendor_name' => $this['vendor_name'] ?? null,
            'service_name' => $this['service_name'] ?? null,
        ];
    }
}