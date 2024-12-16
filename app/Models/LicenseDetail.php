<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_country',
        'first_name',
        'last_name',
        'license_number',
        'issue_date',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
