<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SalesInvoiceHeaderRequestValidationIndex;
use App\Http\Resources\SalesInvoiceHeaderResourcesCollection;
use App\Http\Resources\SalesInvoiceHeaderResources;
use App\Http\Requests\SalesInvoiceDetailRequestValidationIndex;
use App\Http\Resources\SalesInvoiceDetailResourcesCollection;
use App\Http\Resources\salesInvoiceDetailResources;
use App\Http\Requests\InvoiceSalesRequestValidationIndex;
use App\Http\Resources\salesInvoiceResourcesCollection;
use App\Helpers\ApiResponse;
use App\Services\OdooService;

class SalesInvoices extends Controller
{
   

     public function __construct(protected OdooService $odoo) {}
    //  code header
    public function AccountMove(SalesInvoiceHeaderRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        $domain = [
            ['move_type', '=', 'out_invoice']
        ];

        $total = $this->odoo->searchCount('account.move', $domain);

        $records = $this->odoo->searchRead(
            'account.move',
            $domain,
            [
                'create_date',
                'partner_id',
                'invoice_origin',
                'name',
                'ref',
                'company_currency_id',
                'amount_untaxed_signed',
                'amount_total_in_currency_signed',
                'amount_tax',
                'payment_state',
                'state',
                'move_type',
            ],
            $limit,
            $offset
        );

        $message = empty($records)
            ? 'The data you are looking for was not found'
            : 'Success Get Invoice Header';

        return ApiResponse::paginate(
            new SalesInvoiceHeaderResourcesCollection(
                $records,
                $total,
                $limit,
                $offset
            ),
            $message
        );
    }




public function AccountMoveLine(SalesInvoiceDetailRequestValidationIndex $request)
{
    $validated = $request->validated();

    $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
    $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;
    $moveId = (int) $validated['move_id'];

    // 1. Filter langsung di Odoo (Domain)
    // Ini jauh lebih cepat karena Odoo tidak perlu mengirim data tax/note yang tidak perlu
    $domain = [
        ['move_id', '=', $moveId],
        ['display_type', '=', 'product']
    ];

    // 2. Hitung total real dari Odoo
    $total = $this->odoo->searchCount('account.move.line', $domain);

    // 3. Ambil data dengan limit dan offset yang sesuai request
    $records = $this->odoo->searchRead(
        'account.move.line',
        $domain,
        [
            'move_id',
            'name',
            'display_type',
            'product_id',
            'quantity',
            'price_unit',
            'discount',
            'tax_ids',
            'price_subtotal',
            'price_total'
        ],
        $limit,  // Gunakan limit dari request
        $offset  // Gunakan offset dari request
    );

    $message = empty($records) 
        ? 'Invoice details not found' 
        : 'Success Get Invoice Detail';

    return ApiResponse::paginate(
        new SalesInvoiceDetailResourcesCollection(
            $records, // Langsung kirim $records, tidak perlu collect()->slice() lagi
            $total,
            $limit,
            $offset
        ),
        $message
    );
}





public function CombinedInvoiceSales(InvoiceSalesRequestValidationIndex $request)
{
    $validated = $request->validated();
    $limit  = (int) ($validated['limit'] ?? 10);
    $offset = (int) ($validated['offset'] ?? 0);

    $domain = [['move_type', '=', 'out_invoice']];

    // 1. Hitung Total untuk Pagination
    $total = $this->odoo->searchCount('account.move', $domain);

    // 2. Ambil Data Header
    $headers = $this->odoo->searchRead(
        'account.move',
        $domain,
        [
            'id', 'name', 'invoice_partner_display_name', 'partner_id', 'invoice_date',
            'invoice_origin', 'ref', 'company_currency_id', 'currency_id',
            'amount_untaxed', 'amount_untaxed_signed', 'amount_tax', 'amount_total',
            'amount_total_in_currency_signed', 'payment_state', 'state', 'move_type', 'invoice_line_ids'
        ],
        $limit,
        $offset
    );

    if (empty($headers)) {
        return ApiResponse::paginate(
            new salesInvoiceResourcesCollection([], 0, $limit, $offset),
            "Data not found"
        );
    }

    // 3. Eager Load Lines (Ambil Detail sekaligus)
    $moveIds = collect($headers)->pluck('id')->toArray();
    $allLines = $this->odoo->searchRead(
        'account.move.line',
        [
            ['move_id', 'in', $moveIds],
            ['display_type', '=', 'product']
        ],
        [
            'move_id', 'name', 'product_id', 'quantity', 'price_unit', 
            'discount', 'tax_ids', 'price_subtotal', 'price_total'
        ]
    );

    // Grouping lines berdasarkan move_id
    $groupedLines = collect($allLines)->groupBy(function ($item) {
        return is_array($item['move_id']) ? $item['move_id'][0] : $item['move_id'];
    });

    // 4. Merge lines ke dalam record headers
    $records = collect($headers)->map(function ($header) use ($groupedLines) {
        $header['lines'] = $groupedLines->get($header['id']) ?? [];
        return $header;
    });

    // 5. Return menggunakan gaya ApiResponse kamu
    return ApiResponse::paginate(
        new salesInvoiceResourcesCollection($records, $total, $limit, $offset),
        "Success Get Sales Invoice"
    );
}




}