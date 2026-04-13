<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutstandingDispatchResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'dc' => $this['dc'],
            'cbm' => round($this['cbm'], 4),
            'kgs' => round($this['kgs'], 4),
            'min_kgs' => $this['min_kgs'],
            'lead_time' => $this['lead_time'],
            'ratio' => $this['ratio'],
            'vm' => round($this['vm'], 4),
            'cw' => round($this['cw'], 4),
            'cheapest' => $this['cheapest'],
            'vendor_name' => $this['vendor_name'],
            'service_name' => $this['service_name'],
        ];
    }
}