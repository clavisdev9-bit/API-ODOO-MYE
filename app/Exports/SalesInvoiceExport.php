<?php 
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesInvoiceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = collect();
        foreach ($this->data as $header) {
            foreach ($header['lines'] as $line) {
                $rows->push([
                    'header' => $header,
                    'line'   => $line
                ]);
            }
        }
        return $rows;
    }

    // public function headings(): array
    // {
    //     return [
    //         'YEAR', 'CUSTOMER', 'No SO', 'No Invoice', 'Reference', 
    //         'BRAND', 'DESKRIPSI', 'Qty', 'Price', 'SI Amt Bef. Disc', 
    //         'Discount (%)', 'Total SI Discount', 'SI Amt Bef. Tax', 
    //         'Tax %', 'Total Tax', 'SI Amt Aft. Tax'
    //     ];
    // }

    // public function map($row): array
    // {
    //     $h = $row['header'];
    //     $l = $row['line'];

    //     // Logic perhitungan manual agar akurat seperti di gambar
    //     $qty = $l['quantity'] ?? 0;
    //     $priceUnit = $l['price_unit'] ?? 0;
    //     $amtBefDisc = $qty * $priceUnit;
    //     $discountPercent = $l['discount'] ?? 0;
    //     $totalDiscount = ($amtBefDisc * $discountPercent) / 100;
    //     $amtBefTax = $l['price_subtotal'] ?? ($amtBefDisc - $totalDiscount);
        
    //     // Ambil info pajak (asumsi 11% jika tidak ada)
    //     $taxPercent = !empty($l['tax_formatted']) ? $l['tax_formatted'][0][1] : '11%';
    //     $totalTax = ($l['price_total'] ?? 0) - $amtBefTax;

    //     return [
    //         date('Y', strtotime($h['invoice_date'])), // YEAR
    //         $h['invoice_partner_display_name'] ?? '-', // CUSTOMER
    //         $h['invoice_origin'] ?? '-',               // No SO
    //         $h['name'] ?? '-',                         // No Invoice
    //         $h['ref'] ?? '-',                          // Reference
    //         $l['product_category'][1] ?? '-',          // BRAND (Kategori)
    //         $l['name'] ?? '-',                         // DESKRIPSI
    //         $qty,                                      // Qty
    //         $priceUnit,                                // Price
    //         $amtBefDisc,                               // SI Amt Bef. Disc
    //         $discountPercent . '%',                    // Discount (%)
    //         $totalDiscount,                            // Total SI Discount
    //         $amtBefTax,                                // SI Amt Bef. Tax
    //         $taxPercent,                               // Tax %
    //         $totalTax,                                 // Total Tax
    //         $l['price_total'] ?? ($amtBefTax + $totalTax), // SI Amt Aft. Tax
    //     ];
    // }

    public function headings(): array
    {
        return [
            'DATE',        // Kolom baru
            'MONTH',       // Kolom baru (YY-MM)
            'YEAR', 
            'CUSTOMER', 
            'No SO', 
            'No Invoice', 
            'Reference', 
            'BRAND', 
            'DESKRIPSI', 
            'Qty', 
            'Price', 
            'SI Amt Bef. Disc', 
            'Discount (%)', 
            'Total SI Discount', 
            'SI Amt Bef. Tax', 
            'Tax %', 
            'Total Tax', 
            'SI Amt Aft. Tax'
        ];
    }

    public function map($row): array
    {
        $h = $row['header'];
        $l = $row['line'];

        // Logic Tanggal
        $timestamp = strtotime($h['invoice_date']);
        $dateFormatted = date('d/m/Y', $timestamp); // Format 11/03/2026
        $monthFormatted = date('y-m', $timestamp);   // Format 26-03 (YY-MM)
        $yearOnly = date('Y', $timestamp);           // Format 2026

        // Perhitungan (Sama seperti sebelumnya)
        $qty = $l['quantity'] ?? 0;
        $priceUnit = $l['price_unit'] ?? 0;
        $amtBefDisc = $qty * $priceUnit;
        $discountPercent = $l['discount'] ?? 0;
        $totalDiscount = ($amtBefDisc * $discountPercent) / 100;
        $amtBefTax = $l['price_subtotal'] ?? ($amtBefDisc - $totalDiscount);
        
        $taxPercent = !empty($l['tax_formatted']) ? $l['tax_formatted'][0][1] : '11%';
        $totalTax = ($l['price_total'] ?? 0) - $amtBefTax;

        return [
            $dateFormatted,    // DATE
            $monthFormatted,   // MONTH (YY-MM)
            $yearOnly,         // YEAR
            $h['invoice_partner_display_name'] ?? '-',
            $h['invoice_origin'] ?? '-',
            $h['name'] ?? '-',
            $h['ref'] ?? '-',
            $l['product_category'][1] ?? '-',
            $l['name'] ?? '-',
            $qty,
            $priceUnit,
            $amtBefDisc,
            $discountPercent . '%',
            $totalDiscount,
            $amtBefTax,
            $taxPercent,
            $totalTax,
            $l['price_total'] ?? ($amtBefTax + $totalTax),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Tebalkan header dan beri warna latar belakang (opsional)
            1    => ['font' => ['bold' => true]],
        ];
    }
}