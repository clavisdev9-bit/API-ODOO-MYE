<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Services\OdooService;
use App\Http\Resources\PodHandOverCollection;

class PodHandOverController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(Request $request)
    {
        $limit  = is_numeric($request->limit ?? null) ? (int) $request->limit : 10;
        $offset = is_numeric($request->offset ?? null) ? (int) $request->offset : 0;

        // =========================
        // DOMAIN
        // =========================
        $domain = [
            ['state', 'in', ['purchase', 'done']]
        ];

        // =========================
        // TOTAL
        // =========================
        $total = $this->odoo->searchCount('purchase.order', $domain);

        // =========================
        // PO
        // =========================
        $poRecords = $this->odoo->searchRead(
            'purchase.order',
            $domain,
            ['id', 'name', 'partner_id', 'date_order'],
            $limit,
            $offset
        );

        if (empty($poRecords)) {
            return ApiResponse::paginate(
                new PodHandOverCollection([], 0, $limit, $offset),
                'Data tidak ditemukan'
            );
        }

        $poNames = array_column($poRecords, 'name');

        // =========================
        // SO (WAJIB)
        // =========================
        $soRecords = $this->odoo->searchRead(
            'sale.order',
            [
                '|',
                ['client_order_ref', 'in', $poNames],
                ['origin', 'in', $poNames],
            ],
            ['id', 'name', 'client_order_ref', 'origin'],
            0,
            0
        );

        $soIds = array_column($soRecords, 'id');

        // =========================
        // DO (DARI SO)
        // =========================
        $doRecords = !empty($soIds)
            ? $this->odoo->searchRead(
                'stock.picking',
                [
                    ['sale_id', 'in', $soIds],
                    ['picking_type_code', '=', 'outgoing'],
                ],
                [
                    'id',
                    'name',
                    'sale_id',
                    'scheduled_date',
                    'partner_id',
                    'user_id'
                ],
                0,
                0
            )
            : [];

        // =========================
        // MOVE (QTY)
        // =========================
        $moveRecords = !empty($doRecords)
            ? $this->odoo->searchRead(
                'stock.move',
                [
                    ['picking_id', 'in', array_column($doRecords, 'id')],
                ],
                ['picking_id', 'product_uom_qty'],
                0,
                0
            )
            : [];

        // =========================
        // GROUPING
        // =========================
        $soByPo = $this->groupBy($soRecords, function ($r) {
            return $r['client_order_ref']
                ?? $r['origin']
                ?? null;
        });

        $doBySo = $this->groupBy($doRecords, fn($r) => $r['sale_id'][0] ?? null);

        $moveByPicking = $this->groupBy($moveRecords, fn($r) => $r['picking_id'][0] ?? null);

        // =========================
        // BUILD RESPONSE
        // =========================
        $rows = [];

        foreach ($poRecords as $po) {

            $poName = $po['name'];

            // SO dari PO
            $so = $soByPo[$poName][0] ?? null;

            // DO dari SO
            $do = $so
                ? ($doBySo[$so['id']][0] ?? null)
                : null;

            // QTY
            $qty = 0;
            if ($do) {
                $moves = $moveByPicking[$do['id']] ?? [];
                $qty = array_sum(array_column($moves, 'product_uom_qty'));
            }

            $rows[] = [
                'do_sent'        => $do ? 'YES' : 'NO',
                'do_received'    => 'NO',
                'cancel_status'  => 'NO',

                'cust_po_no'     => $poName,
                'awb_no'         => null,

                'cust_po_date'   => $po['date_order'] ?? null,
                'customer_name'  => $po['partner_id'][1] ?? null,

                'dc_name'        => $do['partner_id'][1] ?? null,
                'do_no'          => $do['name'] ?? null,
                'do_date'        => $do['scheduled_date'] ?? null,

                'shipped_by'     => $do['user_id'][1] ?? null,

                'qty_shipped'    => $qty,
                'qty_box'        => null,

                'do_sent_by'     => null,
                'do_sent_at'     => null,
                'do_received_by' => null,
                'do_received_at' => null,
            ];
        }

        $message = empty($rows)
            ? 'Data tidak ditemukan'
            : 'Success';

        return ApiResponse::paginate(
            new PodHandOverCollection($rows, $total, $limit, $offset),
            $message
        );
    }

    // =========================
    // HELPER GROUP BY
    // =========================
    private function groupBy(array $items, callable $keyFn): array
    {
        $result = [];

        foreach ($items as $item) {
            $key = $keyFn($item);
            if ($key !== null) {
                $result[$key][] = $item;
            }
        }

        return $result;
    }


   public function detail(Request $request)
{
    $poName = $request->po_name;

    if (empty($poName)) {
        return ApiResponse::error('Parameter po_name wajib diisi', [
            'po_name' => ['Parameter tidak boleh kosong']
        ], 422);
    }

    // =========================
    // GET PO
    // =========================
    $poRecords = $this->odoo->searchRead(
        'purchase.order',
        [
            ['name', '=', $poName],
            ['state', 'in', ['purchase', 'done']]
        ],
        ['id', 'name', 'partner_id', 'date_order'],
        1,
        0
    );

    if (empty($poRecords)) {
        return ApiResponse::error('Data tidak ditemukan', [
            'po_name' => ['PO tidak ditemukan']
        ], 404);
    }

    $po = $poRecords[0];

    // =========================
    // DO
    // =========================
    $doRecords = $this->odoo->searchRead(
        'stock.picking',
        [
            ['origin', '=', $poName],
            ['picking_type_code', '=', 'outgoing'],
        ],
        [
            'id',
            'name',
            'origin',
            'scheduled_date',
            'partner_id',
            'user_id'
        ],
        1,
        0
    );

    $do = $doRecords[0] ?? null;

    // =========================
    // QTY
    // =========================
    $qty = 0;

    if ($do) {
        $moveRecords = $this->odoo->searchRead(
            'stock.move',
            [
                ['picking_id', '=', $do['id']],
            ],
            ['product_uom_qty'],
            0,
            0
        );

        $qty = array_sum(array_column($moveRecords, 'product_uom_qty'));
    }

    // =========================
    // BUILD RESPONSE
    // =========================
    $result = [
        'do_sent'        => $do ? 'YES' : 'NO',
        'do_received'    => 'NO',
        'cancel_status'  => 'NO',

        'cust_po_no'     => $po['name'],
        'awb_no'         => null,

        'cust_po_date'   => $po['date_order'] ?? null,
        'customer_name'  => $po['partner_id'][1] ?? null,

        'dc_name'        => $do['partner_id'][1] ?? null,
        'do_no'          => $do['name'] ?? null,
        'do_date'        => $do['scheduled_date'] ?? null,

        'shipped_by'     => $do['user_id'][1] ?? null,

        'qty_shipped'    => $qty,
        'qty_box'        => null,

        'do_sent_by'     => null,
        'do_sent_at'     => null,
        'do_received_by' => null,
        'do_received_at' => null,
    ];

    return ApiResponse::success(
        $result,
        'Success'
    );
}
}