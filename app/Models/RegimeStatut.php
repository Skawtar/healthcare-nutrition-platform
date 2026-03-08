<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegimeStatut extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'color'
    ];

    public function regimeAlimentaires()
    {
        return $this->hasMany(RegimeAlimentaire::class, 'statut_code', 'code');
    }
}