<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Authenticatable
{
    use SoftDeletes, HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'cin',
        'nom',
        'prenom',
        'date_naissance',
        'genre',
        'email',
        'password',
        'telephone',
        'adresse',
        'image_profil',// Legacy field
        'current_service_id', // New field
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    
    ];

    protected $appends = [
        'profile_image_url',
    ];


    public function routeNotificationForMail()
    {
        return $this->email;
    }

    // Relationships
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function documentMedicals()
    {
        return $this->hasMany(DocumentMedical::class);
    }
   
    public function dossierMedical()
    {
        return $this->hasOne(DossierMedical::class);
    }

    public function regimeAlimentaires()
    {
        return $this->hasMany(RegimeAlimentaire::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
  
    public function patientBloodPressures()
    {
        return $this->hasMany(PatientBloodPressure::class);
    }

    public function patientBloodSugars()
    {
        return $this->hasMany(PatientBloodSugar::class);
    }

    // New Subscription Relationships
     public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->latestOfMany();
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'current_service_id');
    }

    // Attributes
 public function getProfileImageUrlAttribute()
{
    if ($this->image_profil) {
        // Check if it's already a full URL (for migration purposes)
        if (filter_var($this->image_profil, FILTER_VALIDATE_URL)) {
            return $this->image_profil;
        }
        return asset('storage/'.$this->image_profil);
    }
    return asset('images/default-profile.png');
}

public function scopeSubscribed($query)
{
    return $query->whereHas('activeSubscription');
}

public function scopeRecent($query, $days = 30)
{
    return $query->where('created_at', '>=', now()->subDays($days));
}

   
}