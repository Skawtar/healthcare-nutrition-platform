<?php

namespace App\Http\Controllers;

use App\Models\DocumentMedical;
use App\Models\Patient;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index()
{
    $documents = DocumentMedical::with(['patient', 'medicalRecord'])
                    ->where('medecin_id', auth()->id())
                    ->latest()
                    ->get();
    
    return view('medecin.documents.index', compact('documents'));
}

   public function create(Patient $patient = null)
{
    $patients = Patient::all();
    $medicalRecords = MedicalRecord::where('patient_id', $patient->id ?? null )
                    ->where('medecin_id', auth()->id())
                    ->get();
    return view('medecin.documents.create', compact('patients', 'medicalRecords', 'patient'));
}
    public function store(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'nom_fichier' => 'required|string|max:255',
            'patient_id' => 'required|exists:patients,id',
            'medecin_id' => 'required|exists:users,id',
            'medical_record_id' => 'nullable|exists:medical_records,medical_record_id',
            'document_type' => 'required|string|max:100',
            'est_signe' => 'boolean'
        ]);

        $file = $request->file('fichier');
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $file->getClientOriginalExtension());
        $path = $file->storeAs('medical_documents', $filename);

        $document = DocumentMedical::create([
            'patient_id' => $request->patient_id,
            'medecin_id' => $request->medecin_id,
            'medical_record_id' => $request->medical_record_id,
            'nom_fichier' => $request->nom_fichier,
            'fichier' => $path,
            'document_type' => $request->document_type,
            'est_signe' => $request->est_signe ?? false,
            'date_creation' => now()
        ]);

        return redirect()->route('documents.index')->with('success', 'Document uploaded successfully!');
    }

    public function download(DocumentMedical $document)
    {
        if (!Storage::exists($document->fichier)) {
            abort(404, 'File not found');
        }

        return Storage::download($document->fichier, $document->nom_fichier);
    }

    public function destroy(DocumentMedical $document)
    {
        Storage::delete($document->fichier);
        $document->delete();

        return response()->json(['success' => true]);
    }

    public function toggleSign(DocumentMedical $document)
    {
        $document->update(['est_signe' => !$document->est_signe]);
        return response()->json(['signed' => $document->est_signe]);
    }
}