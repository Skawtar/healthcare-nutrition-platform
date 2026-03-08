<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient; // Needed for patient selection in create form
use App\Models\Consultation; // Needed for linking to consultations
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // To get the authenticated doctor
// If you plan to generate PDFs, you might need a library like Dompdf or Snappy
// use Barryvdh\DomPDF\Facade\Pdf; // Example if using Dompdf

class MedicalRecordController extends Controller
{
    public function __construct()
    {
        // Ensure only authenticated users (doctors) can manage medical records
        $this->middleware('auth');
        // You might want to add a specific role check here, e.g., $this->middleware('can:isDoctor');
    }

    /**
     * Show the form for creating a new medical record.
     * Optionally pre-selects a patient if passed.
     *
     * @param  \App\Models\Patient|null  $patient
     * @return \Illuminate\View\View
     */
    public function create(Patient $patient = null)
    {
        // Get all patients to populate the dropdown
        $patients = Patient::orderBy('nom')->get();

        // Get consultations for the selected patient (if any) and the authenticated doctor
        $consultations = collect(); // Initialize as empty collection
        if ($patient) {
            $consultations = Consultation::where('patient_id', $patient->id)
                                        ->where('medecin_id', Auth::id())
                                        ->orderBy('date_heure', 'desc')
                                        ->get();
        }

        return view('medecin.medical_records.create', compact('patient', 'patients', 'consultations'));
    }

    /**
     * Store a newly created medical record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'consultation_id' => 'nullable|exists:consultations,id', // Make sure consultation_id exists if provided
            'record_start_date' => 'required|date',
            'notes' => 'required|string',
        ]);

        // Ensure the authenticated user is the one creating the record
        $medecinId = Auth::id();

        MedicalRecord::create([
            'patient_id' => $request->patient_id,
            'medecin_id' => $medecinId,
            'consultation_id' => $request->consultation_id,
            'record_start_date' => $request->record_start_date,
            'notes' => $request->notes,
        ]);

        // Redirect back to the patient's show page or a confirmation page
        return redirect()->route('patients.show', $request->patient_id)
                         ->with('success', 'Medical record added successfully!');
    }

    /**
     * Download a PDF or other report for the specified medical record.
     * Since the 'medical_records' table schema does not have a 'file_path' column,
     * this method assumes you want to generate a report from the record's data.
     *
     * @param  \App\Models\MedicalRecord  $medicalRecord
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(MedicalRecord $medicalRecord)
    {
        // Security check: Ensure the authenticated doctor owns this record or has permission
        if (Auth::id() !== $medicalRecord->medecin_id) {
            abort(403, 'Unauthorized action. You do not have permission to download this medical record.');
        }

        // Load necessary relationships for the report
        $medicalRecord->load(['patient', 'medecin', 'consultation']);

        // --- Example of generating a PDF using a library like Dompdf ---
        // You would typically have a Blade view for the PDF content
        // $pdf = Pdf::loadView('medecin.medical_records.pdf_report', compact('medicalRecord'));
        // return $pdf->download('medical_record_' . $medicalRecord->patient->cin . '_' . $medicalRecord->record_start_date->format('Ymd') . '.pdf');

        // --- Placeholder for a simple text download if no PDF library is set up ---
        $content = "Medical Record Details\n\n";
        $content .= "Record ID: " . $medicalRecord->medical_record_id . "\n";
        $content .= "Patient: " . $medicalRecord->patient->nom . " " . $medicalRecord->patient->prenom . " (CIN: " . $medicalRecord->patient->cin . ")\n";
        $content .= "Recorded by: Dr. " . ($medicalRecord->medecin->name ?? 'N/A') . "\n";
        $content .= "Record Date: " . $medicalRecord->record_start_date->format('Y-m-d') . "\n";
        if ($medicalRecord->consultation) {
            $content .= "Consultation Date: " . $medicalRecord->consultation->date_heure->format('Y-m-d H:i') . "\n";
            $content .= "Consultation Motif: " . $medicalRecord->consultation->motif . "\n";
        }
        $content .= "Notes:\n" . $medicalRecord->notes . "\n";

        return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="medical_record_' . $medicalRecord->patient->cin . '_' . $medicalRecord->record_start_date->format('Ymd') . '.txt"');
    }

    /**
     * Remove the specified medical record from storage.
     *
     * @param  \App\Models\MedicalRecord  $medicalRecord
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MedicalRecord $medicalRecord)
    {
        // Security check
        if (Auth::id() !== $medicalRecord->medecin_id) {
            abort(403, 'Unauthorized action.');
        }

        $patientId = $medicalRecord->patient_id; // Store patient ID before deleting

        $medicalRecord->delete();

        // Redirect back to the patient's show page or the medical records history
        return redirect()->route('patients.show', $patientId)
                         ->with('success', 'Medical record deleted successfully!');
    }

    public function indexAll()
    {
        $medicalRecords = MedicalRecord::with(['patient:id,nom,prenom,cin', 'consultation:id,date_heure'])
                                       ->where('medecin_id', Auth::id())
                                       ->orderBy('record_start_date', 'desc')
                                       ->paginate(10);
        return view('medecin.medical_records.index_all', compact('medicalRecords'));
    }

     public function showPatientRecords(Patient $patient)
    {
        

        $medicalRecords = $patient->medicalRecords()
            ->with(['medecin:id,name,specialite_code', 'consultation:id,date_heure']) // Load doctor name/specialty and consultation date
            ->orderBy('record_start_date', 'desc')
            ->paginate(10); // Paginate the results

        return view('medecin.patient.medical_records_all', compact('patient', 'medicalRecords'));
    }
}
