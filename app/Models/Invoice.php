<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'customer_id',
        'store_information_id',
        'po_number',
        'lot_number',
        'vessel',
        'invoice_type',
        'size_description',
        'invoice_details',
        'weight',
        'rate',
        'gross_amount',
        'sst_percentage',
        'sst_amount',
        'total_amount',
        'wh_tax_percentage',
        'wh_tax_amount',
        'net_amount',
        'sst_withholding_tax_percentage',
        'sst_withholding_tax_amount',
        'net_sst_amount_sbr',
        'received',
        'chq_number',
        'cheque_received_date',
        'invoice_status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function storeInformation()
    {
        return $this->belongsTo(StoreInformation::class);
    }
}
