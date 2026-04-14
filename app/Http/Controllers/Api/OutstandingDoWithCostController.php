<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutstandingDoWithCostRequestValidationIndex;
use App\Http\Resources\OutstandingDoWithCostResourcesCollection;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class OutstandingDoWithCostController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(OutstandingDoWithCostRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;
        $search = $validated['search'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | DOMAIN
        |--------------------------------------------------------------------------
        */
        $domain = [];

        if (!empty($search)) {
            $domain[] = '|';
            $domain[] = '|';

            $domain[] = ['name', 'ilike', $search];
            $domain[] = ['partner_ref', 'ilike', $search];
            $domain[] = ['partner_id', 'ilike', $search];
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL COUNT
        |--------------------------------------------------------------------------
        */
        $total = $this->odoo->searchCount(
            'purchase.order',
            $domain
        );

        /*
        |--------------------------------------------------------------------------
        | FETCH DATA
        |--------------------------------------------------------------------------
        */
        $records = $this->odoo->searchRead(
            'purchase.order',
            $domain,
            [
                'name',
                'partner_id',
                'partner_ref',
                'date_order',
                'date_planned',
                'effective_date',
                'amount_untaxed',
                'amount_tax',
                'amount_total',
                'dest_address_id',
                'picking_ids',
                'incoterm_id',
                'x_studio_project_no',
                'x_studio_end_user',
                'x_studio_buyer',
            ],
            $limit,
            $offset
        );

        $message = empty($records)
            ? 'Data Outstanding DO With Cost tidak ditemukan'
            : 'Success Outstanding DO With Cost';

        return ApiResponse::paginate(
            new OutstandingDoWithCostResourcesCollection(
                $records,
                $total,
                $limit,
                $offset
            ),
            $message
        );
    }
}