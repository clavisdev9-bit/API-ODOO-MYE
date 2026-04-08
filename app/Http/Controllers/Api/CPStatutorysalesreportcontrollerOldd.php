<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class StatutorySalesReportController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index()
    {
        // ── 1. PURCHASE ORDER ──────────────────────────
        $poRecords = $this->odoo->searchRead(
            'purchase.order',
            [['state', 'in', ['purchase', 'done']]],
            ['id', 'name', 'partner_ref', 'date_order', 'partner_id', 'date_planned'],
            100, 0
        );

        if (empty($poRecords)) {
            return ApiResponse::success([], 'Tidak ada data ditemukan');
        }

        $poIds      = array_column($poRecords, 'id');
        $partnerIds = array_unique(array_filter(array_map(fn($r) => $r['partner_id'][0] ?? null, $poRecords)));

        // ── 2. PO LINES ──────────────────────────
        $poLines = $this->odoo->searchRead(
            'purchase.order.line',
            [['order_id', 'in', $poIds]],
            ['id', 'order_id', 'product_qty', 'qty_received'],
            0, 0
        );

        // ── 3. GR (stock.picking incoming) ───────
        $grRecords = $this->odoo->searchRead(
            'stock.picking',
            [
                ['purchase_id', 'in', $poIds],
                ['picking_type_code', '=', 'incoming'],
                ['state', '=', 'done'],
            ],
            ['id', 'name', 'date_done', 'purchase_id', 'partner_id'],
            0, 0
        );

        $grIds = array_column($grRecords, 'id');

        // ── 4. GR MOVES (REAL QTY) ───────────────
        $grMoves = !empty($grIds) ? $this->odoo->searchRead(
            'stock.move.line',
            [['picking_id', 'in', $grIds]],
            ['id', 'picking_id', 'qty_done'],
            0, 0
        ) : [];

        // ── 5. PURCHASE INVOICE ─────────────────
        $piRecords = $this->odoo->searchRead(
            'account.move',
            [
                ['move_type', '=', 'in_invoice'],
                ['partner_id', 'in', array_values($partnerIds)],
                ['state', '=', 'posted'],
            ],
            ['id', 'name', 'ref', 'invoice_date', 'partner_id', 'amount_untaxed', 'amount_tax', 'amount_total'],
            0, 0
        );

        $piIds = array_column($piRecords, 'id');

        // ── 6. PI LINES ─────────────────────────
        $piLines = !empty($piIds) ? $this->odoo->searchRead(
            'account.move.line',
            [
                ['move_id', 'in', $piIds],
                ['display_type', '=', false]
            ],
            ['id', 'move_id', 'quantity'],
            0, 0
        ) : [];

        // ── 7. SALE ORDER ───────────────────────
        $soRecords = $this->odoo->searchRead(
            'sale.order',
            [
                ['partner_id', 'in', array_values($partnerIds)],
                ['state', 'in', ['sale', 'done']],
            ],
            ['id', 'name', 'date_order', 'partner_id', 'partner_shipping_id', 'commitment_date'],
            0, 0
        );

        $soIds = array_column($soRecords, 'id');

        // ── 8. SO LINES ─────────────────────────
        $soLines = !empty($soIds) ? $this->odoo->searchRead(
            'sale.order.line',
            [['order_id', 'in', $soIds]],
            ['id', 'order_id', 'product_uom_qty', 'qty_delivered'],
            0, 0
        ) : [];

        // ── 9. DO (stock.picking outgoing) ──────
        $doRecords = !empty($soIds) ? $this->odoo->searchRead(
            'stock.picking',
            [
                ['sale_id', 'in', $soIds],
                ['picking_type_code', '=', 'outgoing'],
            ],
            ['id', 'name', 'scheduled_date', 'date_done', 'sale_id', 'partner_id', 'state'],
            0, 0
        ) : [];

        $doIds = array_column($doRecords, 'id');

        // ── 10. DO MOVES (REAL QTY) ─────────────
        $doMoves = !empty($doIds) ? $this->odoo->searchRead(
            'stock.move.line',
            [['picking_id', 'in', $doIds]],
            ['id', 'picking_id', 'qty_done'],
            0, 0
        ) : [];

        // ── 11. SALES INVOICE ───────────────────
        $siRecords = $this->odoo->searchRead(
            'account.move',
            [
                ['move_type', '=', 'out_invoice'],
                ['partner_id', 'in', array_values($partnerIds)],
                ['state', '=', 'posted'],
            ],
            ['id', 'name', 'invoice_date', 'partner_id', 'amount_untaxed', 'amount_tax', 'amount_total', 'ref'],
            0, 0
        );

        $siIds = array_column($siRecords, 'id');

        // ── 12. SI LINES ────────────────────────
        $siLines = !empty($siIds) ? $this->odoo->searchRead(
            'account.move.line',
            [
                ['move_id', 'in', $siIds],
                ['display_type', '=', false]
            ],
            ['id', 'move_id', 'quantity'],
            0, 0
        ) : [];

        // ── 13. PAYMENT ─────────────────────────
        $prRecords = $this->odoo->searchRead(
            'account.payment',
            [
                ['partner_id', 'in', array_values($partnerIds)],
                ['payment_type', '=', 'outbound'],
                ['state', 'in', ['posted', 'reconciled']],
            ],
            // ['id', 'name', 'date', 'amount', 'partner_id', 'ref'],
            ['id', 'name', 'date', 'amount', 'partner_id', 'payment_reference'],
            0, 0
        );

        // ── GROUPING ────────────────────────────
        $maps = [
            'po_lines' => $this->groupBy($poLines, fn($r) => $r['order_id'][0] ?? null),
            'gr_by_po' => $this->groupBy($grRecords, fn($r) => $r['purchase_id'][0] ?? null),
            'gr_moves' => $this->groupBy($grMoves, fn($r) => $r['picking_id'][0] ?? null),
            'pi_by_partner' => $this->groupBy($piRecords, fn($r) => $r['partner_id'][0] ?? null),
            'pi_lines' => $this->groupBy($piLines, fn($r) => $r['move_id'][0] ?? null),
            'so_by_partner' => $this->groupBy($soRecords, fn($r) => $r['partner_id'][0] ?? null),
            'so_lines' => $this->groupBy($soLines, fn($r) => $r['order_id'][0] ?? null),
            'do_by_so' => $this->groupBy($doRecords, fn($r) => $r['sale_id'][0] ?? null),
            'do_moves' => $this->groupBy($doMoves, fn($r) => $r['picking_id'][0] ?? null),
            'si_by_partner' => $this->groupBy($siRecords, fn($r) => $r['partner_id'][0] ?? null),
            'si_lines' => $this->groupBy($siLines, fn($r) => $r['move_id'][0] ?? null),
            'pr_by_partner' => $this->groupBy($prRecords, fn($r) => $r['partner_id'][0] ?? null),
        ];

        return ApiResponse::success([
            'total' => count($poRecords),
            'records' => $this->buildReportRows($poRecords, $maps),
        ], 'Success');
    }

   private function buildReportRows(array $poRecords, array $m): array
{
    $rows = [];

    foreach ($poRecords as $po) {
        $partnerId = $po['partner_id'][0] ?? null;

        // ================= GR =================
        $grList = $m['gr_by_po'][$po['id']] ?? [];
        $gr = $grList[0] ?? [];

        $grMoves = !empty($grList)
            ? array_merge(...array_map(fn($g) => $m['gr_moves'][$g['id']] ?? [], $grList))
            : [];

        $grQty = array_sum(array_column($grMoves, 'qty_done'));

        // ================= PI =================
        $pi = ($m['pi_by_partner'][$partnerId] ?? [])[0] ?? [];
        $piLines = $pi ? ($m['pi_lines'][$pi['id']] ?? []) : [];

        $piQty = array_sum(array_column($piLines, 'quantity'));

        // ================= SO =================
        $so = ($m['so_by_partner'][$partnerId] ?? [])[0] ?? [];
        $soLines = $so ? ($m['so_lines'][$so['id']] ?? []) : [];

        // ================= DO =================
        $doList = $so ? ($m['do_by_so'][$so['id']] ?? []) : [];
        $do = $doList[0] ?? [];

        $doMoves = !empty($doList)
            ? array_merge(...array_map(fn($d) => $m['do_moves'][$d['id']] ?? [], $doList))
            : [];

        $doQty = array_sum(array_column($doMoves, 'qty_done'));

        // ================= POD =================
        $pod = current(array_filter($doList, fn($d) => ($d['state'] ?? '') === 'done')) ?: [];

        $podMoves = $pod ? ($m['do_moves'][$pod['id']] ?? []) : [];
        $podQty = array_sum(array_column($podMoves, 'qty_done'));

        // ================= SI =================
        $si = ($m['si_by_partner'][$partnerId] ?? [])[0] ?? [];
        $siLines = $si ? ($m['si_lines'][$si['id']] ?? []) : [];

        $siQty = array_sum(array_column($siLines, 'quantity'));

        // ================= PR =================
        $pr = ($m['pr_by_partner'][$partnerId] ?? [])[0] ?? [];

        // ================= BUILD ROW =================
        $rows[] = [
            // ===== PURCHASE =====
            'cust_po_no'        => $po['name'],
            'new_cust_po_no'    => $po['partner_ref'] ?? null,
            'cust_po_date'      => $po['date_order'],
            'po_no'             => $po['name'],
            'po_qty'            => array_sum(array_column($m['po_lines'][$po['id']] ?? [], 'product_qty')),
            'req_delivery_date' => $po['date_planned'] ?? null,

            // ===== CUSTOMER =====
            'customer'          => $po['partner_id'][1] ?? null,
            'ship_to'           => $so['partner_shipping_id'][1] ?? null,

            // ===== GR =====
            'gr_no'             => $gr['name'] ?? null,
            'gr_date'           => $gr['date_done'] ?? null,
            'gr_qty'            => $grQty,

            // ===== PI =====
            'pi_ref_no'         => $pi['ref'] ?? null,
            'pi_date'           => $pi['invoice_date'] ?? null,
            'pi_qty'            => $piQty,
            'gr_pi_amt_bef_tax' => $pi['amount_untaxed'] ?? null,
            'gr_pi_amt_tax'     => $pi['amount_tax'] ?? null,
            'gr_pi_amt_afr_tax' => $pi['amount_total'] ?? null,

            // ===== SO =====
            'so_no'             => $so['name'] ?? null,
            'so_date'           => $so['date_order'] ?? null,
            'so_qty'            => array_sum(array_column($soLines, 'product_uom_qty')),

            // ===== DO =====
            'do_no'             => $do['name'] ?? null,
            'do_date'           => $do['scheduled_date'] ?? null,
            'do_qty'            => $doQty,

            // ===== POD =====
            'pod_no'            => $pod['name'] ?? null,
            'pod_date'          => $pod['date_done'] ?? null,
            'pod_qty'           => $podQty,

            // ===== SI =====
            'si_no'             => $si['name'] ?? null,
            'si_date'           => $si['invoice_date'] ?? null,
            'si_qty'            => $siQty,
            'si_amt_bef_tax'    => $si['amount_untaxed'] ?? null,

            // ===== PR =====
            'pr_no'             => $pr['name'] ?? null,
            'pr_date'           => $pr['date'] ?? null,
            'pr_amt'            => $pr['amount'] ?? null,

            // ===== MESSAGE =====
            'message'           => null,
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