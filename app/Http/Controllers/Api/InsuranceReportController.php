<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Services\OdooService;
use Illuminate\Http\Request;
use App\Http\Resources\InsuranceReportResources;
use App\Http\Resources\InsuranceReportResourcesCollection;
use App\Http\Requests\InsuranceReportRequestValidationIndex;

class InsuranceReportController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(InsuranceReportRequestValidationIndex $request)
    {
        $validated   = $request->validated();
        $search      = $validated['search']      ?? null;
        $partnerId   = $validated['partner_id']  ?? null;
        $dateFrom    = $validated['date_from']   ?? null;
        $dateTo      = $validated['date_to']     ?? null;
        $limit  = is_numeric($validated['limit']  ?? null) ? (int) $validated['limit']  : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        // =========================
        // DOMAIN
        // =========================
        $domain = [
            ['move_type', '=', 'out_invoice'],
            ['state', '=', 'posted'],
        ];

        if (!empty($partnerId)) {
            $domain[] = ['partner_id', '=', (int) $partnerId];
        }

        if (!empty($dateFrom)) {
            $domain[] = ['invoice_date', '>=', $dateFrom];
        }

        if (!empty($dateTo)) {
            $domain[] = ['invoice_date', '<=', $dateTo];
        }

        // SEARCH (name / customer)
        if (!empty($search)) {
            $domain[] = '|';
            $domain[] = ['name', 'ilike', $search];
            $domain[] = ['partner_id', 'ilike', $search];
        }

        // =========================
        // GET DATA
        // =========================
        $total = $this->odoo->searchCount('account.move', $domain);

        $records = $this->odoo->searchRead(
                'account.move',
                $domain,
                [
                    'id',
                    'partner_id',
                    'name',
                    'invoice_date',
                    'invoice_date_due',
                    'invoice_payment_term_id',
                    'amount_total',
                    'invoice_payments_widget'
                ],
                $limit,   
                $offset 
            );

         $message = empty($records) ? "Data yang Anda cari tidak ditemukan" : "Success";

        return ApiResponse::paginate(
            new InsuranceReportResourcesCollection($records, $total, $limit, $offset),
            $message
        );
    }

    public function show(int $id)
    {
        $records = $this->odoo->read(
            'account.move',
            [$id],
            [
                'partner_id',
                'name',
                'invoice_date',
                'invoice_date_due',
                'invoice_payment_term_id',
                'amount_total',
                'invoice_payments_widget'
            ]
        );

        if (empty($records)) {
            return ApiResponse::error('Invoice not found', [
                'id' => ['Data dengan ID tersebut tidak ditemukan']
            ], 404);
        }

        return ApiResponse::success(
            new InsuranceReportResources($records[0]),
            'Success, take the detailed invoice',
            200
        );
    }
}