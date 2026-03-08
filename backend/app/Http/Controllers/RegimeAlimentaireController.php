<?php

namespace App\Http\Controllers;

use App\Models\RegimeAlimentaire;
use App\Models\Patient; // Assuming you have a Patient model
use App\Models\User;    // Assuming your User model is for doctors/medecins
use App\Models\RegimeStatut; // For the status dropdown
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // To get the authenticated user's ID

class RegimeAlimentaireController extends Controller
{
    /**
     * Display a listing of the dietary regimes for the authenticated doctor.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the ID of the currently authenticated doctor
        $medecinId = Auth::id();

        // Fetch dietary regimes prescribed by the authenticated doctor,
        // ordered by prescription date descending, and eager load relationships.
        $regimes = RegimeAlimentaire::where('medecin_id', $medecinId)
                                    ->with(['patient', 'medecin', 'regimeStatut'])
                                    ->orderBy('date_prescription', 'desc')
                                    ->paginate(10);

        return view('medecin.regimes.index', compact('regimes'));
    }

    /**
     * Show the form for creating a new dietary regime.
     *
     * @return \Illuminate\View\View
     */
  public function create()
{
    $patients = Patient::orderBy('nom')->get();
    $regimeStatuses = RegimeStatut::all();
    
    // Find the "Actif" status code (assuming it exists in your database)
    $defaultStatus = RegimeStatut::where('name', 'Actif')->first();
    
    return view('medecin.regimes.create', [
        'patients' => $patients,
        'regimeStatuses' => $regimeStatuses,
        'defaultStatus' => $defaultStatus ? $defaultStatus->code : null
    ]);
}

    /**
     * Store a newly created dietary regime in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
 public function store(Request $request)
    {
        // Convert comma-separated string to an array for 'restrictions'
        if ($request->has('restrictions') && is_string($request->input('restrictions'))) {
            $request->merge([
                'restrictions' => array_map('trim', explode(',', $request->input('restrictions')))
            ]);
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'date_prescription' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_prescription',
            'calories_journalieres' => 'nullable|integer|min:0',
            'restrictions' => 'nullable|array', // This will now correctly validate the array
            'recommandations' => 'nullable|string',
            'statut_code' => 'required|exists:regime_statuts,code',
        ]);

        // Force status to be "Actif" for new regimes
        $actifStatus = RegimeStatut::where('name', 'Actif')->firstOrFail();

        RegimeAlimentaire::create([
            'patient_id' => $request->patient_id,
            'medecin_id' => Auth::id(),
            'date_prescription' => $request->date_prescription,
            'statut_code' => $actifStatus->code, // Force active status
            'date_expiration' => $request->date_expiration,
            'calories_journalieres' => $request->calories_journalieres,
            'restrictions' => $request->restrictions, // This will be the array
            'recommandations' => $request->recommandations,
        ]);

        return redirect()->route('regimes.index')->with('success', 'Régime alimentaire créé avec succès.');
    }

    /**
     * Display the specified dietary regime.
     *
     * @param  \App\Models\RegimeAlimentaire  $regimeAlimentaire
     * @return \Illuminate\View\View
     */
    public function show(RegimeAlimentaire $regime)
    {
        

        // Eager load relationships for display
        $regime->load(['patient', 'medecin', 'regimeStatut']);
        return view('medecin.regimes.show', compact('regime'));
    }

    /**
     * Show the form for editing the specified dietary regime.
     *
     * @param  \App\Models\RegimeAlimentaire  $regimeAlimentaire
     * @return \Illuminate\View\View
     */
    public function edit(RegimeAlimentaire $regime)
    {
        
        $patients = Patient::orderBy('nom')->get();
        $regimeStatuses = RegimeStatut::all();

        return view('medecin.regimes.edit', compact('regime', 'patients', 'regimeStatuses'));
    }

    /**
     * Update the specified dietary regime in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RegimeAlimentaire  $regimeAlimentaire
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, RegimeAlimentaire $regime)
    {
        // Ensure the authenticated doctor can only update their own regimes
       if ($request->has('restrictions') && is_string($request->input('restrictions'))) {
            $request->merge([
                'restrictions' => array_map('trim', explode(',', $request->input('restrictions')))
            ]);
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'date_prescription' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_prescription',
            'calories_journalieres' => 'nullable|integer|min:0',
            'restrictions' => 'nullable|array',
            'recommandations' => 'nullable|string',
            'statut_code' => 'required|exists:regime_statuts,code',
        ]);

        $regime->update([
            'patient_id' => $request->patient_id,
            // medecin_id is typically not updated after creation, but you can add it if needed
            'date_prescription' => $request->date_prescription,
            'date_expiration' => $request->date_expiration,
            'calories_journalieres' => $request->calories_journalieres,
            'restrictions' => $request->restrictions,
            'recommandations' => $request->recommandations,
            'statut_code' => $request->statut_code,
        ]);

        return redirect()->route('regimes.index')->with('success', 'Régime alimentaire mis à jour avec succès.');
    }

    /**
     * Remove the specified dietary regime from storage.
     *
     * @param  \App\Models\RegimeAlimentaire  $regimeAlimentaire
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(RegimeAlimentaire $regime)
    {
       
        $regimeAlimentaire->delete();
        return redirect()->route('regimes.index')->with('success', 'Régime alimentaire supprimé avec succès.');
    }
}
