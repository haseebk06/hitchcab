<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'patient_code',
        'doctor_id',
        'name',
        'guardian_name',
        'gender',
        'date_of_birth',
        'age',
        'cnic',
        'phone',
        'alternate_phone',
        'email',
        'address',
        'city',
        'blood_group',
        'marital_status',
        'occupation',
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_phone',
        'allergies',
        'medical_history',
        'current_medications',
        'chronic_diseases',
        'insurance_provider',
        'insurance_policy_number',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'age' => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
