<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomersResources;
use App\Http\Resources\CustomersResourcesCollection;
use App\Http\Requests\CustomersRequestValidationIndex;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class CustomersController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    // =====================================================
    // LIST CUSTOMER (WITH FILTER & PAGINATION)
    // =====================================================
    public function index(CustomersRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;
        $search = $validated['search'] ?? null;
        $type   = $validated['type'] ?? 'customer'; // 🔥 default customer

        // =========================
        // DOMAIN BASE
        // =========================
        $domain = [
            ['active', '=', true],
        ];

        // =========================
        // FILTER TYPE
        // =========================
        switch ($type) {
            case 'supplier':
                $domain[] = ['supplier_rank', '>', 0];
                break;

            case 'contact':
                $domain[] = ['customer_rank', '=', 0];
                $domain[] = ['supplier_rank', '=', 0];
                break;

            default: // customer
                $domain[] = ['customer_rank', '>', 0];
                break;
        }

        // =========================
        // SEARCH
        // =========================
        if (!empty($search)) {
            $domain[] = '|';
            $domain[] = '|';
            $domain[] = ['name', 'ilike', $search];
            $domain[] = ['email', 'ilike', $search];
            $domain[] = ['phone', 'ilike', $search];
        }

        // =========================
        // TOTAL
        // =========================
        $total = $this->odoo->searchCount('res.partner', $domain);

        // =========================
        // GET DATA (LIGHTWEIGHT)
        // =========================
        $records = $this->odoo->searchRead(
            'res.partner',
            $domain,
            [
                'id',
                'name',
                'x_studio_alias',
                'phone',
                'email',
                'city',
                'country_id',
                'active',
            ],
            $limit,
            $offset
        );

        $message = empty($records)
            ? "Data yang Anda cari tidak ditemukan"
            : "Success";

        return ApiResponse::paginate(
            new CustomersResourcesCollection($records, $total, $limit, $offset),
            $message
        );
    }

    // =====================================================
    // DETAIL CUSTOMER
    // =====================================================
    public function show(int $id)
    {
        $records = $this->odoo->read(
            'res.partner',
            [$id],
            [
                'id', 'name', 'x_studio_alias', 'company_name', 'function',
                'street', 'street2', 'city', 'zip',
                'state_id', 'country_id', 'contact_address_complete',
                'email', 'phone', 'website',
                'vat', 'company_registry',
                'currency_id',
                'comment',
                'is_company', 'supplier_rank', 'customer_rank', 'active',
                'create_date', 'write_date',

                // custom fields (optional)
                // 'x_alias_name',
                // 'x_fax',
                // 'x_studio_id',
                // 'remark_1',
                // 'remark_2',
                // 'remark_3',
                // 'remark_4',
                // 'remark_5',
            ]
        );

        if (empty($records)) {
            return ApiResponse::error('Customer not found', [
                'id' => ['Data dengan ID tersebut tidak ditemukan']
            ], 404);
        }

        return ApiResponse::success(
            new CustomersResources($records[0]),
            'Success, take the detailed Customer',
            200
        );
    }
}