<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DossierMedical extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_cin',
        'poids',
        'taille',
        'groupe_sanguin',
        'allergies',
        'antecedents',
        'traitements',
        'derniere_consultation'
    ];

    protected $casts = [
        'allergies' => 'array',
        'antecedents' => 'array',
        'traitements' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function calculerIMC()
    {
        if ($this->poids && $this->taille) {
            return $this->poids / (($this->taille / 100) ** 2);
        }
        return null;
    }
}