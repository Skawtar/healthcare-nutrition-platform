<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ConsultationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $consultation;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(Consultation $consultation, string $oldStatus, string $newStatus)
    {
        $this->consultation = $consultation;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;

        // Eager load relationships: 'medecin' (which is a User), 'patient' (which is a Patient)
        // This is correct based on your models.
        $this->consultation->load(['medecin', 'patient']);
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        // ... (this method is fine, not the source of the N/A issue in Flutter UI) ...
        $doctorName = $this->consultation->medecin->name ?? 'N/A';
        $consultationDateFormatted = $this->consultation->date_heure ? $this->consultation->date_heure->format('M j, Y \a\t g:i A') : 'N/A';

        return (new MailMessage)
            ->subject('Consultation Status Updated')
            ->line('Your consultation status has been updated from ' . ucfirst($this->oldStatus) . ' to: ' . ucfirst($this->newStatus) . '.')
            ->line('Doctor: ' . $doctorName)
            ->line('Scheduled Time: ' . $consultationDateFormatted)
            ->action('View Details', url('/consultations/' . $this->consultation->id))
            ->line('Thank you for using our platform!');
    }



public function toDatabase($notifiable)
{
    // Safely get doctor's name and specialty (medecin is a User model)
    $doctorName = $this->consultation->medecin->name ?? 'N/A';
    $doctorSpeciality = ($this->consultation->medecin) ? ($this->consultation->medecin->specialite_code ?? 'N/A') : 'N/A'; 

    // Safely get patient's full name (patient is a Patient model, has nom and prenom directly)
    $patientName = 'N/A';
    if ($this->consultation->patient) {
        $patientNom = $this->consultation->patient->nom ?? '';
        $patientPrenom = $this->consultation->patient->prenom ?? '';
        if ($patientNom || $patientPrenom) {
            $patientName = trim("$patientNom $patientPrenom");
        } else {
            $patientName = $this->consultation->patient->email ?? 'N/A';
        }
    }

    // Format date and time
    $consultationDate = 'N/A';
    $consultationTime = 'N/A';
    if ($this->consultation->date_heure) {
        $consultationDate = $this->consultation->date_heure->format('Y-m-d');
        $consultationTime = $this->consultation->date_heure->format('H:i');
    }

    $message = "Your consultation with $doctorName ($doctorSpeciality) has been " . ucfirst($this->newStatus) . ".";

    // *** THIS IS THE CRITICAL LOGGING PART ***
    Log::info('DEBUG: ConsultationStatusChanged final data payload values:', [
        'consultation_id_val' => $this->consultation->id,
        'doctor_name_val' => $doctorName,
        'doctor_speciality_val' => $doctorSpeciality,
        'patient_name_val' => $patientName,
        'consultation_date_val' => $consultationDate,
        'consultation_time_val' => $consultationTime,
        'final_message_val' => $message,
        'has_medecin_obj' => (bool)$this->consultation->medecin,
        'medecin_id_on_consultation' => $this->consultation->medecin_id,
        'has_patient_obj' => (bool)$this->consultation->patient,
        'patient_id_on_consultation' => $this->consultation->patient_id,
        'date_heure_obj_exists' => (bool)$this->consultation->date_heure,
        'date_heure_raw_value' => $this->consultation->getRawOriginal('date_heure'),
    ]);
    // *** END CRITICAL LOGGING ***

    return [
        'consultation_id' => $this->consultation->id,
        'doctor_name' => $doctorName,
        'doctor_speciality' => $doctorSpeciality,
        'patient_name' => $patientName,
        'old_status' => $this->oldStatus,
        'new_status' => $this->newStatus,
        'status' => $this->newStatus,
        'consultation_date' => $consultationDate,
        'consultation_time' => $consultationTime,
        'message' => $message,
        'type' => 'status_changed'
    ];
}
}