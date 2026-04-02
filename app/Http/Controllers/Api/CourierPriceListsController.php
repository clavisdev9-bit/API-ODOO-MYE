<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourierPriceListsValidationIndex;
use App\Http\Resources\CourierPriceListsResourcesCollection;
use App\Http\Resources\CourierPriceListsResources;
use App\Http\Requests\CourierPriceListsRequest;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class CourierPriceListsController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    

//     public function index(CourierPriceListsValidationIndex $request)
// {
//     $validated = $request->validated();

//     $search = $validated['search'] ?? null;
//     $limit  = is_numeric($validated['limit']  ?? null) ? (int) $validated['limit']  : 10;
//     $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

//     // Tampilkan semua partner
//     $domain = [];

//     if ($search) {
//         $domain[] = ['name', 'ilike', $search];
//     }

//     $total   = $this->odoo->searchCount('res.partner', $domain);
//     $records = $this->odoo->searchRead(
//         'res.partner',
//         $domain,
//         [
//             'id', 'name', 'ref', 'company_name',
//             'street', 'street2', 'city', 'zip',
//             'state_id', 'country_id', 'contact_address_complete',
//             'email', 'phone', 'mobile', 'website',
//             'is_company', 'supplier_rank', 'customer_rank', 'active',
//             'x_studio_tes', 'x_studio_id',
//             'create_date', 'write_date',
//         ],
//         $limit,
//         $offset
//     );

//     $message = empty($records) ? "Data yang Anda cari tidak ditemukan" : "Success";

//     return ApiResponse::paginate(
//         new CourierPriceListsResourcesCollection($records, $total, $limit, $offset),
//         $message
//     );
// }

   public function index(CourierPriceListsValidationIndex $request)
{
    $validated = $request->validated();

    $search = $validated['search'] ?? null;
    $limit  = is_numeric($validated['limit']  ?? null) ? (int) $validated['limit']  : 10;
    $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

    // ✅ Domain kosong - tampilkan semua
    $domain = [];

    if ($search) {
        $domain[] = ['name', 'ilike', $search];
    }

    $total   = $this->odoo->searchCount('res.partner', $domain);
    $records = $this->odoo->searchRead(
        'res.partner',
        $domain,
        [
            'id', 'name', 'ref', 'company_name',
            'street', 'street2', 'city', 'zip',
            'state_id', 'country_id', 'contact_address_complete',
            'email', 'phone', 'mobile', 'website',
            'is_company', 'supplier_rank', 'customer_rank', 'active',
            'x_studio_tes', 'x_studio_id',
            'create_date', 'write_date',
        ],
        $limit,
        $offset
    );

    $message = empty($records) ? "Data yang Anda cari tidak ditemukan" : "Success";

    return ApiResponse::paginate(
        new CourierPriceListsResourcesCollection($records, $total, $limit, $offset),
        $message
    );
}

    // GET /api/odoo/courier-price-lists/{id}
    public function show(int $id)
    {
        $records = $this->odoo->read(
            'res.partner',
            [$id],
            [
                'id', 'name', 'ref', 'company_name',
                'street', 'street2', 'city', 'zip',
                'state_id', 'country_id', 'contact_address_complete',
                'email', 'phone', 'mobile', 'website',
                'is_company', 'supplier_rank', 'customer_rank', 'active',
                'x_studio_tes', 'x_studio_id',
                'create_date', 'write_date',
            ]
        );

        if (empty($records)) {
            return ApiResponse::error('Courier Price List not found', [
                'id' => ['Data with that ID is not available']
            ], 404);
        }

        return ApiResponse::success(
            new CourierPriceListsResources($records[0]),
            'Success, take the detailed Courier Price List',
            200
        );
    }

    // POST /api/odoo/courier-price-lists
    public function store(CourierPriceListsRequest $request)
    {
        $data = $request->validated();

        try {
            $id = $this->odoo->create('res.partner', [
                'name'          => $data['name'],
                'ref'           => $data['ref']     ?? null,
                'email'         => $data['email']   ?? null,
                'phone'         => $data['phone']   ?? null,
                'mobile'        => $data['mobile']  ?? null,
                'street'        => $data['street']  ?? null,
                'street2'       => $data['street2'] ?? null,
                'city'          => $data['city']    ?? null,
                'zip'           => $data['zip']     ?? null,
                'customer_rank' => 1,
            ]);

            $records = $this->odoo->read(
                'res.partner',
                [$id],
                [
                    'id', 'name', 'ref', 'company_name',
                    'street', 'street2', 'city', 'zip',
                    'state_id', 'country_id', 'contact_address_complete',
                    'email', 'phone', 'mobile', 'website',
                    'is_company', 'supplier_rank', 'customer_rank', 'active',
                    'create_date', 'write_date',
                ]
            );

            return ApiResponse::success(
                new CourierPriceListsResources($records[0]),
                'Success Create New Courier Price List',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create Courier Price List', [
                'exception' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // PUT /api/odoo/courier-price-lists/{id}
    public function update(CourierPriceListsRequest $request, int $id)
    {
        $data = $request->validated();

        $existing = $this->odoo->read('res.partner', [$id], ['id', 'name']);

        if (empty($existing)) {
            return ApiResponse::error('Courier Price List with that ID was not found.', [
                'id' => ['Data not available.']
            ], 404);
        }

        try {
            $this->odoo->write('res.partner', [$id], $data);

            $updated = $this->odoo->read(
                'res.partner',
                [$id],
                [
                    'id', 'name', 'ref', 'company_name',
                    'street', 'street2', 'city', 'zip',
                    'state_id', 'country_id', 'contact_address_complete',
                    'email', 'phone', 'mobile', 'website',
                    'is_company', 'supplier_rank', 'customer_rank', 'active',
                    'create_date', 'write_date',
                ]
            );

            return ApiResponse::success(
                new CourierPriceListsResources($updated[0]),
                'Success Update Courier Price List',
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update Courier Price List', [
                'exception' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // DELETE /api/odoo/courier-price-lists/{id}
    public function destroy(int $id)
    {
        $existing = $this->odoo->read(
            'res.partner',
            [$id],
            [
                'id', 'name', 'ref', 'email',
                'phone', 'city', 'active',
            ]
        );

        if (empty($existing)) {
            return ApiResponse::error('Courier Price List with that ID was not found.', [
                'id' => ['Data not available.']
            ], 404);
        }

        try {
            $this->odoo->unlink('res.partner', [$id]);

            return ApiResponse::success(
                new CourierPriceListsResources($existing[0]),
                'Success Delete Courier Price List',
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete Courier Price List', [
                'exception' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}