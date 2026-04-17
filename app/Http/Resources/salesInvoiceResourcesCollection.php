<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class salesInvoiceResourcesCollection extends ResourceCollection
{
    public $collects = salesInvoiceResources::class;

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

    // public function toArray(Request $request): array
    // {
    //     $limit       = $this->limit > 0 ? $this->limit : 10;
    //     $currentPage = (int) floor($this->offset / $limit) + 1;
    //     $lastPage    = (int) ceil($this->total / $limit);
    //     $baseUrl     = $request->url();

    //     return [
    //         'data'       => $this->collection,
    //         'pagination' => [
    //             'total'         => $this->total,
    //             'per_page'      => $limit,
    //             'current_page'  => $currentPage,
    //             'last_page'     => $lastPage,
    //             'next_page_url' => $currentPage < $lastPage 
    //                 ? $baseUrl . '?' . http_build_query(['limit' => $limit, 'offset' => $this->offset + $limit]) 
    //                 : null,
    //             'prev_page_url' => $currentPage > 1 
    //                 ? $baseUrl . '?' . http_build_query(['limit' => $limit, 'offset' => $this->offset - $limit]) 
    //                 : null,
    //         ],
    //     ];
    // }


    // oke banget
//     public function toArray(Request $request): array
// {
//     $limit       = $this->limit > 0 ? $this->limit : 10;
//     $currentPage = (int) floor($this->offset / $limit) + 1;
//     $lastPage    = (int) ceil($this->total / $limit);
//     $baseUrl     = $request->url();

//     // Ambil semua query parameters yang ada saat ini (start_date, end_date, dll)
//     $currentQueries = $request->query();

//     return [
//         'data'       => $this->collection,
//         'pagination' => [
//             'total'         => $this->total,
//             'per_page'      => $limit,
//             'current_page'  => $currentPage,
//             'last_page'     => $lastPage,
//             'next_page_url' => $currentPage < $lastPage 
//                 ? $baseUrl . '?' . http_build_query(array_merge($currentQueries, [
//                     'limit'  => $limit, 
//                     'offset' => $this->offset + $limit
//                 ])) 
//                 : null,
//             'prev_page_url' => $currentPage > 1 
//                 ? $baseUrl . '?' . http_build_query(array_merge($currentQueries, [
//                     'limit'  => $limit, 
//                     'offset' => $this->offset - $limit
//                 ])) 
//                 : null,
//         ],
//     ];
// }

public function toArray(Request $request): array
{
    // Jika limit null (mode full data), kita set default ke total data agar last_page jadi 1
    $limit       = ($this->limit > 0) ? $this->limit : ($this->total > 0 ? $this->total : 1);
    $currentPage = (int) floor($this->offset / $limit) + 1;
    $lastPage    = (int) ceil($this->total / $limit);
    $baseUrl     = $request->url();
    
    // Pertahankan parameter filter (start_date, end_date) di link pagination
    $currentQueries = $request->query();

    return [
        'data'       => $this->collection,
        'pagination' => [
            'total'         => $this->total,
            'per_page'      => $this->limit ?? $this->total, // Tetap tampilkan null/total asli
            'current_page'  => $currentPage,
            'last_page'     => $lastPage,
            'next_page_url' => $currentPage < $lastPage 
                ? $baseUrl . '?' . http_build_query(array_merge($currentQueries, [
                    'limit'  => $limit, 
                    'offset' => $this->offset + $limit
                ])) 
                : null,
            'prev_page_url' => $currentPage > 1 
                ? $baseUrl . '?' . http_build_query(array_merge($currentQueries, [
                    'limit'  => $limit, 
                    'offset' => $this->offset - $limit
                ])) 
                : null,
        ],
    ];
}
}