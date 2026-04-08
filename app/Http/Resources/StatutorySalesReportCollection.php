<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StatutorySalesReportCollection extends ResourceCollection
{
    public $collects = StatutorySalesReportResource::class;

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
        $limit       = $this->limit;
        $currentPage = (int) floor($this->offset / $limit) + 1;
        $lastPage    = (int) ceil($this->total / $limit);
        $baseUrl     = $request->url();

        return [
            'data' => $this->collection,

            'pagination' => [
                'total'         => $this->total,
                'per_page'      => $limit,
                'current_page'  => $currentPage,
                'last_page'     => $lastPage,
                'next_page_url' => $currentPage < $lastPage
                    ? $baseUrl . '?' . http_build_query([
                        'limit' => $limit,
                        'offset' => $this->offset + $limit
                    ])
                    : null,

                'prev_page_url' => $currentPage > 1
                    ? $baseUrl . '?' . http_build_query([
                        'limit' => $limit,
                        'offset' => $this->offset - $limit
                    ])
                    : null,
            ]
        ];
    }
}