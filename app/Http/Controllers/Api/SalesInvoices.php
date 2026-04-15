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



// code lama tanpa optimasi query dan penambahan kategori produk
// public function AccountMoveLine(SalesInvoiceDetailRequestValidationIndex $request)
// {
//     $validated = $request->validated();

//     $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
//     $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;
//     $moveId = (int) $validated['move_id'];

//     // 1. Filter langsung di Odoo (Domain)
//     // Ini jauh lebih cepat karena Odoo tidak perlu mengirim data tax/note yang tidak perlu
//     $domain = [
//         ['move_id', '=', $moveId],
//         ['display_type', '=', 'product']
//     ];

//     // 2. Hitung total real dari Odoo
//     $total = $this->odoo->searchCount('account.move.line', $domain);

//     // 3. Ambil data dengan limit dan offset yang sesuai request
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
//         $limit,  // Gunakan limit dari request
//         $offset  // Gunakan offset dari request
//     );

//     $message = empty($records) 
//         ? 'Invoice details not found' 
//         : 'Success Get Invoice Detail';

//     return ApiResponse::paginate(
//         new SalesInvoiceDetailResourcesCollection(
//             $records, // Langsung kirim $records, tidak perlu collect()->slice() lagi
//             $total,
//             $limit,
//             $offset
//         ),
//         $message
//     );
// }

// code baru dengan optimasi query dan penambahan kategori produk
// public function AccountMoveLine(SalesInvoiceDetailRequestValidationIndex $request)
// {
//     $validated = $request->validated();

//     $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
//     $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;
//     $moveId = (int) $validated['move_id'];

//     $domain = [
//         ['move_id', '=', $moveId],
//         ['display_type', '=', 'product']
//     ];

//     $total = $this->odoo->searchCount('account.move.line', $domain);

//     $records = $this->odoo->searchRead(
//         'account.move.line',
//         $domain,
//         ['move_id', 
//         'name', 
//         'product_id', 
//         'quantity', 
//         'price_unit', 
//         'discount', 
//         'tax_ids', 
//         'price_subtotal', 
//         'price_total'],
//         $limit,
//         $offset
//     );

//     if (!empty($records)) {
//         // --- PROSES AMBIL CATEG_ID ---
//         // 1. Ambil semua product_id unik dari records
//         $productIds = collect($records)->pluck('product_id.0')->unique()->filter()->toArray();

//         // 2. Tarik categ_id dari model product.product
//         $products = $this->odoo->searchRead(
//             'product.product',
//             [['id', 'in', $productIds]],
//             ['id', 'categ_id']
//         );

//         // 3. Buat map [product_id => categ_id]
//         $productMap = collect($products)->keyBy('id');

//         // 4. Masukkan categ_id ke masing-masing record
//         $records = collect($records)->map(function ($item) use ($productMap) {
//             $prodId = $item['product_id'][0] ?? null;
//             // Tempelkan categ_id ke record detail
//             $item['product_category'] = $productMap->get($prodId)['categ_id'] ?? null;
//             return $item;
//         });
//     }

//     $message = empty($records) 
//         ? 'Invoice details not found' 
//         : 'Success Get Invoice Detail';

//     return ApiResponse::paginate(
//         new SalesInvoiceDetailResourcesCollection(
//             $records, 
//             $total,
//             $limit,
//             $offset
//         ),
//         $message
//     );
// }


// code baru dengan optimasi query dan penambahan kategori produk dan optimasi data pajak
public function AccountMoveLine(SalesInvoiceDetailRequestValidationIndex $request)
{
    $validated = $request->validated();

    $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
    $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;
    $moveId = (int) $validated['move_id'];

    $domain = [
        ['move_id', '=', $moveId],
        ['display_type', '=', 'product']
    ];

    $total = $this->odoo->searchCount('account.move.line', $domain);

    $records = $this->odoo->searchRead(
        'account.move.line',
        $domain,
        [
            'move_id', 'name', 'product_id', 'quantity', 'price_unit', 
            'discount', 'tax_ids', 'price_subtotal', 'price_total'
        ],
        $limit,
        $offset
    );

    if (!empty($records)) {
        // --- PROSES EAGER LOAD UNTUK DETAIL ---

        // 1. Ambil Kategori Produk
        $productIds = collect($records)->pluck('product_id.0')->unique()->filter()->toArray();
        $products = $this->odoo->searchRead('product.product', [['id', 'in', $productIds]], ['id', 'categ_id']);
        $productMap = collect($products)->keyBy('id');

        // 2. Ambil Nama Pajak (Ubah ID [234] jadi [[234, "Name"]])
        $taxIds = collect($records)->pluck('tax_ids')->flatten()->unique()->filter()->toArray();
        $taxes = !empty($taxIds) ? $this->odoo->searchRead('account.tax', [['id', 'in', $taxIds]], ['id', 'name']) : [];
        $taxMap = collect($taxes)->keyBy('id');

        // 3. Mapping data ke dalam records
        $records = collect($records)->map(function ($item) use ($productMap, $taxMap) {
            $prodId = $item['product_id'][0] ?? null;
            
            // Tempel Kategori
            $item['product_category'] = $productMap->get($prodId)['categ_id'] ?? null;
            
            // Format Pajak
            $item['tax_formatted'] = collect($item['tax_ids'] ?? [])->map(function($tId) use ($taxMap) {
                return [
                    $tId, 
                    $taxMap->get($tId)['name'] ?? 'Unknown Tax'
                ];
            })->toArray();
            
            return $item;
        });
    }

    $message = empty($records) 
        ? 'Invoice details not found' 
        : 'Success Get Invoice Detail';

    return ApiResponse::paginate(
        new SalesInvoiceDetailResourcesCollection(
            $records, 
            $total,
            $limit,
            $offset
        ),
        $message
    );
}




// kode lama tanpa optimasi query dan penambahan kategori produk
// public function CombinedInvoiceSales(InvoiceSalesRequestValidationIndex $request)
// {
//     $validated = $request->validated();
//     $limit  = (int) ($validated['limit'] ?? 10);
//     $offset = (int) ($validated['offset'] ?? 0);

//     $domain = [['move_type', '=', 'out_invoice']];

//     // 1. Hitung Total untuk Pagination
//     $total = $this->odoo->searchCount('account.move', $domain);

//     // 2. Ambil Data Header
//     $headers = $this->odoo->searchRead(
//         'account.move',
//         $domain,
//         [
//             'id', 'name', 'invoice_partner_display_name', 'partner_id', 'invoice_date',
//             'invoice_origin', 'ref', 'company_currency_id', 'currency_id',
//             'amount_untaxed', 'amount_untaxed_signed', 'amount_tax', 'amount_total',
//             'amount_total_in_currency_signed', 'payment_state', 'state', 'move_type', 'invoice_line_ids'
//         ],
//         $limit,
//         $offset
//     );

//     if (empty($headers)) {
//         return ApiResponse::paginate(
//             new salesInvoiceResourcesCollection([], 0, $limit, $offset),
//             "Data not found"
//         );
//     }

//     // 3. Eager Load Lines (Ambil Detail sekaligus)
//     $moveIds = collect($headers)->pluck('id')->toArray();
//     $allLines = $this->odoo->searchRead(
//         'account.move.line',
//         [
//             ['move_id', 'in', $moveIds],
//             ['display_type', '=', 'product']
//         ],
//         [
//             'move_id', 'name', 'product_id', 'quantity', 'price_unit', 
//             'discount', 'tax_ids', 'price_subtotal', 'price_total'
//         ]
//     );

//     // Grouping lines berdasarkan move_id
//     $groupedLines = collect($allLines)->groupBy(function ($item) {
//         return is_array($item['move_id']) ? $item['move_id'][0] : $item['move_id'];
//     });

//     // 4. Merge lines ke dalam record headers
//     $records = collect($headers)->map(function ($header) use ($groupedLines) {
//         $header['lines'] = $groupedLines->get($header['id']) ?? [];
//         return $header;
//     });

//     // 5. Return menggunakan gaya ApiResponse kamu
//     return ApiResponse::paginate(
//         new salesInvoiceResourcesCollection($records, $total, $limit, $offset),
//         "Success Get Sales Invoice"
//     );
// }

//3
// public function CombinedInvoiceSales(InvoiceSalesRequestValidationIndex $request)
// {
//     $validated = $request->validated();
//     $limit  = (int) ($validated['limit'] ?? 10);
//     $offset = (int) ($validated['offset'] ?? 0);

//     $domain = [['move_type', '=', 'out_invoice']];

//     // 1. Hitung Total untuk Pagination
//     $total = $this->odoo->searchCount('account.move', $domain);

//     // 2. Ambil Data Header
//     $headers = $this->odoo->searchRead(
//         'account.move',
//         $domain,
//         [
//             'id', 'name', 'invoice_partner_display_name', 'partner_id', 'invoice_date',
//             'invoice_origin', 'ref', 'company_currency_id', 'currency_id',
//             'amount_untaxed', 'amount_untaxed_signed', 'amount_tax', 'amount_total',
//             'amount_total_in_currency_signed', 'payment_state', 'state', 'move_type', 'invoice_line_ids'
//         ],
//         $limit,
//         $offset
//     );

//     if (empty($headers)) {
//         return ApiResponse::paginate(
//             new salesInvoiceResourcesCollection([], 0, $limit, $offset),
//             "Data not found"
//         );
//     }

//     // 3. Eager Load Lines (Ambil Detail sekaligus)
//     $moveIds = collect($headers)->pluck('id')->toArray();
//     $allLines = $this->odoo->searchRead(
//         'account.move.line',
//         [
//             ['move_id', 'in', $moveIds],
//             ['display_type', '=', 'product']
//         ],
//         [
//             'move_id', 'name', 'product_id', 'quantity', 'price_unit', 
//             'discount', 'tax_ids', 'price_subtotal', 'price_total'
//         ]
//     );

//     // --- TAMBAHAN: Ambil Kategori Produk ---
//     $productIds = collect($allLines)->pluck('product_id.0')->unique()->filter()->toArray();
//     $products = $this->odoo->searchRead(
//         'product.product',
//         [['id', 'in', $productIds]],
//         ['id', 'categ_id']
//     );
//     $productMap = collect($products)->keyBy('id');

//     // Grouping lines berdasarkan move_id + Sisipkan categ_id
//     $groupedLines = collect($allLines)->map(function ($line) use ($productMap) {
//         $pId = $line['product_id'][0] ?? null;
//         $line['product_category'] = $productMap->get($pId)['categ_id'] ?? null;
//         return $line;
//     })->groupBy(function ($item) {
//         return is_array($item['move_id']) ? $item['move_id'][0] : $item['move_id'];
//     });

//     // 4. Merge lines ke dalam record headers
//     $records = collect($headers)->map(function ($header) use ($groupedLines) {
//         $header['lines'] = $groupedLines->get($header['id']) ?? [];
//         return $header;
//     });

//     // 5. Return menggunakan gaya ApiResponse lama kamu
//     return ApiResponse::paginate(
//         new salesInvoiceResourcesCollection($records, $total, $limit, $offset),
//         "Success Get Sales Invoice"
//     );
// }


// kode baru dengan optimasi query dan penambahan kategori produk dan optimasi data pajak
// public function CombinedInvoiceSales(InvoiceSalesRequestValidationIndex $request)
// {
//     $validated = $request->validated();
//     $limit  = (int) ($validated['limit'] ?? 10);
//     $offset = (int) ($validated['offset'] ?? 0);

//     $domain = [['move_type', '=', 'out_invoice']];

//     // 1. Hitung Total untuk Pagination
//     $total = $this->odoo->searchCount('account.move', $domain);

//     // 2. Ambil Data Header
//     $headers = $this->odoo->searchRead(
//         'account.move',
//         $domain,
//         [
//             'id', 'name', 'invoice_partner_display_name', 'partner_id', 'invoice_date',
//             'invoice_origin', 'ref', 'company_currency_id', 'currency_id',
//             'amount_untaxed', 'amount_untaxed_signed', 'amount_tax', 'amount_total',
//             'amount_total_in_currency_signed', 'payment_state', 'state', 'move_type', 'invoice_line_ids'
//         ],
//         $limit,
//         $offset
//     );

//     if (empty($headers)) {
//         return ApiResponse::paginate(
//             new salesInvoiceResourcesCollection([], 0, $limit, $offset),
//             "Data not found"
//         );
//     }

//     // 3. Ambil Detail Lines
//     $moveIds = collect($headers)->pluck('id')->toArray();
//     $allLines = $this->odoo->searchRead(
//         'account.move.line',
//         [
//             ['move_id', 'in', $moveIds],
//             ['display_type', '=', 'product']
//         ],
//         [
//             'move_id', 'name', 'product_id', 'quantity', 'price_unit', 
//             'discount', 'tax_ids', 'price_subtotal', 'price_total'
//         ]
//     );

//     // --- PROSES EAGER LOAD: KATEGORI & PAJAK ---

//     // A. Ambil Data Kategori Produk
//     $productIds = collect($allLines)->pluck('product_id.0')->unique()->filter()->toArray();
//     $products = $this->odoo->searchRead('product.product', [['id', 'in', $productIds]], ['id', 'categ_id']);
//     $productMap = collect($products)->keyBy('id');

//     // B. Ambil Data Nama Pajak (Karena tax_ids cuma balikin [234])
//     $taxIds = collect($allLines)->pluck('tax_ids')->flatten()->unique()->filter()->toArray();
//     $taxes = !empty($taxIds) ? $this->odoo->searchRead('account.tax', [['id', 'in', $taxIds]], ['id', 'name']) : [];
//     $taxMap = collect($taxes)->keyBy('id');

//     // C. Gabungkan Semuanya ke dalam Lines
//     $linesMapped = collect($allLines)->map(function ($line) use ($productMap, $taxMap) {
//         // Tempel Kategori
//         $pId = $line['product_id'][0] ?? null;
//         $line['product_category'] = $productMap->get($pId)['categ_id'] ?? null;
        
//         // Tempel Data Pajak (Ubah [234] jadi [[234, "PPN 11%"]])
//         $line['tax_formatted'] = collect($line['tax_ids'] ?? [])->map(function($tId) use ($taxMap) {
//             return [
//                 $tId, 
//                 $taxMap->get($tId)['name'] ?? 'Pajak Tidak Diketahui'
//             ];
//         })->toArray();
        
//         return $line;
//     });

//     // 4. Grouping lines berdasarkan move_id
//     $groupedLines = $linesMapped->groupBy(function ($item) {
//         return is_array($item['move_id']) ? $item['move_id'][0] : $item['move_id'];
//     });

//     // 5. Merge lines ke dalam record headers
//     $records = collect($headers)->map(function ($header) use ($groupedLines) {
//         $header['lines'] = $groupedLines->get($header['id']) ?? [];
//         return $header;
//     });

//     // 6. Return menggunakan gaya ApiResponse
//     return ApiResponse::paginate(
//         new salesInvoiceResourcesCollection($records, $total, $limit, $offset),
//         "Success Get Sales Invoice"
//     );
// }



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

    // 3. Ambil Detail Lines
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

    // --- PROSES EAGER LOAD: KATEGORI & PAJAK ---

    // A. Ambil Data Kategori Produk
    // Tambahan: .values() dan (int) casting untuk mencegah unhashable type error
    $productIds = collect($allLines)
        ->pluck('product_id.0')
        ->filter()
        ->unique()
        ->map(fn($id) => (int)$id)
        ->values() 
        ->toArray();

    $productMap = collect();
    if (!empty($productIds)) {
        $products = $this->odoo->searchRead('product.product', [['id', 'in', $productIds]], ['id', 'categ_id']);
        $productMap = collect($products)->keyBy('id');
    }

    // B. Ambil Data Nama Pajak
    // Tambahan: .values() dan (int) casting untuk mencegah unhashable type error
    $taxIds = collect($allLines)
        ->pluck('tax_ids')
        ->flatten()
        ->filter()
        ->unique()
        ->map(fn($id) => (int)$id)
        ->values()
        ->toArray();

    $taxMap = collect();
    if (!empty($taxIds)) {
        $taxes = $this->odoo->searchRead('account.tax', [['id', 'in', $taxIds]], ['id', 'name']);
        $taxMap = collect($taxes)->keyBy('id');
    }

    // C. Gabungkan Semuanya ke dalam Lines
    $linesMapped = collect($allLines)->map(function ($line) use ($productMap, $taxMap) {
        // Tempel Kategori
        $pId = $line['product_id'][0] ?? null;
        $line['product_category'] = $productMap->get($pId)['categ_id'] ?? null;
        
        // Tempel Data Pajak
        $line['tax_formatted'] = collect($line['tax_ids'] ?? [])->map(function($tId) use ($taxMap) {
            return [
                (int) $tId, 
                $taxMap->get($tId)['name'] ?? 'Pajak Tidak Diketahui'
            ];
        })->toArray();
        
        return $line;
    });

    // 4. Grouping lines berdasarkan move_id
    $groupedLines = $linesMapped->groupBy(function ($item) {
        return is_array($item['move_id']) ? $item['move_id'][0] : $item['move_id'];
    });

    // 5. Merge lines ke dalam record headers
    $records = collect($headers)->map(function ($header) use ($groupedLines) {
        $header['lines'] = $groupedLines->get($header['id']) ?? [];
        return $header;
    });

    // 6. Return menggunakan gaya ApiResponse
    return ApiResponse::paginate(
        new salesInvoiceResourcesCollection($records, $total, $limit, $offset),
        "Success Get Sales Invoice"
    );
}
}