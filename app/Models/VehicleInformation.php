<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'make',
        'model',
        'color',
        'vehicle_average',
        'license_plate_number',
        'license_number',
        'issue_date',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
