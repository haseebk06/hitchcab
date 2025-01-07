<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        "rating",
        "driver_id",
        "hitchhiker_id",
    ];

    public function driver() {
        return $this->belongsTo(Driver::class);
    }

    public function hitchhiker() {
        return $this->belongsTo(Hitchhiker::class);
    }
}
