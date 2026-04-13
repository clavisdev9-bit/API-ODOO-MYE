<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Services\OdooService;
use Illuminate\Http\Request;

class OutstandingDispatchController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(Request $request)
    {
        $limit  = is_numeric($request->limit) ? (int) $request->limit : 10;
        $offset = is_numeric($request->offset) ? (int) $request->offset : 0;
        // =========================
        // 1. STOCK MOVE
        // =========================
        // $moveRecords = $this->odoo->searchRead(
        //     'stock.move',
        //     [],
        //     ['id', 'product_id', 'product_uom_qty', 'picking_id'],
        //     $limit,
        //     $offset
        // );
        $domain = [
    
];

$moveRecords = $this->odoo->searchRead(
    'stock.move',
    $domain,
    ['id', 'product_id', 'product_uom_qty', 'picking_id'],
    $limit,
    $offset,
    'id desc'
);

        if (empty($moveRecords)) {
            return ApiResponse::success([], 'Data kosong');
        }

        // =========================
        // 2. PRODUCT IDS
        // =========================
        $productIds = [];

        foreach ($moveRecords as $m) {
            if (!isset($m['product_id'])) continue;

            $pid = is_array($m['product_id'])
                ? ($m['product_id'][0] ?? null)
                : $m['product_id'];

            if ($pid) $productIds[] = (int) $pid;
        }

        $productIds = array_values(array_unique($productIds));

        // =========================
        // 3. PRODUCT
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

        $productById = [];
        foreach ($productRecords as $p) {
            $productById[$p['id']] = $p;
        }

        // =========================
        // 4. PICKING IDS
        // =========================
        $pickingIds = [];

        foreach ($moveRecords as $m) {
            if (!isset($m['picking_id'])) continue;

            $pid = is_array($m['picking_id'])
                ? ($m['picking_id'][0] ?? null)
                : $m['picking_id'];

            if ($pid) $pickingIds[] = (int) $pid;
        }

        $pickingIds = array_values(array_unique($pickingIds));

        // =========================
        // 5. PICKING
        // =========================
        $pickingRecords = [];

        if (!empty($pickingIds)) {
            $pickingRecords = $this->odoo->searchRead(
                'stock.picking',
                [['id', 'in', $pickingIds]],
                ['id', 'partner_id'],
                0,
                0
            );
        }

        $pickingById = [];
        foreach ($pickingRecords as $p) {
            $pickingById[$p['id']] = $p;
        }

        // =========================
        // 6. PARTNER
        // =========================
        $partnerIds = [];

        foreach ($pickingRecords as $p) {
            if (isset($p['partner_id']) && is_array($p['partner_id'])) {
                $partnerIds[] = $p['partner_id'][0];
            }
        }

        $partnerIds = array_values(array_unique($partnerIds));

        $partnerRecords = [];

        if (!empty($partnerIds)) {
            $partnerRecords = $this->odoo->searchRead(
                'res.partner',
                [['id', 'in', $partnerIds]],
                ['id', 'commercial_company_name', 'category_id'],
                0,
                0
            );
        }

        $partnerById = [];
        foreach ($partnerRecords as $p) {
            $partnerById[$p['id']] = $p;
        }

        // =========================
        // 🔥 7. GROUPING PICKING + PRODUCT
        // =========================
       // =========================
// 🔥 7. GROUPING PER PICKING SAJA
// =========================
$grouped = [];

foreach ($moveRecords as $move) {

    if (!isset($move['picking_id'])) continue;

    $pickingId = is_array($move['picking_id'])
        ? ($move['picking_id'][0] ?? null)
        : $move['picking_id'];

    if (!$pickingId) continue;

    $productId = is_array($move['product_id'])
        ? ($move['product_id'][0] ?? null)
        : $move['product_id'];

    if (!$productId) continue;

    $product = $productById[$productId] ?? [];

    $qty = (float) ($move['product_uom_qty'] ?? 0);

    $weight = isset($product['weight']) ? (float) $product['weight'] : 0;
    $volume = isset($product['volume']) ? (float) $product['volume'] : 0;

    // fallback
    if ($weight <= 0) $weight = 1;
    if ($volume <= 0) $volume = 0.001;

    $cbm = $volume * $qty;
    $kgs = $weight * $qty;

    // 🔥 KEY SEKARANG HANYA PICKING
    if (!isset($grouped[$pickingId])) {
        $grouped[$pickingId] = [
            'picking_id' => $pickingId,
            'cbm' => 0,
            'kgs' => 0,
        ];
    }

    $grouped[$pickingId]['cbm'] += $cbm;
    $grouped[$pickingId]['kgs'] += $kgs;
}

        // =========================
        // 8. BUILD RESULT
        // =========================
        $rows = [];

foreach ($moveRecords as $move) {

    $pickingId = is_array($move['picking_id'])
        ? ($move['picking_id'][0] ?? null)
        : $move['picking_id'];

    $productId = is_array($move['product_id'])
        ? ($move['product_id'][0] ?? null)
        : $move['product_id'];

    if (!$pickingId || !$productId) continue;

    $product = $productById[$productId] ?? [];
    $picking = $pickingById[$pickingId] ?? [];

    $partnerId = $picking['partner_id'][0] ?? null;
    $vendor = $partnerById[$partnerId] ?? [];

    $qty = (float) ($move['product_uom_qty'] ?? 0);

    $weight = (float) ($product['weight'] ?? 0);
    $volume = (float) ($product['volume'] ?? 0);

    if ($weight <= 0) $weight = 1;
    if ($volume <= 0) $volume = 0.001;

    $cbm = $volume * $qty;
    $kgs = $weight * $qty;

    $ratio = 6000;
    $vm = $cbm * $ratio;
    $cw = max($kgs, $vm);

    $rows[] = [
        'dc' => $vendor['commercial_company_name'] ?? null,
        'cbm' => round($cbm, 4),
        'kgs' => round($kgs, 4),
        'min_kgs' => 1,
        'lead_time' => 3,
        'ratio' => $ratio,
        'vm' => round($vm, 4),
        'cw' => round($cw, 4),
        'cheapest' => 1,
        'vendor_name' => $vendor['commercial_company_name'] ?? null,
        'service_name' => $vendor['category_id'][1] ?? null,
    ];
}

        return ApiResponse::success($rows, 'Success Outstanding Dispatch');
    }
}