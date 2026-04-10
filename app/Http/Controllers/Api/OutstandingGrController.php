<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OutstandingGrCollection;
use App\Helpers\ApiResponse;
use App\Services\OdooService;
use Illuminate\Http\Request;

class OutstandingGrController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(Request $request)
    {
        $limit  = is_numeric($request->limit) ? (int) $request->limit : 10;
        $offset = is_numeric($request->offset) ? (int) $request->offset : 0;

        // =========================
        // 1. PURCHASE ORDER
        // =========================
        $poRecords = $this->odoo->searchRead(
            'purchase.order',
            [],
            ['id', 'name', 'date_approve'],
            $limit,
            $offset
        );

        $total = $this->odoo->searchCount('purchase.order', []);

        if (empty($poRecords)) {
            return ApiResponse::paginate(
                new OutstandingGrCollection([], 0, $limit, $offset),
                'Data tidak ditemukan'
            );
        }

        $poNames = array_column($poRecords, 'name');

        // =========================
        // 2. STOCK PICKING
        // =========================
        $stockRecords = $this->odoo->searchRead(
            'stock.picking',
            [['origin', 'in', $poNames]],
            ['id', 'origin', 'partner_id'],
            0,
            0
        );

        $stockIds = array_column($stockRecords, 'id');

        // =========================
        // 3. STOCK MOVE
        // =========================
        $moveRecords = [];

        if (!empty($stockIds)) {
            $moveRecords = $this->odoo->searchRead(
                'stock.move',
                [['picking_id', 'in', $stockIds]],
                ['id', 'picking_id', 'product_id', 'product_uom_qty'],
                0,
                0
            );
        }

        // =========================
        // 4. AMBIL PRODUCT IDS (FIX)
        // =========================
        $productIds = [];

        foreach ($moveRecords as $m) {

            if (!isset($m['product_id'])) continue;

            if (is_array($m['product_id'])) {
                $pid = $m['product_id'][0] ?? null;
            } else {
                $pid = $m['product_id'];
            }

            if (!empty($pid) && is_numeric($pid)) {
                $productIds[] = (int) $pid;
            }
        }

        $productIds = array_values(array_unique($productIds));

        // =========================
        // 5. PRODUCT
        // =========================
        $productRecords = [];

        if (!empty($productIds)) {
            $productRecords = $this->odoo->searchRead(
                'product.product',
                [['id', 'in', $productIds]],
                ['id', 'weight', 'volume'],
                0,
                0
            );
        }

        // =========================
        // GROUPING
        // =========================
        $stockByOrigin = [];
        foreach ($stockRecords as $s) {
            $origin = $s['origin'] ?? null;
            if ($origin) {
                $stockByOrigin[$origin][] = $s;
            }
        }

        $moveByPicking = [];
        foreach ($moveRecords as $m) {

            if (!isset($m['picking_id']) || empty($m['picking_id'])) {
                continue;
            }

            $pickingId = is_array($m['picking_id'])
                ? ($m['picking_id'][0] ?? null)
                : $m['picking_id'];

            if (!$pickingId) continue;

            $moveByPicking[$pickingId][] = $m;
        }

        $productById = [];
        foreach ($productRecords as $p) {
            $productById[$p['id']] = $p;
        }

        // =========================
        // BUILD DATA
        // =========================
        $rows = [];

        foreach ($poRecords as $po) {

            $poName = $po['name'] ?? null;
            if (!$poName) continue;

            $stocks = $stockByOrigin[$poName] ?? [];

            foreach ($stocks as $stock) {

                $pickingId = $stock['id'] ?? null;
                if (!$pickingId) continue;

                $moves = $moveByPicking[$pickingId] ?? [];

                foreach ($moves as $move) {

                    // PRODUCT ID
                    $productId = null;
                    $productName = null;

                    if (isset($move['product_id']) && is_array($move['product_id'])) {
                        $productId   = $move['product_id'][0] ?? null;
                        $productName = $move['product_id'][1] ?? null;
                    }

                    $product = $productById[$productId] ?? [];

                    $qty = $move['product_uom_qty'] ?? 0;

                    // CUSTOMER
                    $customer = null;
                    if (isset($stock['partner_id']) && is_array($stock['partner_id'])) {
                        $customer = $stock['partner_id'][1] ?? null;
                    }

                    $rows[] = [
                        'cust_po_no'    => $poName,
                        'so_date'       => null,
                        'approved_date' => $po['date_approve'] ?? null,
                        'customer'      => $customer,
                        'product_code'  => $productName,

                        'qty_so'  => $qty,
                        'qty_ctn' => 0,

                        'cbm' => ($product['volume'] ?? 0) * $qty,
                        'kgs' => ($product['weight'] ?? 0) * $qty,
                    ];
                }
            }
        }

        return ApiResponse::paginate(
            new OutstandingGrCollection($rows, $total, $limit, $offset),
            empty($rows) ? "Data tidak ditemukan" : "Success"
        );
    }
}