<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'doctor_code',
        'name',
        'father_name',
        'gender',
        'date_of_birth',
        'cnic',
        'phone',
        'email',
        'address',
        'department',
        'specialization',
        'qualification',
        'license_number',
        'experience_years',
        'consultation_fee',
        'available_from',
        'available_to',
        'available_days',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'available_days' => 'array',
            'experience_years' => 'integer',
            'consultation_fee' => 'decimal:2',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
