<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBloodSugar extends Model
{
    use HasFactory;

    protected $table = 'patient_blood_sugars';

    protected $fillable = [
        'patient_cin',
        'patient_id',
        'value',
        'measurement_type',
        'measurement_at',
        'notes',
    ];

    protected $casts = [
        'measurement_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }
}