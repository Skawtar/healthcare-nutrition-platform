<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Added for BelongsTo relationships

class DocumentMedical extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'patient_id',
        'patient_cin',
        'medecin_id',
        'medical_record_id',
        'consultation_id', // NEW: Added consultation_id to fillable
        'date_creation',
        'fichier',
        'est_signe',
        'document_type',
    ];

    protected $casts = [
        'date_creation' => 'datetime',
        'est_signe' => 'boolean',
    ];

    /**
     * Get the patient that owns the document.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Get the doctor (medecin) that uploaded the document.
     */
    public function medecin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medecin_id', 'id');
    }

    /**
     * Get the medical record that the document belongs to.
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id', 'medical_record_id');
    }

    /**
     * Get the consultation that the document belongs to.
     * NEW: Relationship to Consultation
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'id');
    }

    /**
     * Sign the document.
     */
    public function signer()
    {
        $this->update(['est_signe' => true]);
    }
}
