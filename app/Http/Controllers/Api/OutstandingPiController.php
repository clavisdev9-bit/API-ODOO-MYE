<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutstandingPiRequestValidationIndex;
use App\Http\Resources\OutstandingPiResourcesCollection;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class OutstandingPiController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(OutstandingPiRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $limit = $validated['limit'] ?? 10;
        $offset = $validated['offset'] ?? 0;

        $domain = [];

        $total = $this->odoo->searchCount('purchase.order', $domain);

        $purchaseOrders = $this->odoo->searchRead(
            'purchase.order',
            $domain,
            [
                'id',
                'name',
                'date_approve',
                'partner_id',
                'order_line',
                'picking_ids',
                'state',
                'amount_untaxed',
                'write_date'
            ],
            $limit,
            $offset,
            'id desc'
        );

        if (empty($purchaseOrders)) {
            return ApiResponse::paginate(
                new OutstandingPiResourcesCollection([], 0, $limit, $offset),
                'Data kosong'
            );
        }

        $rows = [];

        foreach ($purchaseOrders as $po) {

            /*
            |--------------------------------------------------------------------------
            | ORDER LINES
            |--------------------------------------------------------------------------
            */
            $orderLines = [];

            if (!empty($po['order_line'])) {
                $orderLines = $this->odoo->searchRead(
                    'purchase.order.line',
                    [['id', 'in', $po['order_line']]],
                    [
                        'product_id',
                        'product_qty',
                        'qty_received'
                    ],
                    0,
                    0
                );
            }

            /*
            |--------------------------------------------------------------------------
            | PICKINGS
            |--------------------------------------------------------------------------
            */
            $pickings = [];

            if (!empty($po['picking_ids'])) {
                $pickings = $this->odoo->searchRead(
                    'stock.picking',
                    [['id', 'in', $po['picking_ids']]],
                    [
                        'name',
                        'scheduled_date',
                        'date_done',
                        'state'
                    ],
                    0,
                    0
                );
            }

            /*
            |--------------------------------------------------------------------------
            | CALCULATIONS
            |--------------------------------------------------------------------------
            */
            $totalSku = count($orderLines);

            $qtySo = collect($orderLines)->sum('product_qty');

            $qtyGr = collect($orderLines)->sum('qty_received');

            $firstPicking = $pickings[0] ?? [];

            /*
            |--------------------------------------------------------------------------
            | BUILD RESPONSE
            |--------------------------------------------------------------------------
            */
            $rows[] = [
                'cust_po_no' => $po['name'] ?? null,

                'po_date' => $po['date_approve'] ?? null,

                'gr_no' => $firstPicking['name'] ?? null,

                'gr_date' => $firstPicking['date_done']
                    ?? $firstPicking['scheduled_date']
                    ?? null,

                'exp_date' => null,

                'customer' => $po['partner_id'][1] ?? null,

                'customer_alias' => null,

                'dc_name' => null,

                'area' => null,

                'total_sku' => $totalSku,

                'qty_so' => $qtySo,

                'qty_so_ctn' => null,

                'qty_gr' => $qtyGr,

                'qty_grn_ctn' => null,

                'qty_do' => null,

                'qty_do_ctn' => null,

                'amt_bef_tax' => $po['amount_untaxed'] ?? 0,

                'last_status' => $po['state'] ?? null,

                'last_update' => $po['write_date'] ?? null,

                'messages' => null,
            ];
        }

        return ApiResponse::paginate(
            new OutstandingPiResourcesCollection(
                $rows,
                $total,
                $limit,
                $offset
            ),
            'Success Outstanding PI'
        );
    }
}