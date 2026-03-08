<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Notifications\ConsultationStatusChanged; // Notification for patient
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Added for logging errors
use Illuminate\Support\Facades\Notification; // For sending notifications
use App\Models\User; // Assuming you have a User model for doctors (medecins)

class ConsultationController extends Controller
{
    /**
     * Display a list of consultations for the authenticated doctor (web dashboard).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user(); // This will be the doctor (User model)
        $perPage = 10; // Number of items per page for pagination

        // --- Upcoming Consultations Query (including pending and confirmed) ---
        $upcomingQuery = Consultation::with(['patient', 'medecin']) // Eager load patient and medecin
            ->where('medecin_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('date_heure', '>=', now())
            ->orderBy('date_heure', 'asc');

        // Apply CIN search for upcoming consultations
        if ($request->filled('upcoming_cin_search')) {
            $cin = $request->input('upcoming_cin_search');
            $upcomingQuery->whereHas('patient', function ($q) use ($cin) {
                $q->where('cin', 'like', '%' . $cin . '%');
            });
        }

        // Apply status filter for upcoming consultations
        if ($request->filled('upcoming_status_filter')) {
            $upcomingQuery->where('status', $request->input('upcoming_status_filter'));
        }

        $upcomingConsultations = $upcomingQuery->paginate($perPage, ['*'], 'upcoming_page');

        // --- Historical Consultations Query ---
        $historicalQuery = Consultation::with(['patient', 'medecin']) // Eager load patient and medecin
            ->where('medecin_id', $user->id)
            ->whereIn('status', ['completed', 'cancelled', 'rejected']) // Filter out 'pending' and 'confirmed' as they are handled above
            ->orderBy('date_heure', 'desc');

        // Apply CIN search for historical consultations
        if ($request->filled('historical_cin_search')) {
            $cin = $request->input('historical_cin_search');
            $historicalQuery->whereHas('patient', function ($q) use ($cin) {
                $q->where('cin', 'like', '%' . $cin . '%');
            });
        }

        // Apply status filter for historical consultations
        if ($request->filled('historical_status_filter')) {
            $historicalQuery->where('status', $request->input('historical_status_filter'));
        }

        $historicalConsultations = $historicalQuery->paginate($perPage, ['*'], 'historical_page');

        // Note: $pendingConsultations in your original code seems to be a subset of $upcomingConsultations.
        // I've streamlined to use $upcomingConsultations for the main "Upcoming" list with filters.
        // If you need a distinct "Pending Actions" section, you can add another query.
        // For now, I'll pass only upcomingConsultations and historicalConsultations.

        return view('medecin.consultations.index', compact('upcomingConsultations', 'historicalConsultations'));
    }

    /**
     * Display a specific consultation for the authenticated doctor (web dashboard).
     *
     * @param  \App\Models\Consultation  $consultation
     * @return \Illuminate\View\View
     */
    public function show(Consultation $consultation)
    {
        // Authorization - only the assigned doctor can view from the web dashboard
        if (Auth::id() !== $consultation->medecin_id) {
            abort(403, 'Unauthorized action.');
        }

        // Load necessary relationships for the doctor's view
        $consultation->load([
            'patient.dossierMedical', // Assuming these relationships exist on Patient model
            'patient.documentMedicals',
            'patient.medicalRecords',
            'patient.consultations.medecin', // To see patient's other consultations with other doctors
            'medecin' // The current doctor
        ]);

        return view('medecin.consultations.show', compact('consultation'));
    }

    /**
     * Update the status of a consultation by the doctor (from web dashboard).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Consultation  $consultation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Consultation $consultation)
    {
        // Authorization: Ensure only the assigned doctor or authorized user can update
        if (Auth::id() !== $consultation->medecin_id) {
            abort(403, 'Unauthorized action.');
        }

        // Validation for the new status
        $request->validate([
            'status' => 'required|string|in:confirmed,rejected,cancelled,completed', // Add other valid statuses as needed
        ]);

        $newStatus = $request->input('status');
        $currentStatus = $consultation->status;

        // Optional: Add logic to prevent invalid status transitions (e.g., cannot confirm a completed consultation)
        // if ($currentStatus == 'completed' && ($newStatus == 'pending' || $newStatus == 'confirmed')) {
        //     return back()->with('error', 'Cannot change status from completed to ' . $newStatus . '.');
        // }

        try {
            $consultation->update(['status' => $newStatus]);

            // Load only 'medecin' (which is a User) and 'patient' (which is a Patient)
            $consultation->load(['medecin', 'patient']);

            // Log what is available in the controller before dispatching
            Log::info('Controller dispatching data:', [
                'consultation_id' => $consultation->id,
                'doctor_name_from_controller' => $consultation->medecin->name ?? 'NOT_LOADED_DOC_NAME_CONTROLLER',
                'doctor_speciality_from_controller' => $consultation->medecin->specialite_code ?? 'NOT_LOADED_DOC_SPEC_CONTROLLER',
                'patient_nom_from_controller' => $consultation->patient->nom ?? 'NOT_LOADED_PAT_NOM_CONTROLLER',
                'patient_prenom_from_controller' => $consultation->patient->prenom ?? 'NOT_LOADED_PAT_PRENOM_CONTROLLER',
                'consultation_date_from_controller' => $consultation->date_heure ? $consultation->date_heure->format('Y-m-d') : 'NOT_LOADED_DATE_CONTROLLER',
                'consultation_time_from_controller' => $consultation->date_heure ? $consultation->date_heure->format('H:i') : 'NOT_LOADED_TIME_CONTROLLER',
            ]);

            // Notify the Patient instance directly
            if ($consultation->patient) {
                // Ensure the Patient model uses the Notifiable trait
                $consultation->patient->notify(new ConsultationStatusChanged($consultation, $currentStatus, $newStatus));
            } else {
                Log::warning('Attempted to notify a null patient for consultation ID: ' . $consultation->id);
            }

            return back()->with('success', 'Consultation status updated successfully to ' . $newStatus . '!');

        } catch (\Exception $e) {
            Log::error('Error updating consultation status: ' . $e->getMessage(), ['consultation_id' => $consultation->id]);
            return back()->with('error', 'Failed to update consultation status. Please try again.');
        }
    }
}