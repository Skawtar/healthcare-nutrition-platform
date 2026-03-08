<?php
// app/Http/Controllers/Api/PatientBloodPressureController.php
namespace App\Http\Controllers\API;

use App\Models\PatientBloodPressure;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PatientBloodPressureController extends Controller
{
    public function index(Request $request)
    {
        $pressures = PatientBloodPressure::where('patient_id', $request->user()->id)
            ->orderBy('measurement_at', 'desc')
            ->get();

        return response()->json($pressures);
    }
public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'systolic' => 'required|integer|min:50|max:250',
            'diastolic' => 'required|integer|min:30|max:150',
            'measurement_at' => 'required|date|before_or_equal:now',
            'notes' => 'nullable|string|max:500',
        ]);

        // Validate diastolic < systolic
        if ($validated['diastolic'] >= $validated['systolic']) {
            throw ValidationException::withMessages([
                'diastolic' => 'Diastolic must be less than systolic'
            ]);
        }

        // Create the blood pressure record
        $bloodPressure = PatientBloodPressure::create([
            'patient_id' => $request->user()->id,
            'systolic' => $validated['systolic'],
            'diastolic' => $validated['diastolic'],
            'measurement_at' => $validated['measurement_at'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $bloodPressure,
            'message' => 'Blood pressure recorded successfully'
        ], 201);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $e->errors()
        ], 422);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}
    public function show(PatientBloodPressure $bloodPressure)
    {
        return response()->json($bloodPressure);
    }

    public function update(Request $request, PatientBloodPressure $bloodPressure)
    {
        $validated = $request->validate([
            'systolic' => 'required|integer|min:50|max:250',
            'diastolic' => 'required|integer|min:30|max:150',
            'measurement_at' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $bloodPressure->update($validated);

        return response()->json($bloodPressure);
    }

    public function destroy(PatientBloodPressure $bloodPressure)
    {
        $bloodPressure->delete();
        return response()->json(null, 204);
    }
}