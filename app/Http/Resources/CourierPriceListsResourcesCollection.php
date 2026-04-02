<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CourierPriceListsResourcesCollection extends ResourceCollection
{
    // ✅ Tambah ini - paksa pakai CourierPriceListsResources
    public $collects = CourierPriceListsResources::class;

    protected int $total;
    protected int $limit;
    protected int $offset;

    public function __construct($resource, int $total, int $limit, int $offset)
    {
        parent::__construct($resource);
        $this->total  = $total;
        $this->limit  = $limit;
        $this->offset = $offset;
    }

    public function toArray(Request $request): array
    {
        $limit       = $this->limit > 0 ? $this->limit : 10;
        $currentPage = (int) floor($this->offset / $limit) + 1;
        $lastPage    = (int) ceil($this->total / $limit);
        $baseUrl     = $request->url();

        $nextPage = $currentPage < $lastPage
            ? $baseUrl . '?' . http_build_query(['limit' => $limit, 'offset' => $this->offset + $limit])
            : null;

        $prevPage = $currentPage > 1
            ? $baseUrl . '?' . http_build_query(['limit' => $limit, 'offset' => $this->offset - $limit])
            : null;

        return [
            'data'       => CourierPriceListsResources::collection($this->collection),
            'pagination' => [
                'total'         => $this->total,
                'per_page'      => $limit,
                'current_page'  => $currentPage,
                'last_page'     => $lastPage,
                'next_page_url' => $nextPage,
                'prev_page_url' => $prevPage,
            ],
        ];
    }
}