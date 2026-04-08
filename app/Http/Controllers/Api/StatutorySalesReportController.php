<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatutorySalesReportRequest;
use App\Http\Resources\StatutorySalesReportCollection;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class StatutorySalesReportController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(StatutorySalesReportRequest $request)
    {
        $validated = $request->validated();

        $limit  = $validated['limit'];
        $offset = $validated['offset'];

        // ================= PO =================
       $poRecords = $this->odoo->searchRead(
    'purchase.order',
    [['state', 'in', ['purchase', 'done']]],
    [
        'id',
        'name',
        'partner_id',
        'date_order' // ✅ TAMBAH INI
    ],
    $limit,
    $offset
);

        if (empty($poRecords)) {
            return ApiResponse::paginate(
                new StatutorySalesReportCollection([], 0, $limit, $offset),
                'Tidak ada data'
            );
        }

        $poIds   = array_column($poRecords, 'id');
        $poNames = array_column($poRecords, 'name');

        // ================= FETCH ALL =================
        $maps = $this->fetchAllData($poIds, $poNames);

        // ================= BUILD =================
        $rows = $this->buildRows($poRecords, $maps);

        return ApiResponse::paginate(
            new StatutorySalesReportCollection($rows, count($poRecords), $limit, $offset),
            'Success'
        );
    }

    // =====================================================
    // FETCH RELASI SESUAI DOKUMEN (INI KUNCI)
    // =====================================================
    private function fetchAllData(array $poIds, array $poNames): array
    {
        // ================= PO LINE =================
        $poLines = $this->odoo->searchRead(
            'purchase.order.line',
            [['order_id', 'in', $poIds]],
            ['order_id', 'product_qty'],
            0,
            0
        );

        // ================= GR =================
        $grRecords = $this->odoo->searchRead(
            'stock.picking',
            [
                ['purchase_id', 'in', $poIds],
                ['picking_type_code', '=', 'incoming'],
            ],
            ['id', 'name', 'purchase_id'],
            0,
            0
        );

        // ================= PI (Vendor Bill dari PO) =================
        $piRecords = $this->odoo->searchRead(
            'account.move',
            [
                ['move_type', '=', 'in_invoice'],
                ['invoice_origin', 'in', $poNames],
                ['state', '=', 'posted'],
            ],
            ['id', 'name', 'invoice_origin'],
            0,
            0
        );

        // ================= SO (dari PO) =================
        $soRecords = $this->odoo->searchRead(
            'sale.order',
            [
                ['client_order_ref', 'in', $poNames],
                ['state', 'in', ['sale', 'done']],
            ],
            ['id', 'name', 'client_order_ref'],
            0,
            0
        );

        $soIds   = array_column($soRecords, 'id');
        $soNames = array_column($soRecords, 'name');

        // ================= DO =================
        $doRecords = !empty($soIds)
            ? $this->odoo->searchRead(
                'stock.picking',
                [
                    ['sale_id', 'in', $soIds],
                    ['picking_type_code', '=', 'outgoing'],
                ],
                ['id', 'name', 'sale_id'],
                0,
                0
            )
            : [];

        // ================= SI (Customer Invoice) =================
        $siRecords = $this->odoo->searchRead(
            'account.move',
            [
                ['move_type', '=', 'out_invoice'],
                ['invoice_origin', 'in', $soNames],
                ['state', '=', 'posted'],
            ],
            ['id', 'name', 'invoice_origin'],
            0,
            0
        );

        // ================= PAYMENT =================
        $prRecords = $this->odoo->searchRead(
            'account.payment',
            [
                ['in', $soNames], // biasanya refer ke SO / invoice
                ['state', 'in', ['posted', 'reconciled']],
            ],
            ['id', 'name', 'amount'],
            0,
            0
        );

        return [
            'po_lines' => $this->groupBy($poLines, fn($r) => $r['order_id'][0] ?? null),

            'gr_by_po' => $this->groupBy($grRecords, fn($r) => $r['purchase_id'][0] ?? null),

            'pi_by_po' => $this->groupBy($piRecords, fn($r) => $r['invoice_origin'] ?? null),

            'so_by_po' => $this->groupBy($soRecords, fn($r) => $r['client_order_ref'] ?? null),

            'do_by_so' => $this->groupBy($doRecords, fn($r) => $r['sale_id'][0] ?? null),

            'si_by_so' => $this->groupBy($siRecords, fn($r) => $r['invoice_origin'] ?? null),

            'pr_by_ref' => $this->groupBy($prRecords, fn($r) => $r['ref'] ?? null),
        ];
    }

    // =====================================================
    // BUILD FINAL ROW (ANTI NULL VERSION)
    // =====================================================
    private function buildRows(array $poRecords, array $m): array
    {
        $rows = [];

        foreach ($poRecords as $po) {

            $poName = $po['name'];

            $so = $m['so_by_po'][$poName][0] ?? null;
            $soName = $so['name'] ?? null;

            $rows[] = [
            'cust_po_date' => $po['date_order'] ?? null,
                'customer'   => $po['partner_id'][1] ?? null,

                'po_qty' => array_sum(array_column(
                    $m['po_lines'][$po['id']] ?? [],
                    'product_qty'
                )),

                'gr_no' => $m['gr_by_po'][$po['id']][0]['name'] ?? null,

                'pi_ref_no' => $m['pi_by_po'][$poName][0]['name'] ?? null,

                'so_no' => $soName,

                'do_no' => $so
                    ? ($m['do_by_so'][$so['id']][0]['name'] ?? null)
                    : null,

                'si_no' => $soName
                    ? ($m['si_by_so'][$soName][0]['name'] ?? null)
                    : null,

                'pr_amt' => $soName
                    ? ($m['pr_by_ref'][$soName][0]['amount'] ?? null)
                    : null,
            ];
        }

        return $rows;
    }

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
}