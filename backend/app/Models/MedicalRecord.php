<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Patient;
use App\Models\User;


class MedicalRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'medical_records';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'medical_record_id'; 
   
    protected $fillable = [
        'patient_cin',
        'patient_id',
        'medecin_id',
        'consultation_id',
        'record_start_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'record_start_date' => 'date',
    ];

  
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medecin_id', 'id');
    }

    /**
     * A medical record belongs to a consultation.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'id'); // Assumes Consultation model uses 'id' as PK
    }

    /**
     * A medical record can have many medical documents.
     */
    public function DocumentMedical(): HasMany
    {
        return $this->hasMany(DocumentMedical::class, 'medical_record_id', 'medical_record_id');
    }
}