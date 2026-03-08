<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon; // Make sure Carbon is imported

class PatientController extends Controller
{
  public function index(Request $request) // Added Request $request
    {
        $query = Patient::with('dossierMedical')
                        ->orderBy('created_at', 'desc');

        // Search by CIN
        if ($request->filled('cin_search')) {
            $query->where('cin', 'like', '%' . $request->input('cin_search') . '%');
        }

        // Filter by Genre
        if ($request->filled('genre_filter') && in_array($request->input('genre_filter'), ['H', 'F'])) {
            $query->where('genre', $request->input('genre_filter'));
        }

        // Filter by Blood Group (groupe_sanguin)
        if ($request->filled('blood_group_filter')) {
            $bloodGroup = $request->input('blood_group_filter');
            $query->whereHas('dossierMedical', function ($q) use ($bloodGroup) {
                $q->where('groupe_sanguin', $bloodGroup);
            });
        }

        $patients = $query->paginate(10)->appends($request->except('page')); // Append all search/filter parameters

        // Fetch distinct blood groups for the filter dropdown
        // This might need adjustment if dossierMedical relationship isn't always present or if you have many
        $availableBloodGroups = Patient::whereHas('dossierMedical', function($q) {
                                    $q->whereNotNull('groupe_sanguin');
                                })
                                ->with('dossierMedical')
                                ->get()
                                ->pluck('dossierMedical.groupe_sanguin')
                                ->unique()
                                ->sort()
                                ->values()
                                ->all();


        return view('medecin.patient.index', compact('patients', 'availableBloodGroups'));
    }

public function show(Patient $patient)
{
    $patient->load([
        'dossierMedical',
        'regimeAlimentaires.medecin', // Add this to load regimes with their medecin
        'documentMedicals',
        'patientBloodPressures',
        'patientBloodSugars',
        'medicalRecords.medecin',
        'consultations.medecin',
    ]);
    
    $consultations = $patient->consultations()
        ->with('medecin')
        ->orderBy('created_at', 'desc')
        ->paginate(5); 
        
    $medicalRecords = $patient->medicalRecords()
        ->with('medecin')
        ->orderBy('created_at', 'desc')
        ->paginate(5);
        
    $documents = $patient->documentMedicals()
        ->with('medecin');

    // Prepare chart data
    $bloodPressureData = $this->prepareBloodPressureData('7days', $patient);
    $bloodSugarData = $this->prepareBloodSugarData('7days', $patient);

    return view('medecin.patient.show', compact(
        'patient',
        'bloodPressureData',
        'bloodSugarData',
        'consultations',
        'medicalRecords',
        'documents'
    ));
}

    public function getBloodPressureData(Request $request, Patient $patient)
    {
        $timeRange = $request->get('timeRange', 'today'); // Default to '7days' if not specified

        // Use the helper method to get processed data for blood pressure
        $processedData = $this->prepareBloodPressureData($timeRange, $patient);

        return response()->json($processedData);
    }

      public function getBloodSugarsForCalendarTable(Patient $patient)
    {
        // Fetch all blood sugars for the patient, ordered by date and time
        $allBloodSugars = $patient->patientBloodSugars()
            ->orderBy('measurement_at', 'asc')
            ->get();

        // Group the blood sugars by date
        $bloodSugarsGroupedByDate = $allBloodSugars->groupBy(function ($item) {
            return Carbon::parse($item->measurement_at)->format('Y-m-d');
        });

        return view('medecin.patient.AllbloodSugarData', [
            'patient' => $patient,
            'bloodSugarsGroupedByDate' => $bloodSugarsGroupedByDate,
        ]);
    }
    private function prepareBloodPressureData(string $timeRange, Patient $patient)
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $chartType = 'bar'; // Default chart type for aggregated data
        $dateFormat = 'D, M d'; // Default date format for categories

        switch ($timeRange) {
            case 'yesterday':
                $startDate = Carbon::now()->subDay()->startOfDay();
                $endDate = Carbon::now()->subDay()->endOfDay();
                $chartType = 'line'; // For individual readings on a single day
                $dateFormat = 'HH:mm'; // Format for time on a single day
                break;
            case 'today':
                $chartType = 'line'; // For individual readings on a single day
                $dateFormat = 'HH:mm'; // Format for time on a single day
                break;
            case '7days':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                $dateFormat = 'D, M d';
                break;
            case '14days':
                $startDate = Carbon::now()->subDays(13)->startOfDay();
                $dateFormat = 'M d'; // Shorter format for more days
                break;
            case '30days':
                $startDate = Carbon::now()->subDays(29)->startOfDay();
                $dateFormat = 'M d';
                break;
            case '90days':
                $startDate = Carbon::now()->subDays(89)->startOfDay();
                $dateFormat = 'M d';
                break;
            default:
                $startDate = Carbon::now()->subDays(6)->startOfDay(); // Fallback to 7 days
                $dateFormat = 'D, M d';
                break;
        }

        // Fetch and filter blood pressure records within the calculated range
        $bloodPressuresFiltered = $patient->patientBloodPressures
            ->where('measurement_at', '>=', $startDate)
            ->where('measurement_at', '<=', $endDate)
            ->sortBy('measurement_at');

        $systolicData = [];
        $diastolicData = [];
        $categories = [];

        if ($chartType === 'line') {
            // For 'yesterday' or 'today', show individual readings as a line chart
            foreach ($bloodPressuresFiltered as $reading) {
                $systolicData[] = (int) $reading->systolic;
                $diastolicData[] = (int) $reading->diastolic;
                $categories[] = Carbon::parse($reading->measurement_at)->format($dateFormat);
            }
        } else {
            // For aggregated periods (7, 30, 90 days), group by day and calculate averages
            $bloodPressuresGroupedByDay = $bloodPressuresFiltered->groupBy(function($reading) {
                return Carbon::parse($reading->measurement_at)->format('Y-m-d');
            });

            $currentDate = $startDate->copy();
            while ($currentDate->lessThanOrEqualTo($endDate)) {
                $formattedDate = $currentDate->format('Y-m-d');
                $categories[] = $currentDate->format($dateFormat); // Use configured date format for labels

                $dailyReadings = $bloodPressuresGroupedByDay->get($formattedDate);

                if ($dailyReadings && $dailyReadings->isNotEmpty()) {
                    $systolicData[] = round($dailyReadings->avg('systolic'));
                    $diastolicData[] = round($dailyReadings->avg('diastolic'));
                } else {
                    $systolicData[] = 0; // Or null, depending on ApexCharts' preference for gaps
                    $diastolicData[] = 0; // Or null
                }
                $currentDate->addDay();
            }
        }

        return [
            'systolicDailyAvg' => $systolicData,
            'diastolicDailyAvg' => $diastolicData,
            'dailyCategories' => $categories,
            'chartType' => $chartType 
        ];
    } 
           
          
    
    public function getBloodSugarData(Request $request, Patient $patient)
    {
        $timeRange = $request->get('timeRange', '7days'); // Default to '7days' if not specified

        // Use the helper method to get processed data for blood sugar
        $processedData = $this->prepareBloodSugarData($timeRange, $patient);

        return response()->json($processedData);
    }

    /**
     * Helper method to prepare blood sugar data for chart.
     * @param string $timeRange e.g., 'yesterday', 'today', '7days', '30days', '90days'
     * @param Patient $patient
     * @return array
     */
    private function prepareBloodSugarData(string $timeRange, Patient $patient)
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $dateFormat = 'HH:mm'; // Default format for individual readings (for today/yesterday)

        switch ($timeRange) {
            case 'yesterday':
                $startDate = Carbon::now()->subDay()->startOfDay();
                $endDate = Carbon::now()->subDay()->endOfDay();
                break;
            case 'today':
                // Already set for today
                break;
            case '7days':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                $dateFormat = 'M d';
                break;
            case '14days':
                $startDate = Carbon::now()->subDays(13)->startOfDay();
                $dateFormat = 'M d';
                break;
            case '30days':
                $startDate = Carbon::now()->subDays(29)->startOfDay();
                $dateFormat = 'M d';
                break;
            case '90days':
                $startDate = Carbon::now()->subDays(89)->startOfDay();
                $dateFormat = 'M d';
                break;
            default:
                $startDate = Carbon::now()->subDays(6)->startOfDay(); // Fallback to 7 days
                $dateFormat = 'M d';
                break;
        }

        $bloodSugarsFiltered = $patient->patientBloodSugars
            ->where('measurement_at', '>=', $startDate)
            ->where('measurement_at', '<=', $endDate)
            ->sortBy('measurement_at');

        $sugarLevels = [];
        $measurementTimes = [];

        // For multi-day ranges, you might want daily averages to simplify the chart
        if (in_array($timeRange, ['7days', '14days', '30days', '90days'])) {
            $bloodSugarsGroupedByDay = $bloodSugarsFiltered->groupBy(function($reading) {
                return Carbon::parse($reading->measurement_at)->format('Y-m-d');
            });

            $currentDate = $startDate->copy();
            while ($currentDate->lessThanOrEqualTo($endDate)) {
                $formattedDate = $currentDate->format('Y-m-d');
                $measurementTimes[] = $currentDate->format($dateFormat); // Use configured date format for labels

                $dailyReadings = $bloodSugarsGroupedByDay->get($formattedDate);

                if ($dailyReadings && $dailyReadings->isNotEmpty()) {
                    $sugarLevels[] = round($dailyReadings->avg('value'), 2); // Average blood sugar for the day
                } else {
                    $sugarLevels[] = 0; // Or null
                }
                $currentDate->addDay();
            }
        } else {
            // For 'yesterday' or 'today', show individual readings
            foreach ($bloodSugarsFiltered as $reading) {
                $sugarLevels[] = (float) $reading->value;
                $measurementTimes[] = Carbon::parse($reading->measurement_at)->format($dateFormat);
            }
        }

        return [
            'sugarLevels' => $sugarLevels,
            'measurementTimes' => $measurementTimes,
        ];
            
    }

    /**
     * Displays the patient's medical history.
     */
    public function medicalHistory(Patient $patient)
    {
        $history = $patient->load([
            'consultations.medecin', // Nested eager loading
            'dossierMedical',
            'regimeAlimentaires',
            'documentMedicals'
        ]);

        return view('patients.medical-history', compact('history'));
    }


    public function register(Request $request)
{
    $request->headers->set('Accept', 'application/json');
    // 1. Validation Block
    $request->validate([
        'cin' => 'required|unique:patients',
        'nom' => 'required',
        'prenom' => 'required',
        'date_naissance' => 'required|date',
        'genre' => 'required',
        'email' => 'required|email|unique:patients',
        'password' => ['required', 'confirmed'],
        'telephone' => 'required', // Keep this without regex for now
        'adresse' => 'required'
    ]);
    // 2. Patient Creation Block
    $patient = Patient::create([
        'cin' => $request->cin,
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'date_naissance' => $request->date_naissance,
        'email' => $request->email,
        'password' => bcrypt($request->password), // Hash the password
        'genre' => $request->genre,
        'telephone' => $request->telephone,
        'adresse' => $request->adresse,
    ]);
    // 3. Token Generation Block
    
    $token = $patient->createToken('patient_token')->plainTextToken;

    return response()->json([
        'patient' => $patient,
        'token' => $token,
        'message' => 'Registration successful'
    ]);
}
public function login(Request $request)
{
    $request->headers->set('Accept', 'application/json');
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (auth('patient')->attempt($credentials)) {
        $patient = Patient::where('email', $request->email)->first();
        return response()->json([
            'message' => 'Login successful',
            'patient' => $patient,
            'token' => $patient->createToken('patient_token')->plainTextToken,
        ]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
}
}