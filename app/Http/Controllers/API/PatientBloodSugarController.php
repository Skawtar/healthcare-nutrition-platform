<?php
// app/Http/Controllers/Api/PatientBloodSugarController.php
namespace App\Http\Controllers\API;

use App\Models\PatientBloodSugar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PatientBloodSugarController extends Controller
{
    public function index(Request $request)
    {
        $sugars = PatientBloodSugar::where('patient_id', $request->user()->id)
            ->orderBy('measurement_at', 'desc')
            ->get();

        return response()->json($sugars);
    }

 public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'value' => [
                'required',
                'numeric',
                'min:50',
                'max:500',
                function ($attribute, $value, $fail) use ($request) {
                    // Additional validation based on measurement type
                    $type = $request->input('measurement_type');
                    
                    if ($type === 'fasting' && $value > 126) {
                        $fail('Fasting blood sugar should be below 126 mg/dL');
                    }
                    
                    if ($type === 'after_meal' && $value > 200) {
                        $fail('Postprandial blood sugar should be below 200 mg/dL');
                    }
                }
            ],
            'measurement_type' => 'required|in:fasting,after_meal,random',
            'measurement_at' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) > time()) {
                        $fail('Measurement date cannot be in the future');
                    }
                }
            ],
            'notes' => 'nullable|string|max:500',
        ]);

        // Create the blood sugar record
        $sugar = PatientBloodSugar::create([
            'patient_id' => $request->user()->id,
            'value' => $validated['value'],
            'measurement_type' => $validated['measurement_type'],
            'measurement_at' => $validated['measurement_at'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $sugar,
            'message' => 'Blood sugar measurement recorded successfully',
            'interpretation' => $this->interpretReading(
                $validated['value'],
                $validated['measurement_type']
            )
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

protected function interpretReading($value, $type)
{
    if ($type === 'fasting') {
        if ($value < 100) return 'Normal fasting glucose';
        if ($value < 126) return 'Prediabetes';
        return 'Diabetes';
    }
    
    if ($type === 'after_meal') {
        if ($value < 140) return 'Normal postprandial';
        if ($value < 200) return 'Prediabetes';
        return 'Diabetes';
    }
    
    if ($value < 140) return 'Normal random glucose';
    if ($value < 200) return 'Potential hyperglycemia';
    return 'Likely diabetes';
}

    public function show(PatientBloodSugar $bloodSugar)
    {
        return response()->json($bloodSugar);
    }

    public function update(Request $request, PatientBloodSugar $bloodSugar)
    {
        $validated = $request->validate([
            'value' => 'required|numeric|min:50|max:500',
            'measurement_type' => 'required|in:fasting,after_meal,random',
            'measurement_at' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $bloodSugar->update($validated);

        return response()->json($bloodSugar);
    }

    public function destroy(PatientBloodSugar $bloodSugar)
    {
        $bloodSugar->delete();
        return response()->json(null, 204);
    }
}