<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBloodPressure extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_cin',

        'patient_id',
        'systolic',
        'diastolic',
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