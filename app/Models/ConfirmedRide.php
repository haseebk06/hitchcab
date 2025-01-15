<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfirmedRide extends Model
{
    use HasFactory;

    protected $fillable = [
        "origin_address",
        "destination_address",
        "ride_status",
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function hitchhiker()
    {
        return $this->belongsTo(Hitchhiker::class);
    }
}
