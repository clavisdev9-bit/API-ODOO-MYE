<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatutorySalesReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $d = $this->resource;

        return [
            // PURCHASE
            'cust_po_no'        => $d['cust_po_no'],
            'new_cust_po_no'    => $d['new_cust_po_no'],
            'cust_po_date'      => $d['cust_po_date'],
            'po_no'             => $d['po_no'],
            'po_qty'            => $d['po_qty'],
            'req_delivery_date' => $d['req_delivery_date'],

            // CUSTOMER
            'customer' => $d['customer'],
            'ship_to'  => $d['ship_to'],

            // GR
            'gr_no'   => $d['gr_no'],
            'gr_date' => $d['gr_date'],
            'gr_qty'  => $d['gr_qty'],

            // PI
            'pi_ref_no'         => $d['pi_ref_no'],
            'pi_date'           => $d['pi_date'],
            'pi_qty'            => $d['pi_qty'],
            'gr_pi_amt_bef_tax' => $d['gr_pi_amt_bef_tax'],
            'gr_pi_amt_tax'     => $d['gr_pi_amt_tax'],
            'gr_pi_amt_afr_tax' => $d['gr_pi_amt_afr_tax'],

            // SO
            'so_no'   => $d['so_no'],
            'so_date' => $d['so_date'],
            'so_qty'  => $d['so_qty'],

            // DO
            'do_no'   => $d['do_no'],
            'do_date' => $d['do_date'],
            'do_qty'  => $d['do_qty'],

            // POD
            'pod_no'   => $d['pod_no'],
            'pod_date' => $d['pod_date'],
            'pod_qty'  => $d['pod_qty'],

            // SI
            'si_no'          => $d['si_no'],
            'si_date'        => $d['si_date'],
            'si_qty'         => $d['si_qty'],
            'si_amt_bef_tax' => $d['si_amt_bef_tax'],

            // PR
            'pr_no'  => $d['pr_no'],
            'pr_date'=> $d['pr_date'],
            'pr_amt' => $d['pr_amt'],

            'message' => $d['message'],
        ];
    }
}