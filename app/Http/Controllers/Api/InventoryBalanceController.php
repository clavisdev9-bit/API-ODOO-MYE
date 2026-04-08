<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryBalanceResources;
use App\Http\Resources\InventoryBalanceResourcesCollection;
use App\Http\Requests\InventoryBalanceRequestValidationIndex;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class InventoryBalanceController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

  
public function index(InventoryBalanceRequestValidationIndex $request)
{
    $validated  = $request->validated();
    $search     = $validated['search']      ?? null;
    $locationId = $validated['location_id'] ?? null;
    $limit  = is_numeric($validated['limit']  ?? null) ? (int) $validated['limit']  : 10;
    $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

    $domain = [
        ['quantity', '>', 0],
    ];

    if (!empty($locationId)) {
        $domain[] = ['location_id', '=', (int) $locationId];
    }

    if (!empty($search)) {
        $domain[] = '|';
        $domain[] = ['product_id.name', 'ilike', $search];
        $domain[] = ['product_id.default_code', 'ilike', $search];
    }

    //  ambil total
    $total = $this->odoo->searchCount('stock.quant', $domain);

    //  ambil semua data
    $records = $this->odoo->searchRead(
        'stock.quant',
        $domain,
        [
            'id',
            'product_id',
            'product_tmpl_id',
            'location_id',
            'quantity',
            'reserved_quantity',
            'available_quantity',
        ],
         $limit,   
        $offset 
    );

    //  enrich
    $records = $this->enrichWithProductData($records);

    $message = empty($records)
        ? 'Data yang Anda cari tidak ditemukan'
        : 'Success';

    return ApiResponse::paginate(
            new InventoryBalanceResourcesCollection($records, $total, $limit, $offset),
            $message
        );
}

    public function show(int $id)
    {
        $records = $this->odoo->read(
            'stock.quant',
            [$id],
            [
                'id',
                'product_id',
                'product_tmpl_id',
                'location_id',
                'quantity',
                'reserved_quantity',
                'available_quantity',
            ]
        );

        if (empty($records)) {
            return ApiResponse::error('Inventory Balance not found', [
                'id' => ['Data with that ID is not available']
            ], 404);
        }

        $records = $this->enrichWithProductData($records);

        return ApiResponse::success(
            new InventoryBalanceResources($records[0]),
            'Success, take the detailed Inventory Balance',
            200
        );

        
    }



    private function enrichWithProductData(array $records): array
    {
        if (empty($records)) {
            return $records;
        }

        $tmplIds = array_unique(array_filter(array_map(function ($r) {
            return $r['product_tmpl_id'][0] ?? null;
        }, $records)));

        if (empty($tmplIds)) {
            return $records;
        }

        $templates = $this->odoo->searchRead(
            'product.template',
            [['id', 'in', array_values($tmplIds)]],
            ['id', 'name', 'default_code', 'categ_id'],
            0, 0
        );

        $tmplMap = [];
        foreach ($templates as $tmpl) {
            $tmplMap[$tmpl['id']] = $tmpl;
        }

        foreach ($records as &$record) {
            $tmplId = $record['product_tmpl_id'][0] ?? null;
            $tmpl   = $tmplId ? ($tmplMap[$tmplId] ?? []) : [];

            $record['default_code'] = $tmpl['default_code'] ?? null;
            $record['categ_id']     = $tmpl['categ_id']     ?? null;
            $record['x_brand']      = null;
            $record['x_std_pack']   = null;
        }
        unset($record);

        return $records;
    }
}