<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Consultation;
use Illuminate\Support\Facades\Log;

class NewConsultationRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public $consultation;

    public function __construct(Consultation $consultation)
    {
        try {
            // Load relationships with error handling
            if (!$consultation->relationLoaded('medecin')) {
                $consultation->load('medecin');
            }
            
            if (!$consultation->relationLoaded('patient')) {
                $consultation->load('patient');
            }

            $this->consultation = $consultation;

            Log::debug('Notification constructed', [
                'consultation_id' => $consultation->id,
                'medecin_loaded' => isset($consultation->medecin),
                'patient_loaded' => isset($consultation->patient),
                'date_heure' => $consultation->date_heure?->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to construct notification', [
                'error' => $e->getMessage(),
                'consultation_id' => $consultation->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function via($notifiable)
    {
        return ['database']; // Temporarily remove mail until we fix the issue
    }

    public function toDatabase($notifiable)
    {
        try {
            // Validate required data
            if (!$this->consultation->medecin) {
                throw new \RuntimeException('Doctor relationship missing');
            }

            if (!$this->consultation->patient) {
                throw new \RuntimeException('Patient relationship missing');
            }

            if (!$this->consultation->date_heure) {
                throw new \RuntimeException('Missing appointment date');
            }

            $doctorName = $this->consultation->medecin->name ?? 'Unknown Doctor';
            $patientName = $this->getPatientName();
            $dateTime = $this->consultation->date_heure->format('Y-m-d H:i');

            $payload = [
                'consultation_id' => $this->consultation->id,
                'doctor_name' => $doctorName,
                'patient_name' => $patientName,
                'date_time' => $dateTime,
                'message' => "New appointment from {$patientName}",
                'type' => 'new_consultation_request',
                'status' => $this->consultation->status,
            ];

            Log::debug('Notification payload prepared', $payload);

            return $payload;

        } catch (\Throwable $e) {
            Log::error('Failed to create notification payload', [
                'consultation_id' => $this->consultation->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback minimal payload
            return [
                'consultation_id' => $this->consultation->id ?? 'unknown',
                'message' => 'New consultation request',
                'type' => 'new_consultation_request',
                'error' => $e->getMessage()
            ];
        }
    }

    protected function getPatientName(): string
    {
        try {
            if (!$this->consultation->patient) {
                return 'Unknown Patient';
            }

            $parts = [
                $this->consultation->patient->prenom ?? '',
                $this->consultation->patient->nom ?? ''
            ];

            $name = trim(implode(' ', $parts));
            
            return $name ?: $this->consultation->patient->email ?? 'Unknown Patient';
        } catch (\Throwable $e) {
            Log::error('Failed to get patient name', [
                'error' => $e->getMessage()
            ]);
            return 'Unknown Patient';
        }
    }
}