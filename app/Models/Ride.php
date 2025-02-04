<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        "origin_address",
        "destination_address",
        "origin_latitude",
        "origin_longitude",
        "destination_latitude",
        "destination_longitude",
        "ride_time",
        "ride_distance",
        "fare_price",
        "ride_status",
    ];

    public function driver() {
        return $this->belongsTo(Driver::class);
    }

    public function hitchhiker() {
        return $this->belongsTo(Hitchhiker::class);
    }
}
