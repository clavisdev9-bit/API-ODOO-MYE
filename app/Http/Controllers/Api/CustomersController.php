<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CustomersResources;
use App\Http\Resources\CustomersResourcesCollection;
use App\Http\Requests\CustomersRequest;
use App\Http\Requests\CustomersRequestValidationIndex;
use App\Http\Requests\DcRequestValidationIndex;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class CustomersController extends Controller
{
    public function __construct(protected OdooService $odoo) {}


    public function index(CustomersRequestValidationIndex $request)
{
    $validated = $request->validated();

    $search = $validated['search'] ?? null;

    // =========================
    // DOMAIN
    // =========================
    $domain = [
        ['customer_rank', '>', 0],
        ['active', '=', true],
    ];

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
    // GET DATA (TANPA LIMIT OFFSET)
    // =========================
    $records = $this->odoo->searchRead(
        'res.partner',
        $domain,
        [
            'id', 'name', 'ref', 'company_name', 'function',
            'street', 'street2', 'city', 'zip',
            'state_id', 'country_id', 'contact_address_complete',
            'email', 'phone', 'mobile', 'website',
            'vat', 'company_registry',
            'currency_id',
            'comment',
            'is_company', 'supplier_rank', 'customer_rank', 'active',
            'create_date', 'write_date',
        ]
        // ❌ tidak pakai limit & offset
    );

    $message = empty($records)
        ? "Data yang Anda cari tidak ditemukan"
        : "Success";
            // =========================
            // RESPONSE TANPA PAGINATION
            // =========================
        return ApiResponse::success(
            new CustomersResourcesCollection($records, count($records), 0, 0),
            $message
        );
}

        // GET /api/odoo/customers/{id}
        public function show(int $id)
        {
            $records = $this->odoo->read(
                'res.partner',
                [$id],
               [
                    'id', 'name', 'ref', 'company_name','function',
                    'street', 'street2', 'city', 'zip',
                    'state_id', 'country_id', 'contact_address_complete',
                    'email', 'phone', 'mobile', 'website',
                    'vat', 'company_registry',
                    'currency_id',
                    'comment',
                    'is_company', 'supplier_rank', 'customer_rank', 'active',
                    'create_date', 'write_date',
                ],
            );

            if (empty($records)) {
                return ApiResponse::error('Customer not found', [
                    'id' => ['Data with that ID is not available']
                ], 404);
            }

            return ApiResponse::success(
                new CustomersResources($records[0]),
                'Success, take the detailed Customer',
                200
            );
        }



        
    }
