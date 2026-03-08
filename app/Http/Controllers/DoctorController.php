<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\User; // Assuming your Doctor model is the User model
use Illuminate\Support\Facades\Auth; // To get the authenticated doctor

class DoctorController extends Controller
{
    public function __construct()
    {
       
        $this->middleware('auth');
    }

    /**
     * Display a listing of the doctor's consultations (medical history).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function medicalHistory(Request $request)
    {
        /** @var \App\Models\User $doctor */
        $doctor = Auth::user(); // Get the currently authenticated doctor

        // Fetch consultations by this doctor, eager load patient, documents, and medical records
        $consultationsQuery = $doctor->consultations()
            ->with([
                'patient:id,nom,prenom', // Select specific patient columns for efficiency
                'documentMedicals',     // Load documents associated with this consultation
                'medicalRecords'        // Load medical records associated with this consultation
            ])
            ->orderBy('date_heure', 'desc'); // Order by most recent consultation first

        // Optional: Add filtering/searching for consultations
        if ($request->filled('patient_name_search')) {
            $searchTerm = $request->input('patient_name_search');
            $consultationsQuery->whereHas('patient', function ($q) use ($searchTerm) {
                $q->where('nom', 'like', '%' . $searchTerm . '%')
                  ->orWhere('prenom', 'like', '%' . $searchTerm . '%');
            });
        }
        if ($request->filled('date_filter')) {
            // Filter by exact date of consultation
            $consultationsQuery->whereDate('date_heure', $request->input('date_filter'));
        }

        // Paginate the results to avoid loading too much data at once
        $consultations = $consultationsQuery->paginate(10)->appends($request->except('page'));

        return view('medecin.medical_history.index', compact('consultations'));
    }

    /**
     * Display the specified consultation with all its details, documents, and medical records.
     *
     * @param  \App\Models\Consultation  $consultation
     * @return \Illuminate\View\View
     */
    public function showConsultation(Consultation $consultation)
    {
        // Security check: Ensure the authenticated doctor owns this consultation
        // If not, abort with a 403 Forbidden error.
        if (Auth::id() !== $consultation->medecin_id) {
            abort(403, 'Unauthorized action. You do not have access to this consultation.');
        }

        // Eager load all necessary relationships for the detailed view
        $consultation->load([
            'patient.dossierMedical', // Load patient and their medical dossier
            'documentMedicals.medecin', // Load documents and the doctor who uploaded them
            'medicalRecords.medecin'    // Load medical records and the doctor who created them
        ]);

        return view('medecin.medical_history.show_consultation', compact('consultation'));
    }


     public function medicalRecordsHistory(Request $request)
    {
        /** @var \App\Models\User $doctor */
        $doctor = Auth::user();

        // Fetch medical records created by this doctor
        $medicalRecordsQuery = $doctor->medicalRecords()
            ->with([
                'patient:id,nom,prenom,cin', // Eager load patient with specific columns
                'consultation:id,date_heure' // NEW: Eager load consultation with id and date_heure
            ])
            ->orderBy('record_start_date', 'desc');

        // Optional: Add filtering/searching for medical records
        if ($request->filled('patient_name_search')) {
            $searchTerm = $request->input('patient_name_search');
            $medicalRecordsQuery->whereHas('patient', function ($q) use ($searchTerm) {
                $q->where('nom', 'like', '%' . $searchTerm . '%')
                  ->orWhere('prenom', 'like', '%' . $searchTerm . '%');
            });
        }
        if ($request->filled('date_filter')) {
            $medicalRecordsQuery->whereDate('record_start_date', $request->input('date_filter'));
        }
        if ($request->filled('cin_search')) {
            $searchTerm = $request->input('cin_search');
            $medicalRecordsQuery->whereHas('patient', function ($q) use ($searchTerm) {
                $q->where('cin', 'like', '%' . $searchTerm . '%');
            });
        }
        // NEW: Filter by consultation date
        if ($request->filled('consultation_date_filter')) {
            $medicalRecordsQuery->whereHas('consultation', function ($q) use ($request) {
                $q->whereDate('date_heure', $request->input('consultation_date_filter'));
            });
        }


        $medicalRecords = $medicalRecordsQuery->paginate(10)->appends($request->except('page'));

        return view('medecin.medical_records.index', compact('medicalRecords'));
    }
}
