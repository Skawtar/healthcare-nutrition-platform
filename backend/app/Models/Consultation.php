<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; // Added for HasMany relationships

class Consultation extends Model
{
    use HasFactory;

    protected $guarded = []; // Consider using $fillable for better security

   

    protected $casts = [
        'status' => 'string',
        'date_heure' => 'datetime',
    ];

    protected $fillable = [
        'patient_id',
        'medecin_id',
        'date_heure',
        'status',
        'motif',
        'notes'
    ];

    public static function getStatuses()
    {
        return [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected'
        ];
    }

    /**
     * Get the patient associated with the consultation.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Get the doctor (medecin) associated with the consultation.
     */
    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id', 'id');
    }

    /**
     * Get the medical documents associated with the consultation.
     */
    public function documentMedicals(): HasMany
    {
        // Assumes 'document_medicals' table has a 'consultation_id' column
        return $this->hasMany(DocumentMedical::class, 'consultation_id', 'id');
    }

    /**
     * Get the medical records associated with the consultation.
     */
    public function medicalRecords(): HasMany
    {
        // Assumes 'medical_records' table has a 'consultation_id' column
        return $this->hasMany(MedicalRecord::class, 'consultation_id', 'id');
    }
}
