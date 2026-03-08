<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole; 
// Ensure this matches your UserRole enum path
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        
       'name',
    'email',
    'password',
    'date_naissance', // <--- Make sure it's here
    'genre',
    'specialite_code',
    'num_licence',
    'telephone',
    'adresse',
    'diplome',
    'adresse_cabinet',
    'experience',
    'ville',
    'horaires_debut',
    'horaires_fin',
    'jours_travail',
    'tarif_consultation',
    'image_profil',
    'cin', 
    'date_inscription', 

       
    ];

    public function regimeAlimentaires()
    {
        return $this->hasMany(RegimeAlimentaire::class, 'medecin_cin', 'id');
    }
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'medecin_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(DocumentMedical::class, 'medecin_id', 'id');
    }
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'medecin_id', 'id');
    }

       public function routeNotificationForMail()
    {
        return $this->email;
    }

    public function scopeDoctors($query)
{
    return $query->where('role', UserRole::User);
}

public function scopeRecent($query, $days = 30)
{
    return $query->where('created_at', '>=', now()->subDays($days));
}
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    
  protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'date_naissance' => 'date',
        'date_inscription' => 'date',
        'horaires_debut' => 'datetime:H:i',
        'horaires_fin' => 'datetime:H:i',
     'jours_travail' => 'array',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
   
}
