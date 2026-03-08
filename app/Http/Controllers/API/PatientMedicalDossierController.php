<?php

namespace App\Http\Controllers\API; 
use Illuminate\Http\Request;
use App\Models\DossierMedical;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class PatientMedicalDossierController extends Controller
{
  

  public function storeOrUpdate(Request $request)
    {
        try {
            $patient = Auth::user(); // Get the authenticated patient

            $validatedData = $request->validate([
                'poids' => 'nullable|numeric|min:0',
                'taille' => 'nullable|numeric|min:0',
                'groupe_sanguin' => 'nullable|string|max:10',
                'allergies' => 'nullable|string', // Store as single string, separate in Flutter
                'antecedents' => 'nullable|string', // Store as single string
                'traitements' => 'nullable|string', // Store as single string
                'derniere_consultation' => 'nullable|date',
            ]);

            // Find or create the medical dossier for this patient
            $dossier = $patient->dossierMedical; // Assuming a one-to-one relationship
            if (!$dossier) {
                $dossier = new DossierMedical();
                $dossier->patient_id = $patient->id; // Link to the patient
            }

            $dossier->fill($validatedData);
            $dossier->save();

            return response()->json([
                'message' => 'Medical dossier saved successfully!',
                'dossier' => $dossier // Return the saved dossier
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error saving medical dossier: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save medical dossier.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

      public function show(Request $request)
    {
        try {
            $patient = Auth::user(); // Get the authenticated patient

            $dossier = $patient->dossierMedical; // Assuming a one-to-one relationship

            if (!$dossier) {
                return response()->json([
                    'message' => 'Medical dossier not found for this patient.',
                    'dossier' => null // Explicitly return null dossier
                ], 200); // 200 OK, but with null dossier indicating not found
            }

            return response()->json([
                'message' => 'Medical dossier fetched successfully!',
                'dossier' => $dossier
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching medical dossier: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch medical dossier.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}