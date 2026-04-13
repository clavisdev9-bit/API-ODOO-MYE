<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OutstandingDispatchWithFreightCostResourcesCollection extends ResourceCollection
{
    public $collects = OutstandingDispatchWithFreightCostResources::class;

    protected int $total;
    protected int $limit;
    protected int $offset;

    public function __construct($resource, int $total, int $limit, int $offset)
    {
        parent::__construct($resource);

        $this->total = $total;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function toArray(Request $request): array
    {
        $limit = $this->limit > 0 ? $this->limit : 10;

        $currentPage = floor($this->offset / $limit) + 1;
        $lastPage = ceil($this->total / $limit);

        $baseUrl = $request->url();

        return [
            'data' => OutstandingDispatchWithFreightCostResources::collection($this->collection),

            'pagination' => [
                'total' => $this->total,
                'per_page' => $limit,
                'current_page' => $currentPage,
                'last_page' => $lastPage,

                'next_page_url' => $currentPage < $lastPage
                    ? $baseUrl . '?limit=' . $limit . '&offset=' . ($this->offset + $limit)
                    : null,

                'prev_page_url' => $currentPage > 1
                    ? $baseUrl . '?limit=' . $limit . '&offset=' . ($this->offset - $limit)
                    : null,
            ]
        ];
    }
}