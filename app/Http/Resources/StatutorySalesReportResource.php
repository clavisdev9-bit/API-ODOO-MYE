<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatutorySalesReportResource extends JsonResource
{
    
    public function toArray($request)
{
    return [
        // PURCHASE
        'cust_po_no'        => data_get($this, 'cust_po_no'),
        'new_cust_po_no'    => data_get($this, 'cust_po_no'),
        'cust_po_date'      => data_get($this, 'cust_po_date'),
        'po_no'             => data_get($this, 'po_no'),
        'po_qty'            => data_get($this, 'po_qty'),
        'req_delivery_date' => data_get($this, 'req_delivery_date'),

        // CUSTOMER
        'customer' => data_get($this, 'customer'),
        'ship_to'  => data_get($this, 'ship_to'),

        // GR
        'gr_no'   => data_get($this, 'gr_no'),
        'gr_date' => data_get($this, 'gr_date'),
        'gr_qty'  => data_get($this, 'gr_qty'),

        // PI
        'pi_ref_no'         => data_get($this, 'pi_ref_no'),
        'pi_date'           => data_get($this, 'pi_date'),
        'pi_qty'            => data_get($this, 'pi_qty'),
        'gr_pi_amt_bef_tax' => data_get($this, 'gr_pi_amt_bef_tax'),
        'gr_pi_amt_tax'     => data_get($this, 'gr_pi_amt_tax'),
        'gr_pi_amt_afr_tax' => data_get($this, 'gr_pi_amt_afr_tax'),

        // SO
        'so_no'   => data_get($this, 'so_no'),
        'so_date' => data_get($this, 'so_date'),
        'so_qty'  => data_get($this, 'so_qty'),

        // DO
        'do_no'   => data_get($this, 'do_no'),
        'do_date' => data_get($this, 'do_date'),
        'do_qty'  => data_get($this, 'do_qty'),

        // POD
        'pod_no'   => data_get($this, 'pod_no'),
        'pod_date' => data_get($this, 'pod_date'),
        'pod_qty'  => data_get($this, 'pod_qty'),

        // SI
        'si_no'          => data_get($this, 'si_no'),
        'si_date'        => data_get($this, 'si_date'),
        'si_qty'         => data_get($this, 'si_qty'),
        'si_amt_bef_tax' => data_get($this, 'si_amt_bef_tax'),

        // PR
        'pr_no'   => data_get($this, 'pr_no'),
        'pr_date' => data_get($this, 'pr_date'),
        'pr_amt'  => data_get($this, 'pr_amt'),

        'message' => data_get($this, 'message'),
    ];
}
}