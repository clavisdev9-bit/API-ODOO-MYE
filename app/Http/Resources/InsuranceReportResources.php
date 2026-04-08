<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class InsuranceReportResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        // =========================
        // PAYMENT DATE (AMAN)
        // =========================
        $paymentDate = null;

        $widget = $data['invoice_payments_widget'] ?? null;

        if (!empty($widget)) {

            // kalau string → decode
            if (is_string($widget)) {
                $widget = json_decode($widget, true);
            }

            // kalau array → ambil
            if (is_array($widget)) {
                $paymentDate = $widget['content'][0]['date'] ?? null;
            }
        }

        // =========================
        // PAYMENT DAYS
        // =========================
        $paymentDays = null;

        if (!empty($data['invoice_date']) && $paymentDate) {
            $invoiceDate = Carbon::parse($data['invoice_date']);
            $payDate     = Carbon::parse($paymentDate);

            $paymentDays = $invoiceDate->diffInDays($payDate, false);
        }

        return [
            'id'               => $data['id'] ?? null,
            'customer'         => $data['partner_id'][1] ?? null,
            'invoice_no'       => $data['name'] ?? null,
            'invoice_date'     => $data['invoice_date'] ?? null,
            'invoice_due_date' => $data['invoice_date_due'] ?? null,
            'terms'            => $data['invoice_payment_term_id'][1] ?? null,
            'amount'           => $data['amount_total'] ?? 0,
            'payment_date'     => $paymentDate,
            'payment_days'     => $paymentDays,
        ];
    }
}