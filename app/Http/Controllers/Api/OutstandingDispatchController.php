<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutstandingDispatchRequestValidationIndex;
use App\Http\Resources\OutstandingDispatchResourcesCollection;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class OutstandingDispatchController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(OutstandingDispatchRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $limit  = is_numeric($validated['limit'] ?? null)
            ? (int) $validated['limit']
            : 10;

        $offset = is_numeric($validated['offset'] ?? null)
            ? (int) $validated['offset']
            : 0;

        /*
        |--------------------------------------------------------------------------
        | DOMAIN FILTER
        |--------------------------------------------------------------------------
        */
        $domain = [];

        /*
        |--------------------------------------------------------------------------
        | TOTAL DATA
        |--------------------------------------------------------------------------
        */
        $total = $this->odoo->searchCount('stock.move', $domain);

        /*
        |--------------------------------------------------------------------------
        | STOCK MOVE
        |--------------------------------------------------------------------------
        */
        $moveRecords = $this->odoo->searchRead(
            'stock.move',
            $domain,
            [
                'id',
                'product_id',
                'product_uom_qty',
                'picking_id'
            ],
            $limit,
            $offset,
            'id desc'
        );

        if (empty($moveRecords)) {
            return ApiResponse::paginate(
                new OutstandingDispatchResourcesCollection([], 0, $limit, $offset),
                'Data kosong'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCT IDS
        |--------------------------------------------------------------------------
        */
        $productIds = collect($moveRecords)
            ->pluck('product_id')
            ->map(fn($item) => is_array($item) ? $item[0] : $item)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | PICKING IDS
        |--------------------------------------------------------------------------
        */
        $pickingIds = collect($moveRecords)
            ->pluck('picking_id')
            ->map(fn($item) => is_array($item) ? $item[0] : $item)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | PRODUCT RECORDS
        |--------------------------------------------------------------------------
        */
        $productRecords = $this->odoo->searchRead(
            'product.product',
            [['id', 'in', $productIds]],
            [
                'id',
                'weight',
                'volume'
            ],
            0,
            0
        );

        /*
        |--------------------------------------------------------------------------
        | PICKING RECORDS
        |--------------------------------------------------------------------------
        */
        $pickingRecords = $this->odoo->searchRead(
            'stock.picking',
            [['id', 'in', $pickingIds]],
            [
                'id',
                'partner_id'
            ],
            0,
            0
        );

        /*
        |--------------------------------------------------------------------------
        | PARTNER IDS
        |--------------------------------------------------------------------------
        */
        $partnerIds = collect($pickingRecords)
            ->pluck('partner_id')
            ->map(fn($item) => is_array($item) ? $item[0] : null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | PARTNER RECORDS
        |--------------------------------------------------------------------------
        */
        $partnerRecords = $this->odoo->searchRead(
            'res.partner',
            [['id', 'in', $partnerIds]],
            [
                'id',
                'commercial_company_name',
                'category_id'
            ],
            0,
            0
        );

        /*
        |--------------------------------------------------------------------------
        | MAPPING FAST ACCESS
        |--------------------------------------------------------------------------
        */
        $productById = collect($productRecords)->keyBy('id');
        $pickingById = collect($pickingRecords)->keyBy('id');
        $partnerById = collect($partnerRecords)->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | BUILD RESULT ROWS
        |--------------------------------------------------------------------------
        */
        $rows = [];

        foreach ($moveRecords as $move) {

            $pickingId = is_array($move['picking_id'] ?? null)
                ? $move['picking_id'][0]
                : null;

            $productId = is_array($move['product_id'] ?? null)
                ? $move['product_id'][0]
                : null;

            if (!$pickingId || !$productId) {
                continue;
            }

            $product = $productById->get($productId, []);
            $picking = $pickingById->get($pickingId, []);

            $partnerId = $picking['partner_id'][0] ?? null;

            $vendor = $partnerById->get($partnerId, []);

            /*
            |--------------------------------------------------------------------------
            | CALCULATION REAL VALUE ONLY
            |--------------------------------------------------------------------------
            */
            $qty    = (float) ($move['product_uom_qty'] ?? 0);
            $weight = (float) ($product['weight'] ?? 0);
            $volume = (float) ($product['volume'] ?? 0);

            $cbm = $volume * $qty;
            $kgs = $weight * $qty;

            $ratio = 6000;
            $vm    = $cbm * $ratio;
            $cw    = max($kgs, $vm);

            $rows[] = [
                'dc' => $vendor['commercial_company_name'] ?? null,

                'cbm' => $cbm,
                'kgs' => $kgs,

                'min_kgs' => 1,
                'lead_time' => 3,
                'ratio' => $ratio,

                'vm' => $vm,
                'cw' => $cw,

                'cheapest' => 1,

                'vendor_name' => $vendor['commercial_company_name'] ?? null,

                'service_name' => null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */
        return ApiResponse::paginate(
            new OutstandingDispatchResourcesCollection(
                $rows,
                $total,
                $limit,
                $offset
            ),
            'Success Outstanding Dispatch'
        );
    }
}