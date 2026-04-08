<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DcResources;
use App\Http\Resources\DcResourcesCollection;
use App\Http\Requests\DcRequestValidationIndex;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class DcController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    // =========================
    // GET ALL DC
    // =========================
    public function index(DcRequestValidationIndex $request)
    {
        $validated  = $request->validated();
        $search     = $validated['search'] ?? null;
        $customerId = $validated['customer_id'] ?? null;

        $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        // ✅ Filter hanya DC (child contact)
        $domain = [
            ['active', '=', true],
            ['parent_id', '!=', false],
            ['type', 'in', ['delivery', 'contact']],
        ];

        // 👉 filter by customer
        if (!empty($customerId)) {
            $domain[] = ['parent_id', '=', (int) $customerId];
        }

        // 👉 search
        if (!empty($search)) {
            $domain[] = '|';
            $domain[] = '|';
            $domain[] = ['name', 'ilike', $search];
            $domain[] = ['city', 'ilike', $search];
            $domain[] = ['ref',  'ilike', $search];
        }

        $total = $this->odoo->searchCount('res.partner', $domain);

        $records = $this->odoo->searchRead(
            'res.partner',
            $domain,
            [
                'id', 'name', 'ref', 'parent_id',
                'city', 'zip',
                'state_id', 'country_id',
                'contact_address_complete',
                'phone',

                // 🔥 custom fields
                // 'x_area',
                // 'x_min_lead_day',
                // 'x_max_lead_day',
                // 'x_phone_2',
                // 'x_pulau',
                // 'x_propinsi',
                // 'x_kabupaten',
                // 'x_kecamatan',
                // 'x_kelurahan',
                // 'x_approved_by',
                // 'x_approved_at',

                'active', 'create_date', 'write_date',
            ],
            $limit,
            $offset
        );

        $message = empty($records)
            ? 'Data yang Anda cari tidak ditemukan'
            : 'Success';

        return ApiResponse::paginate(
            new DcResourcesCollection($records, $total, $limit, $offset),
            $message
        );
    }

    // =========================
    // SHOW DETAIL DC
    // =========================
    public function show(int $id)
    {
        $records = $this->odoo->read(
            'res.partner',
            [$id],
             [
                'id', 'name', 'ref', 'parent_id',
                'city', 'zip',
                'state_id', 'country_id',
                'contact_address_complete',
                'phone',

                // 🔥 custom fields
                // 'x_area',
                // 'x_min_lead_day',
                // 'x_max_lead_day',
                // 'x_phone_2',
                // 'x_pulau',
                // 'x_propinsi',
                // 'x_kabupaten',
                // 'x_kecamatan',
                // 'x_kelurahan',
                // 'x_approved_by',
                // 'x_approved_at',

                'active', 'create_date', 'write_date',
            ]
        );

        if (empty($records)) {
            return ApiResponse::error('DC not found', [
                'id' => ['Data with that ID is not available']
            ], 404);
        }

        return ApiResponse::success(
            new DcResources($records[0]),
            'Success, take the detailed DC'
        );
    }

    // =========================
    // GET DC BY CUSTOMER
    // =========================
    public function byCustomer(int $customerId, DcRequestValidationIndex $request)
    {
        $validated = $request->validated();
        $search    = $validated['search'] ?? null;

        $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        //  validasi customer
        $customer = $this->odoo->read(
            'res.partner',
            [$customerId],
            ['id', 'name', 'customer_rank']
        );

        if (empty($customer) || ($customer[0]['customer_rank'] ?? 0) === 0) {
            return ApiResponse::error('Customer not found', [
                'id' => ['Data with that ID is not available']
            ], 404);
        }

        //  ambil DC
        $domain = [
            ['parent_id', '=', $customerId],
            ['active', '=', true],
            ['type', 'in', ['delivery', 'contact']],
        ];

        //  search
        if (!empty($search)) {
            $domain[] = '|';
            $domain[] = '|';
            $domain[] = ['name', 'ilike', $search];
            $domain[] = ['city', 'ilike', $search];
            $domain[] = ['ref',  'ilike', $search];
        }

        $total = $this->odoo->searchCount('res.partner', $domain);

        $records = $this->odoo->searchRead(
            'res.partner',
            $domain,
            [
                'id', 'name', 'ref', 'parent_id',
                'city', 'zip',
                'state_id', 'country_id',
                'contact_address_complete',
                'phone',
                'active', 'create_date', 'write_date',
            ],
            $limit,
            $offset
        );

        $message = empty($records)
            ? 'Data yang Anda cari tidak ditemukan'
            : 'Success';

        return ApiResponse::paginate(
            new DcResourcesCollection($records, $total, $limit, $offset),
            $message
        );
    }
}