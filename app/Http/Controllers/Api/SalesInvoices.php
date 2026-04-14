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
            ? 'Data yang Anda cari tidak ditemukan'
            : 'Success Outstanding Invoice Header';

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


//     public function AccountMoveLine(SalesInvoiceDetailRequestValidationIndex $request)
// {
//     $validated = $request->validated();

//     $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
//     $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;
//     $moveId = $validated['move_id'];
  

//     // $domain = [
//     //     ['move_id', '=', $moveId]
//     // ];
//     $domain = [
//     ['move_id', '=', (int) $moveId] // Paksa menjadi integer
// ];

//     $records = $this->odoo->searchRead(
//         'account.move.line',
//         $domain,
//         [
//             'move_id',
//             'name',
//             'display_type',
//             'product_id',
//             'quantity',
//             'price_unit',
//             'discount',
//             'tax_ids',
//             'price_subtotal',
//             'price_total'
//         ],
//         100,
//         0
//     );

    

//     // FILTER PRODUCT ONLY DI LARAVEL
//     $filtered = collect($records)
//         ->where('display_type', 'product')
//         ->values();

//     $total = $filtered->count();

//     $paginated = $filtered
//         ->slice($offset, $limit)
//         ->values();

//     return ApiResponse::paginate(
//         new SalesInvoiceDetailResourcesCollection(
//             $paginated,
//             $total,
//             $limit,
//             $offset
//         ),
//         $total ? 'Success Outstanding Invoice Detail'
//                : 'Detail invoice tidak ditemukan'
//     );
// }


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
        ? 'Detail invoice tidak ditemukan' 
        : 'Success Outstanding Invoice Detail';

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


     public function ResultInvoiceSales()
    {
        // Data dummy atau data dari database
        $data = [
            'status'  => 'success',
            'message' => 'Data Sales Invoices From Result Invoice Sales',
            'data'    => [
                [
                    'id' => 1,
                    'invoice_number' => 'INV-2023-001',
                    'amount' => 500000,
                    'customer' => 'Budi Santoso'
                ],
                [
                    'id' => 2,
                    'invoice_number' => 'INV-2023-002',
                    'amount' => 1250000,
                    'customer' => 'Siti Aminah'
                ]
            ]
        ];

        // Mengembalikan response JSON dengan HTTP Status Code 200 (OK)
        return response()->json($data, 200);
    }

}