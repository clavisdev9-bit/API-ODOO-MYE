<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\DcResources;
use App\Http\Resources\DcResourcesCollection;
use App\Http\Requests\DcRequestValidationIndex;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class DcController extends Controller
{
  public function __construct(protected OdooService $odoo) {}

    public function index(DcRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $search      = $validated['search']      ?? null;
        $customerId  = $validated['customer_id'] ?? null;
        $limit       = is_numeric($validated['limit']  ?? null) ? (int) $validated['limit']  : 10;
        $offset      = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        $domain = [
            ['parent_id', '!=', false],
            ['x_dc_code', '!=', false],
            ['active',    '=',  true],
        ];

        if (!empty($customerId)) {
            $domain[] = ['parent_id', '=', (int) $customerId];
        }

        if (!empty($search)) {
            $domain[] = '|';
            $domain[] = '|';
            $domain[] = ['name',      'ilike', $search];
            $domain[] = ['x_dc_code', 'ilike', $search];
            $domain[] = ['city',      'ilike', $search];
        }

        $total   = $this->odoo->searchCount('res.partner', $domain);
        $records = $this->odoo->searchRead(
            'res.partner',
            $domain,
            [
                'id', 'name', 'ref', 'parent_id',
                'x_dc_code','x_dc_area',
                'x_min_lead_day', 'x_max_lead_day',
                'x_approved_by', 'x_approved_at',
                'street', 'street2', 'city', 'zip',
                'state_id', 'country_id',
                'contact_address', 'contact_address_complete',
                'x_pulau', 'x_propinsi', 'x_kecamatan', 'x_kelurahan',
                'phone', 'mobile',
                'active', 'create_date', 'write_date',
            ],
            $limit,
            $offset
        );

        $message = empty($records) ? 'Data yang Anda cari tidak ditemukan' : 'Success';

        return ApiResponse::success(
            new DcResourcesCollection($records, count($records), 0, 0),
            $message
        );
    }

    public function show(int $id)
    {
        $records = $this->odoo->read(
            'res.partner',
            [$id],
            [
                'id', 'name', 'ref', 'parent_id',
                'x_dc_code', 'x_dc_area',
                'x_min_lead_day', 'x_max_lead_day',
                'x_approved_by', 'x_approved_at',
                'street', 'street2', 'city', 'zip',
                'state_id', 'country_id',
                'contact_address', 'contact_address_complete',
                'x_pulau', 'x_propinsi', 'x_kecamatan', 'x_kelurahan',
                'phone', 'mobile',
                'active', 'create_date', 'write_date',
            ]
        );

        if (empty($records)) {
            return ApiResponse::error('DC not found', [
                'id' => ['Data with that ID is not available']
            ], 404);
        }

        // pastikan record ini memang DC (punya parent & dc_code)
        if (empty($records[0]['parent_id']) || empty($records[0]['x_dc_code'])) {
            return ApiResponse::error('DC not found', [
                'id' => ['Data with that ID is not a DC record']
            ], 404);
        }

        return ApiResponse::success(
            new DcResources($records[0]),
            'Success, take the detailed DC',
            200
        );
    }

    public function byCustomer(int $customerId, DcRequestValidationIndex $request)
{
    $validated = $request->validated();

    $search = $validated['search'] ?? null;
    $limit  = is_numeric($validated['limit']  ?? null) ? (int) $validated['limit']  : 10;
    $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

    // pastikan customer-nya exist dulu
    $customer = $this->odoo->read('res.partner', [$customerId], ['id', 'name', 'customer_rank']);

    if (empty($customer) || ($customer[0]['customer_rank'] ?? 0) === 0) {
        return ApiResponse::error('Customer not found', [
            'id' => ['Data with that ID is not available']
        ], 404);
    }

    $domain = [
        ['parent_id', '=', $customerId],
        ['x_dc_code', '!=', false],
        ['active',    '=',  true],
    ];

    if (!empty($search)) {
        $domain[] = '|';
        $domain[] = '|';
        $domain[] = ['name',      'ilike', $search];
        $domain[] = ['x_dc_code', 'ilike', $search];
        $domain[] = ['city',      'ilike', $search];
    }

    $total   = $this->odoo->searchCount('res.partner', $domain);
    $records = $this->odoo->searchRead(
        'res.partner',
        $domain,
        [
            'id', 'name', 'ref', 'parent_id',
            'x_dc_code', 'x_dc_area',
            'x_min_lead_day', 'x_max_lead_day',
            'x_approved_by', 'x_approved_at',
            'street', 'street2', 'city', 'zip',
            'state_id', 'country_id',
            'contact_address', 'contact_address_complete',
            'x_pulau', 'x_propinsi', 'x_kecamatan', 'x_kelurahan',
            'phone', 'mobile',
            'active', 'create_date', 'write_date',
        ],
        $limit,
        $offset
    );

    $message = empty($records) ? 'Data yang Anda cari tidak ditemukan' : 'Success';

        return ApiResponse::success(
            new DcResourcesCollection($records, count($records), 0, 0),
            $message
        );
}
}
