<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class RegimeAlimentaire extends Model{
     use HasFactory;
     
     protected $casts = [
        'restrictions' => 'array',
        'date_prescription' => 'date',
        'date_expiration' => 'date',
    ];

    protected $fillable = [
        'patient_id',
        'medecin_id',
        'date_prescription',
        'date_expiration',
        'calories_journalieres',
        'restrictions',
        'recommandations',
        'statut_code'
    ];


    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id', 'id');
    }
public function regimeStatut()
{
    return $this->belongsTo(RegimeStatut::class, 'statut_code', 'code');
}

}
