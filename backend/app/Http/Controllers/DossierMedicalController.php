<?php

namespace App\Http\Controllers;

use App\Models\DossierMedical;
use Illuminate\Http\Request;

class DossierMedicalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    // Check authentication first
    if (!auth('patient')->check()) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    // Get authenticated patient safely
    $patient = auth('patient')->user();
    
    // Validate input
    $validated = $request->validate([
        'poids' => 'nullable|numeric',
        'taille' => 'nullable|numeric',
        'groupe_sanguin' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        'allergies' => 'nullable|json',
        'antecedents' => 'nullable|json',
        'traitements' => 'nullable|json',
        'derniere_consultation' => 'nullable|date',
    ]);

    // Add patient_id to the validated data
    $validated['patient_id'] = $patient->id;

    // Create or update the dossier
    $dossier = DossierMedical::updateOrCreate(
        ['patient_id' => $patient->id],
        $validated
    );

    return response()->json([
        'message' => 'Medical dossier saved successfully',
        'dossier' => $dossier
    ]);
}
    /**
     * Display the specified resource.
     */
    public function show(DossierMedical $dossierMedical)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DossierMedical $dossierMedical)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DossierMedical $dossierMedical)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DossierMedical $dossierMedical)
    {
        //
    }
}
