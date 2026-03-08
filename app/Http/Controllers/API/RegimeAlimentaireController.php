<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RegimeAlimentaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegimeAlimentaireController extends Controller
{
    /**
     * Get all dietary regimens for a specific patient
     *
     * @param int $patientId
     * @return JsonResponse
     */

    public function byPatient($patientId)
    {
        try {
            // Validate patientId is numeric
            if (!is_numeric($patientId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid patient ID'
                ], 400);
            }

            $diets = RegimeAlimentaire::with(['patient', 'medecin', 'regimeStatut'])
                ->where('patient_id', $patientId)
                ->get()
                ->map(function ($diet) {
                    return [
                        'id' => $diet->id,
                        'date_prescription' => $diet->date_prescription->format('Y-m-d'),
                        'date_expiration' => $diet->date_expiration->format('Y-m-d'),
                        'calories_journalieres' => $diet->calories_journalieres,
                        'status_code' => $diet->statut_code, // Use statut_code from RegimeAlimentaire model
                        'restrictions' => $diet->restrictions ?? [],
                        'recommandations' => $diet->recommandations ?? '',
                        'medecin' => $diet->medecin ? [
                            'id' => $diet->medecin->id,
                            'name' => $diet->medecin->name,
                            'specialty' => $diet->medecin->specialty,
                        ] : null,
                        'status' => [
                            'code' => $diet->regimeStatut->code ?? $diet->statut_code, // Fallback to statut_code if relationship fails
                            'name' => optional($diet->regimeStatut)->name, // CHANGED: Use 'name' instead of 'display_name'
                            'color' => optional($diet->regimeStatut)->color,
                        ],
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $diets
            ]);

        } catch (\Exception $e) {
            Log::error("Dietary regimen error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server error'
            ], 500);
        }
    }

/**
     * Display the specified dietary regimen
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $diet = RegimeAlimentaire::with([
                'patient:id,nom,prenom,email',
                'medecin:id,name,email,specialty',
                'regimeStatut:id,name'
            ])->find($id);

            if (!$diet) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Dietary regimen not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $diet->id,
                    'date_prescription' => $diet->date_prescription->format('Y-m-d'),
                    'date_expiration' => $diet->date_expiration->format('Y-m-d'),
                    'calories_journalieres' => $diet->calories_journalieres,
                    'status_code' => $diet->status_code,
                    'status_name' => optional($diet->regimeStatut)->name,
                    'restrictions' => $diet->restrictions ?? [],
                    'recommandations' => $diet->recommandations ?? '',
                    'patient' => optional($diet->patient, function ($patient) {
                        return [
                            'id' => $patient->id,
                            'full_name' => trim($patient->prenom . ' ' . $patient->nom),
                            'email' => $patient->email,
                        ];
                    }),
                    'medecin' => optional($diet->medecin, function ($medecin) {
                        return [
                            'id' => $medecin->id,
                            'name' => $medecin->name,
                            'email' => $medecin->email,
                            'specialty' => $medecin->specialty,
                        ];
                    }),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to fetch diet {$id}: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dietary regimen'
            ], 500);
        }
    }
}