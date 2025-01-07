<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hitchhiker extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'address',
        'profile_image_url',
    ];

    public function rides() {
        return $this->hasMany(Ride::class);
    }

    public function ratings() {
        return $this->hasMany(Rating::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
