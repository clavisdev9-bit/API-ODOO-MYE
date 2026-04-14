<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutstandingDoRequestValidationIndex;
use App\Http\Resources\OutstandingDoResourcesCollection;
use App\Http\Resources\OutstandingDoResources;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class OutstandingDoController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    public function index(OutstandingDoRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $limit  = $validated['limit'] ?? 10;
        $offset = $validated['offset'] ?? 0;
        $search = $validated['search'] ?? null;

        $domain = [
            ['picking_type_id.code', '=', 'outgoing'],
            ['state', 'in', ['assigned', 'confirmed']]
        ];

        if (!empty($search)) {
            $domain[] = '|';
            $domain[] = ['name', 'ilike', $search];
            $domain[] = ['origin', 'ilike', $search];
        }

        $total = $this->odoo->searchCount(
            'stock.picking',
            $domain
        );

        $records = $this->odoo->searchRead(
                'stock.picking',
                $domain,
                [
                    'id',
                    'name',
                    'origin',
                    'partner_id',
                    'location_id',
                    'location_dest_id',
                    'scheduled_date',
                    'date_done',
                    'create_date',
                    'write_date',
                    'state',
                    'priority',
                    'note',
                    'owner_id',
                    'company_id',
                    'backorder_id',
                    'picking_type_id',
                ],
                $limit,
                $offset
            );

        $message = empty($records)
            ? 'Data Outstanding DO tidak ditemukan'
            : 'Success Outstanding DO';

        return ApiResponse::paginate(
            new OutstandingDoResourcesCollection(
                $records,
                $total,
                $limit,
                $offset
            ),
            $message
        );
    }
}