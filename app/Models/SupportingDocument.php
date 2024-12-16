<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_number',
        'front',
        'page_one',
        'page_two',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
