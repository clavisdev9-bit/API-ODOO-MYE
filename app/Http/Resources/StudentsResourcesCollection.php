<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StudentsResourcesCollection extends ResourceCollection
{
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
        $currentPage = (int) floor($this->offset / $this->limit) + 1;
        $lastPage    = (int) ceil($this->total / $this->limit);
        $baseUrl     = $request->url();

        $nextPage = $currentPage < $lastPage
            ? $baseUrl . '?' . http_build_query(['limit' => $this->limit, 'offset' => $this->offset + $this->limit])
            : null;

        $prevPage = $currentPage > 1
            ? $baseUrl . '?' . http_build_query(['limit' => $this->limit, 'offset' => $this->offset - $this->limit])
            : null;

        return [
            'data'       => StudentsResources::collection($this->collection),
            'pagination' => [
                'total'         => $this->total,
                'per_page'      => $this->limit,
                'current_page'  => $currentPage,
                'last_page'     => $lastPage,
                'next_page_url' => $nextPage,
                'prev_page_url' => $prevPage,
            ],
        ];
    }
}