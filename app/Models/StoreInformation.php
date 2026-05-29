<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'storeName',
        'address',
        'phone',
        'email',
        'taxId',
        'logo',
        'currency',
        'sst',
        'wh_tax_percentage',
        'sst_withholding_tax_percentage',
    ];
}
