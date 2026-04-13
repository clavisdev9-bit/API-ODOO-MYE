<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutstandingDispatchWithFreightCostRequestValidationIndex;
use App\Http\Resources\OutstandingDispatchWithFreightCostResourcesCollection;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class OutstandingDispatchWithFreightCostController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(OutstandingDispatchWithFreightCostRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        $domain = [];

        $total = $this->odoo->searchCount('stock.move', $domain);

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
                new OutstandingDispatchWithFreightCostResourcesCollection([], 0, $limit, $offset),
                'Data kosong'
            );
        }

        $productIds = collect($moveRecords)
            ->pluck('product_id')
            ->map(fn($item) => $item[0] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $pickingIds = collect($moveRecords)
            ->pluck('picking_id')
            ->map(fn($item) => $item[0] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $productRecords = $this->odoo->searchRead(
            'product.product',
            [['id', 'in', $productIds]],
            ['id', 'weight', 'volume'],
            0,
            0
        );

        $pickingRecords = $this->odoo->searchRead(
            'stock.picking',
            [['id', 'in', $pickingIds]],
            ['id', 'partner_id'],
            0,
            0
        );

        $partnerIds = collect($pickingRecords)
            ->pluck('partner_id')
            ->map(fn($item) => $item[0] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $partnerRecords = $this->odoo->searchRead(
            'res.partner',
            [['id', 'in', $partnerIds]],
            ['id', 'commercial_company_name', 'category_id'],
            0,
            0
        );

        $productById = collect($productRecords)->keyBy('id');
        $pickingById = collect($pickingRecords)->keyBy('id');
        $partnerById = collect($partnerRecords)->keyBy('id');

        $rows = [];

        foreach ($moveRecords as $move) {

            $productId = $move['product_id'][0] ?? null;
            $pickingId = $move['picking_id'][0] ?? null;

            if (!$productId || !$pickingId) continue;

            $product = $productById[$productId] ?? [];
            $picking = $pickingById[$pickingId] ?? [];

            $partnerId = $picking['partner_id'][0] ?? null;

            $vendor = $partnerById[$partnerId] ?? [];

            $qty = (float) ($move['product_uom_qty'] ?? 0);

            $weight = (float) ($product['weight'] ?? 0);
            $volume = (float) ($product['volume'] ?? 0);

            $cbm = $volume * $qty;
            $kgs = $weight * $qty;

            $ratio = 6000;

            $vm = $cbm * $ratio;
            $cw = max($kgs, $vm);

            $price = 8100;

            $rows[] = [
                'dc' => $vendor['commercial_company_name'] ?? null,
                'cbm' => $cbm,
                'kgs' => $kgs,
                'min_kgs' => 1,
                'lead_time' => 3,
                'ratio' => $ratio,
                'vm' => $vm,
                'cw' => $cw,
                'price' => $price,
                'est_courier_cost' => $price,
                'cheapest' => 1,
                'vendor_name' => $vendor['commercial_company_name'] ?? null,
                'service_name' => null,
            ];
        }

        return ApiResponse::paginate(
            new OutstandingDispatchWithFreightCostResourcesCollection(
                $rows,
                $total,
                $limit,
                $offset
            ),
            'Success Outstanding Dispatch With Freight Cost'
        );
    }
}