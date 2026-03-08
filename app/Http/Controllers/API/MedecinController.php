<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MedecinController extends Controller
{
    /**
     * Get all active doctors with pagination
     * 
     * @return JsonResponse
     */
   public function index(): JsonResponse
{
    try {
        $activeDoctors = User::where('role', 'medecin')
            ->where('est_actif', true)
            ->select([
                'id',
                'name',
                'email',
                'telephone',
                'jours_travail',
                'image_profil',
                'tarif_consultation',
                'specialite_code',
                'adresse_cabinet',
                'horaires_debut',
                'horaires_fin'
            ])
            ->orderBy('name')
            ->paginate(15);

        // Transform the collection
        $transformedDoctors = $activeDoctors->getCollection()->map(function ($doctor) {
            // Handle working days - no need to json_decode if using array cast
            $workingDays = is_array($doctor->jours_travail) 
                ? $doctor->jours_travail 
                : (json_decode($doctor->jours_travail, true) ?? []);

            // Map French day names to English
            $dayMapping = [
                'Lundi' => 'Monday',
                'Mardi' => 'Tuesday',
                'Mercredi' => 'Wednesday',
                'Jeudi' => 'Thursday',
                'Vendredi' => 'Friday',
                'Samedi' => 'Saturday',
                'Dimanche' => 'Sunday'
            ];

            $englishDays = array_map(function($day) use ($dayMapping) {
                return $dayMapping[$day] ?? $day;
            }, $workingDays);

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'telephone' => $doctor->telephone,
                'specialty' => $doctor->specialite_code,
                'working_days' => $englishDays,
                'working_days_raw' => $workingDays, // Keep original French values if needed
                'profile_image' => $this->getProfileImageUrl($doctor->image_profil),
                'consultation_fee' => (float)$doctor->tarif_consultation,
                'address' => $doctor->adresse_cabinet,
                'working_hours' => [
                    'start' => $doctor->horaires_debut,
                    'end' => $doctor->horaires_fin
                ]
            ];
        });

        // Set the transformed collection back to the paginator
        $activeDoctors->setCollection($transformedDoctors);

        return response()->json([
            'success' => true,
            'data' => $activeDoctors,
            'message' => 'List of active doctors retrieved successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to fetch doctors: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve doctors list',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

    /**
     * Get specific doctor details
     * 
     * @param int $id
     * @return JsonResponse 
     */
    public function show(int $id): JsonResponse
    {
        try {
            $doctor = User::where('role', 'medecin')
                ->where('est_actif', true)
                ->select([
                    'id',
                    'name',
                    'email',
                    'telephone',
                    'jours_travail',
                    'image_profil',
                    'tarif_consultation',
                    'specialite_code',
                    'adresse_cabinet',
                    'horaires_debut',
                    'horaires_fin',
                    'experience',
                    'ville',
                    'diplome'
                ])
                ->find($id);

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor not found or inactive'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'email' => $doctor->email,
                    'phone' => $doctor->telephone,
                    'specialty' => $doctor->specialite_code,
                    'working_days' => $doctor->jours_travail?? [],
                    'profile_image' => $this->getProfileImageUrl($doctor->image_profil),
                    'consultation_fee' => (float)$doctor->tarif_consultation,
                    'address' => $doctor->adresse_cabinet,
                    'working_hours' => [
                        'start' => $doctor->horaires_debut,
                        'end' => $doctor->horaires_fin
                    ],
                    'experience' => $doctor->experience,
                    'city' => $doctor->ville,
                    'qualifications' => $doctor->diplome
                ],
                'message' => 'Doctor details retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to fetch doctor {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve doctor details',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Helper method to get full profile image URL
     * 
     * @param string|null $imagePath
     * @return string|null
     */
    private function getProfileImageUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        // Check if the path is already a full URL
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }

        // Check if the file exists in storage
        if (Storage::exists($imagePath)) {
            return asset('storage/' . $imagePath);
        }

        return null;
    }
}