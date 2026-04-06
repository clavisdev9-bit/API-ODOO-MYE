<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourierPriceListResources;
use App\Http\Resources\CourierPriceListResourcesCollection;
use App\Http\Requests\CourierPriceListRequestValidationIndex;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class CourierPriceListController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(CourierPriceListRequestValidationIndex $request)
    {
        $validated   = $request->validated();
        $search      = $validated['search']      ?? null;
        $customerId  = $validated['customer_id'] ?? null;
        $freightType = $validated['freight_type'] ?? null;
        $vendor      = $validated['vendor']      ?? null;
        // $limit       = is_numeric($validated['limit']  ?? null) ? (int) $validated['limit']  : 10;
        // $offset      = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        $domain = [
            ['customer_rank', '>', 0],
            ['active', '=', true],
        ];

        if (!empty($customerId)) {
            $domain[] = ['id', '=', (int) $customerId];
        }

        if (!empty($search)) {
            $domain[] = '|';
            $domain[] = ['name', 'ilike', $search];
            $domain[] = ['city', 'ilike', $search];
        }

        $total   = $this->odoo->searchCount('res.partner', $domain);
        $records = $this->odoo->searchRead(
            'res.partner',
            $domain,
            [
                'id', 'name', 'ref',
                'contact_address', 'contact_address_complete',
                'city', 'zip', 'state_id', 'country_id',
                'x_dc_code', 'x_pulau', 'x_propinsi',
                'x_kecamatan', 'x_kelurahan',
            ],
            // $limit,
            // $offset
        );

        $message = empty($records) ? 'Data yang Anda cari tidak ditemukan' : 'Success';

         return ApiResponse::success(
            new CourierPriceListResourcesCollection($records, count($records), 0, 0),
            $message
        );
    }

    public function show(int $id)
    {
        $records = $this->odoo->read(
            'res.partner',
            [$id],
            [
                'id', 'name', 'ref',
                'contact_address', 'contact_address_complete',
                'city', 'zip', 'state_id', 'country_id',
                'x_dc_code', 'x_pulau', 'x_propinsi',
                'x_kecamatan', 'x_kelurahan',
            ]
        );

        if (empty($records)) {
            return ApiResponse::error('Courier Price List not found', [
                'id' => ['Data with that ID is not available']
            ], 404);
        }

        return ApiResponse::success(
            new CourierPriceListResources($records[0]),
            'Success, take the detailed Courier Price List',
            200
        );
    }
}