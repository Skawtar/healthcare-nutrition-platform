<?php
namespace App\Http\Controllers\API; 
use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Notifications\NewConsultationRequest;
// Ensure ConsultationStatusChanged is imported if you might use it here later, though it's primarily for doctor-to-patient notification
use App\Notifications\ConsultationStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Ensure Auth is imported for auth()->id() and auth()->user()
use Illuminate\Support\Facades\DB; 

class ConsultationController extends Controller
{
    /**
     * Create new consultation (from Flutter app by patient)
     */
  public function store(Request $request)

{

     DB::beginTransaction();
    try {
        $validatedData = $request->validate([
            'medecin_id' => 'required|exists:users,id|different:patient_id',
            'date_heure' => 'required|date|after_or_equal:now',
            'motif' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Parse and reformat the date to ensure correct format
        $date = \Carbon\Carbon::parse($validatedData['date_heure'])->format('Y-m-d\TH:i:s.v');
        $validatedData['date_heure'] = $date;

        $validatedData['patient_id'] = Auth::id();
        $validatedData['status'] = 'pending';

        $consultation = Consultation::create($validatedData);
        $consultation->load('medecin', 'patient');
        DB::commit();

        Log::info('Dispatching NewConsultationRequest for Consultation ID: ' . $consultation->id);
Log::info('Targeting Medecin ID: ' . $consultation->medecin->id ?? 'N/A');
            if ($consultation->medecin) { 
                $consultation->medecin->notify(new NewConsultationRequest($consultation));
            } else {
                Log::warning("NewConsultationRequest: Medecin not found for consultation ID: {$consultation->id}. Notification not sent.");
            }

        return response()->json([
            'success' => true,
            'message' => 'Appointment created successfully',
            'data' => $consultation
        ], 201);

    } catch (\Exception $e) {
        Log::error('Consultation creation failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to create appointment',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Get patient's upcoming consultations (for Flutter app)
     */
    public function getUpcomingAppointments(Request $request)
    {
        try {
            // Ensure the authenticated user is a Patient
            $patient = Auth::user();
            if (!$patient || !$patient instanceof \App\Models\Patient) { // Explicit check if needed
                 return response()->json(['success' => false, 'message' => 'Unauthorized or not a patient user'], 403);
            }

            $appointments = Consultation::where('patient_id', $patient->id)
                ->where('date_heure', '>', now())
                ->with(['medecin' => function($query) {
                    $query->select('id', 'name', 'specialite_code', 'image_profil');
                }])
                ->orderBy('date_heure', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $appointments,
                'message' => 'Upcoming appointments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch upcoming appointments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient's past consultations (for Flutter app)
     */
    public function getPastAppointments(Request $request)
    {
        try {
            $patient = Auth::user();
            if (!$patient || !$patient instanceof \App\Models\Patient) {
                 return response()->json(['success' => false, 'message' => 'Unauthorized or not a patient user'], 403);
            }

            $appointments = Consultation::where('patient_id', $patient->id)
                ->where('date_heure', '<=', now())
                ->with(['medecin' => function($query) {
                    $query->select('id', 'name', 'specialite_code', 'image_profil');
                }])
                ->orderBy('date_heure', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $appointments,
                'message' => 'Past appointments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch past appointments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get consultation details (for Flutter app, accessible by patient or doctor via API)
     */
    public function show(Consultation $consultation)
    {
        try {
            $user = Auth::user(); // This can be a Patient or a User (doctor)
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Authorization - only patient or assigned doctor can view
            // Check if the authenticated user's ID matches patient_id or medecin_id
            if ($user->id != $consultation->patient_id && $user->id != $consultation->medecin_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this consultation'
                ], 403);
            }

            $consultation->load(['medecin', 'patient']);

            return response()->json([
                'success' => true,
                'data' => $consultation,
                'message' => 'Consultation retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Consultation show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Consultation not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get patient's confirmed upcoming consultations (for Flutter app)
     */
public function getConfirmedUpcomingAppointments(Request $request)
{
    try {
        $patient = Auth::user();

        if (!$patient || !$patient instanceof \App\Models\Patient) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Get appointments with timezone consideration
        // Query only for confirmed appointments strictly in the future
        $appointments = Consultation::where('patient_id', $patient->id)
            ->where('status', 'confirmed')
            ->where('date_heure', '>', now()->utc()) // Changed to strictly greater than now (in UTC)
            ->with(['medecin:id,name,specialite_code,image_profil'])
            ->orderBy('date_heure', 'asc')
            ->get()
            ->map(function ($appointment) {
                // Ensure date_heure is a Carbon instance before calling toIso8601String()
                // It should already be if it's a DateTime cast on the model
                return [
                    'id' => $appointment->id,
                    'motif' => $appointment->motif,
                    'status' => $appointment->status,
                    'date_heure' => $appointment->date_heure->toIso8601String(), // This will include timezone info
                    'doctor' => $appointment->medecin,
                    'patient_id' => $appointment->patient_id
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $appointments,
            'meta' => [
                'patient_id' => $patient->id,
                'current_time' => now()->utc()->toIso8601String(), // Show UTC current time
                'timezone' => config('app.timezone'), // Application's configured timezone
                'query_time_window' => 'strictly future (UTC)' // Clarify the query window
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error("Appointments Error - Patient ". ($patient->id ?? 'N/A') .": ".$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve appointments',
            'error' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
}
}