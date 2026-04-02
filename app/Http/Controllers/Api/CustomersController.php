<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CustomersResources;
use App\Http\Resources\CustomersResourcesCollection;
use App\Http\Requests\CustomersRequest;
use App\Http\Requests\CustomersRequestValidationIndex;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class CustomersController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

       public function index(CustomersRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $limit  = is_numeric($validated['limit']  ?? null) ? (int) $validated['limit']  : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        
        // ✅ hanya customer + aktif
        $domain = [
            ['customer_rank', '>', 0],
            ['active', '=', true],
        ];

        // ✅ search multi field
        if (!empty($search)) {
            $domain[] = '|';
            $domain[] = '|';
            $domain[] = ['name', 'ilike', $search];
            $domain[] = ['email', 'ilike', $search];
            $domain[] = ['phone', 'ilike', $search];
        }

        $total   = $this->odoo->searchCount('res.partner', $domain);
        $records = $this->odoo->searchRead(
            'res.partner',
            $domain,
            [
                    'id', 'name', 'ref', 'company_name','function',
                    'street', 'street2', 'city', 'zip',
                    'state_id', 'country_id', 'contact_address_complete',
                    'email', 'phone', 'mobile', 'website',
                    'vat', 'company_registry',
                    'currency_id',
                    'comment',
                    'is_company', 'supplier_rank', 'customer_rank', 'active',
                    'x_studio_tes', 'x_studio_id',
                    'create_date', 'write_date',
                ],
            $limit,
            $offset
        );

        $message = empty($records) ? "Data yang Anda cari tidak ditemukan" : "Success";

        return ApiResponse::paginate(
            new CustomersResourcesCollection($records, $total, $limit, $offset),
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
                    'x_studio_tes', 'x_studio_id',
                    'create_date', 'write_date',
                ],
            );

            if (empty($records)) {
                return ApiResponse::error('Courier Price List not found', [
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
