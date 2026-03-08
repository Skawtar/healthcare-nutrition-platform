@extends('layouts.app')

@section('content')
<div class="ml-64 p-6 bg-gray-50 min-h-screen"> 
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Patient Profile Card (lg:col-span-1) --}}
            <div class="lg:col-span-1">
                <div class="p-6 bg-white rounded-xl shadow-lg overflow-hidden h-fit border border-blue-100"> {{-- Blue-themed border --}}
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-28 h-28 rounded-full bg-blue-100 mb-4 overflow-hidden flex items-center justify-center border-4 border-blue-300">
                            <img class="w-full h-full object-cover rounded-full"
                                src="{{ $patient->photo ? asset('storage/'.$patient->photo) : 'https://ui-avatars.com/api/?name='.urlencode($patient->nom.' '.$patient->prenom).'&background=A7D9F8&color=1F2937' }}" {{-- Lighter blue background for avatar API --}}
                                alt="Patient Photo">
                        </div>
                        <h1 class="text-3xl font-extrabold text-gray-900 mb-1">{{ $patient->prenom }} {{ $patient->nom }}</h1>
                        <p class="text-blue-600 text-lg font-semibold">CIN: {{ $patient->cin }}</p>
                    </div>

                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-blue-700 border-b-2 border-blue-200 pb-3 mb-4">Personal Information</h2>
                        <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                            <div>
                                <p class="text-sm text-gray-500">Date of Birth</p>
                                <p class="font-semibold text-gray-800">
                                    {{ $patient->date_naissance ? \Carbon\Carbon::parse($patient->date_naissance)->format('M d, Y') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Gender</p>
                                <p class="font-semibold text-xl">
                                    @if(($patient->genre ?? '') == 'F' || ($patient->genre ?? '') == 'Female')
                                        <span class="text-pink-500">♀ Female</span>
                                    @elseif(($patient->genre ?? '') == 'H' || ($patient->genre ?? '') == 'Male')
                                        <span class="text-blue-500">♂ Male</span>
                                    @else
                                        <span class="text-gray-400">— N/A</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Height</p>
                                <p class="font-semibold text-gray-800">{{ optional($patient->dossierMedical)->taille ? $patient->dossierMedical->taille . ' cm' : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Weight</p>
                                <p class="font-semibold text-gray-800">{{ optional($patient->dossierMedical)->poids ? $patient->dossierMedical->poids . ' kg' : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-blue-700 border-b-2 border-blue-200 pb-3 mb-4">Medical Information</h2>

                        <div class="mb-3">
                            <p class="text-sm text-gray-500">Blood Group</p>
                            <p class="font-semibold ml-0 mt-1 px-3 py-1 text-sm rounded-full bg-red-100 text-red-800 inline-flex items-center border border-red-200">
                                {{ optional($patient->dossierMedical)->groupe_sanguin ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-2">Allergies</p>
                            @if(optional($patient->dossierMedical)->allergies)
                                @php
                                    $allergies = trim($patient->dossierMedical->allergies, '[]"');
                                    $allergyList = preg_split('/[\s,]+/', $allergies, -1, PREG_SPLIT_NO_EMPTY);
                                @endphp

                                @if(count($allergyList) > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($allergyList as $allergy)
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full border border-yellow-200">
                                                {{ trim($allergy, '"') }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="font-medium text-gray-500 italic">No known allergies</p>
                                @endif
                            @else
                                <p class="font-medium text-gray-500 italic">No known allergies</p>
                            @endif
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-2">Medical History</p>
                            @if(optional($patient->dossierMedical)->antecedents)
                                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100"> {{-- Light blue background with border --}}
                                    @if(is_array(json_decode($patient->dossierMedical->antecedents, true)))
                                        <ul class="list-disc pl-5 space-y-1 text-gray-700">
                                            @foreach(json_decode($patient->dossierMedical->antecedents) as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-gray-700">{{ $patient->dossierMedical->antecedents }}</p>
                                    @endif
                                </div>
                            @else
                                <p class="font-medium text-gray-500 italic">No significant medical history</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 mb-2">Current Treatments</p>
                            @if(optional($patient->dossierMedical)->traitements)
                                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100"> {{-- Light blue background with border --}}
                                    @if(is_array(json_decode($patient->dossierMedical->traitements, true)))
                                        <ul class="list-disc pl-5 space-y-1 text-gray-700">
                                            @foreach(json_decode($patient->dossierMedical->traitements) as $treatment)
                                                <li>{{ $treatment }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-gray-700">{{ $patient->dossierMedical->traitements }}</p>
                                    @endif
                                </div>
                            @else
                                <p class="font-medium text-gray-500 italic">No current treatments</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Upcoming Consultations Section (blue soft themed with scroll) --}}
                <div class="bg-white p-6 rounded-xl shadow-lg mt-8 border border-blue-100"> {{-- Blue-themed border --}}
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-blue-700">Upcoming Consultations</h3> {{-- Blue heading --}}
                       
                    </div>

                    {{-- Scrollable area for consultations --}}
                    <div class="space-y-4 max-h-96 overflow-y-auto pr-2"> {{-- Added max-h-96 and overflow-y-auto, pr-2 for scrollbar spacing --}}
                        @if($consultations->whereIn('status', ['confirmed', 'pending'])->count() > 0)
                            @foreach($consultations->whereIn('status', ['confirmed', 'pending'])->sortBy('date_heure') as $consultation)
                                <div class="p-4  hover:bg-blue-50 rounded-lg border border-blue-200 transition-all duration-200 ease-in-out"> {{-- Blue soft consultation cards --}}
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-lg text-gray-900">{{ $consultation->motif }}</h4>
                                            <div class="flex items-center mt-2 text-sm text-gray-600">
                                                <svg class="w-5 h-5 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Blue icon --}}
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>
                                                    {{ $consultation->date_heure->format('D, M d, Y, H:i') }}
                                                    @if($consultation->duration)
                                                        - {{ $consultation->date_heure->addMinutes($consultation->duration)->format('H:i') }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex items-center mt-1 text-sm text-gray-600">
                                                <svg class="w-5 h-5 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Blue icon --}}
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <span>Dr. {{ $consultation->medecin->name ?? 'N/A' }}</span>
                                            </div>
                                       
                                                                                                                             
                                             <div>
                                            <span class="mx-1 text-blue-400 font-semibold">•</span>
                                             <span class="text-blue-500">{{ $consultation->medecin->specialite_code ?? 'General Practitioner' }}</span>

                                             </div>    

                                        </div>
                                        <span class="text-xs px-3 py-1 rounded-full font-semibold capitalize
                                            @if($consultation->status === 'confirmed') bg-blue-100 text-blue-800 border border-blue-200 {{-- Blue for confirmed --}}
                                            @elseif($consultation->status === 'pending') bg-yellow-100 text-yellow-800 border border-yellow-200 {{-- Yellow for pending --}}
                                            @endif">
                                            {{ $consultation->status }}
                                        </span>
                                    </div>
                                   
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-8 bg-blue-50 rounded-lg border border-blue-200"> {{-- Blue soft for no consultations --}}
                                <svg class="mx-auto h-14 w-14 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <h4 class="mt-3 text-lg font-semibold text-gray-900">No upcoming consultations</h4>
                                <p class="mt-1 text-sm text-gray-600">Schedule a new consultation for this patient.</p>
                                <div class="mt-6">
                                    <a href="#" class="inline-flex items-center px-5 py-2 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"> {{-- Blue button --}}
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        New Consultation
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($consultations->count() > 2)
                        <div class="mt-6 pt-4 border-t border-blue-100 text-center"> {{-- Blue border --}}
                            <a href="" class="text-md text-blue-600 hover:text-blue-800 font-semibold">View all consultations</a> {{-- Blue link --}}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: BMI, Charts, Documents, Medical Records (lg:col-span-2) --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- BODY MASS INDEX Section (Reverted to original green/yellow/red theme) --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border border-indigo-100"> {{-- Indigo-themed border --}}
                    <h3 class="text-xl font-bold text-indigo-700 mb-4">BODY MASS INDEX</h3>

                    @php
                        $bmi = null;
                        $bmiStatus = 'N/A';
                        $bmiOverallColorClass = 'bg-gray-100 text-gray-800 border-gray-300'; // Default for the main BMI result badge

                        // Original colors for the BMI ranges
                        $bmiRangeColors = [
                            'UNDERWEIGHT' => 'bg-blue-50 text-blue-700 border-blue-100', // Kept blue as it's a "soft" blue already
                            'NORMAL' => 'bg-green-50 text-green-700 border-green-100',
                            'OVERWEIGHT' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                            'OBESE' => 'bg-orange-50 text-orange-700 border-orange-100',
                            'EXTREMELY OBESE' => 'bg-red-50 text-red-700 border-red-100',
                            'N/A' => 'bg-gray-50 text-gray-700 border-gray-100',
                        ];

                        if(isset($patient->dossierMedical->taille) && isset($patient->dossierMedical->poids) && $patient->dossierMedical->taille > 0) {
                            $height = $patient->dossierMedical->taille / 100; // Convert cm to meters
                            $weight = $patient->dossierMedical->poids;

                            if ($height > 0) {
                                $bmi = $weight / ($height * $height);

                                if ($bmi < 18.5) {
                                    $bmiStatus = 'UNDERWEIGHT';
                                    $bmiOverallColorClass = 'bg-blue-500 text-white'; // Darker blue for main result
                                } elseif ($bmi >= 18.5 && $bmi < 25) {
                                    $bmiStatus = 'NORMAL';
                                    $bmiOverallColorClass = 'bg-green-500 text-white'; // Darker green for main result
                                } elseif ($bmi >= 25 && $bmi < 30) {
                                    $bmiStatus = 'OVERWEIGHT';
                                    $bmiOverallColorClass = 'bg-yellow-500 text-white'; // Darker yellow for main result
                                } elseif ($bmi >= 30 && $bmi < 35) {
                                    $bmiStatus = 'OBESE';
                                    $bmiOverallColorClass = 'bg-orange-500 text-white'; // Darker orange for main result
                                } else {
                                    $bmiStatus = 'EXTREMELY OBESE';
                                    $bmiOverallColorClass = 'bg-red-500 text-white'; // Darker red for main result
                                }
                            }
                        }
                    @endphp

                    <div class="grid grid-cols-5 gap-3 text-center mb-6">
                        <div class="p-3 rounded-lg border {{ $bmiRangeColors['UNDERWEIGHT'] }} transition-all duration-200">
                            <p class="text-sm font-bold">&lt;18.5</p>
                            <p class="text-xs mt-1">UNDERWEIGHT</p>
                        </div>

                        <div class="p-3 rounded-lg border {{ $bmiRangeColors['NORMAL'] }} transition-all duration-200">
                            <p class="text-sm font-bold">18.5-24.9</p>
                            <p class="text-xs mt-1">NORMAL</p>
                        </div>

                        <div class="p-3 rounded-lg border {{ $bmiRangeColors['OVERWEIGHT'] }} transition-all duration-200">
                            <p class="text-sm font-bold">25-29.9</p>
                            <p class="text-xs mt-1">OVERWEIGHT</p>
                        </div>

                        <div class="p-3 rounded-lg border {{ $bmiRangeColors['OBESE'] }} transition-all duration-200">
                            <p class="text-sm font-bold">30-34.9</p>
                            <p class="text-xs mt-1">OBESE</p>
                        </div>

                        <div class="p-3 rounded-lg border {{ $bmiRangeColors['EXTREMELY OBESE'] }} transition-all duration-200">
                            <p class="text-sm font-bold">35+</p>
                            <p class="text-xs mt-1">EXTREMELY OBESE</p>
                        </div>
                    </div>

                    @if($bmi !== null)
                        <div class="mt-6 text-center">
                            <p class="text-base text-gray-700">Current BMI</p>
                            <p class="text-4xl font-extrabold text-gray-900 mt-2">{{ number_format($bmi, 1) }}</p>
                            <span class="inline-block mt-3 px-4 py-1.5 rounded-full text-base font-semibold {{ $bmiOverallColorClass }} shadow-md">
                                {{ $bmiStatus }}
                            </span>
                        </div>
                    @else
                        <div class="mt-6 text-center py-4 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-base text-gray-600">BMI calculation not available.</p>
                            <p class="text-sm text-gray-500 mt-1">Please ensure height and weight are provided in the patient's medical file.</p>
                        </div>
                    @endif
                </div>

                {{-- Charts Section (purple themed) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- These partials should internally use colors that align or are neutral --}}
                    @include('medecin.patient.BloodPressureChart', ['bloodPressureData' => $bloodPressureData])
                    @include('medecin.patient.BloodSugarChart', ['bloodSugarData' => $bloodSugarData])
                </div>

                {{-- Documents Section (orange themed) --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border border-orange-100">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-orange-700">Documents</h3>
        <a href="{{ route('documents.create', ['patient' => $patient->id])}}" 
           class="text-sm text-orange-600 hover:text-orange-800 flex items-center font-semibold transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add New
        </a>
    </div>

    <div class="space-y-3">
        @forelse($documents as $document)
            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-200 transition-all hover:bg-orange-100 hover:shadow-sm group">
                <div class="flex items-center min-w-0">
                    <div class="p-2 mr-3 rounded-lg bg-orange-100 text-orange-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ $document->nom_fichier ?? 'Document' }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $document->created_at->format('M d, Y') }} • 
                            {{ $document->size ? round($document->size / 1024, 1) . ' KB' : 'N/A' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($document->file_path && Storage::exists($document->file_path))
                        <a href="{{ route('documents.download', $document->id) }}" 
                           class="px-3 py-1 text-sm font-semibold text-orange-600 hover:text-orange-800 transition-colors"
                           title="Download">
                            Download
                        </a>
                        <button class="p-1 text-gray-400 hover:text-gray-600 transition-colors document-action"
                                data-id="{{ $document->id }}"
                                data-action="delete"
                                title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    @else
                        <span class="text-xs text-red-500 px-2">File missing</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-6 bg-gray-50 rounded-lg border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-600">No documents available.</p>
                <a href="{{ route('documents.create') }}" class="mt-3 inline-flex items-center text-sm text-orange-600 hover:text-orange-800 font-medium">
                    Upload your first document
                </a>
            </div>
        @endforelse
    </div>

    @if($documents->count() > 3)
        <div class="mt-6 pt-4 border-t border-orange-100 text-center">
            <a href="{{ route('documents.index') }}" 
               class="text-md text-orange-600 hover:text-orange-800 font-semibold inline-flex items-center">
                View all documents
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.querySelectorAll('.document-action').forEach(button => {
    button.addEventListener('click', function() {
        const documentId = this.dataset.id;
        const action = this.dataset.action;
        
        if (action === 'delete') {
            if (confirm('Are you sure you want to delete this document?')) {
                fetch(`/documents/${documentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error deleting document');
                    }
                });
            }
        }
    });
});
</script>
@endpush

               {{-- Medical Records Section (rose themed) --}}
<div class="bg-white p-6 rounded-xl shadow-lg border border-rose-100 dark:bg-gray-800 dark:border-rose-900">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-rose-700 dark:text-rose-300">Medical Records</h3>
        {{-- Updated "See all" link to point to the route for all medical records of this patient --}}
        <a href="{{ route('patients.medicalRecords.all', $patient->id) }}" class="text-sm text-rose-600 hover:text-rose-800 font-semibold dark:text-rose-400 dark:hover:text-rose-200">See all</a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-rose-50 dark:bg-rose-900"> {{-- Light rose header for table --}}
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-rose-700 uppercase tracking-wider dark:text-rose-300">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-rose-700 uppercase tracking-wider dark:text-rose-300">Notes</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-rose-700 uppercase tracking-wider dark:text-rose-300">Specialist</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 dark:bg-gray-800 dark:divide-gray-700">
                @forelse($medicalRecords as $record)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ \Carbon\Carbon::parse($record->record_start_date)->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ Str::limit($record->notes ?? 'N/A', 70) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">Dr. {{ $record->medecin->name ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-6 whitespace-nowrap text-base text-gray-500 text-center bg-gray-50 dark:bg-gray-700 dark:text-gray-400">No medical records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

                {{-- Regime Section (green themed) --}}
<div class="bg-white p-6 rounded-xl shadow-lg border border-emerald-100"> {{-- Green-themed border --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-emerald-700">Nutritional Regimes</h3>
        <a href="{{ route('regimes.create', ['patient_id' => $patient->id]) }}" 
           class="text-sm text-emerald-600 hover:text-emerald-800 flex items-center font-semibold">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add New Regime
        </a>
    </div>

    <div class="space-y-4">
        @forelse($patient->regimeAlimentaires as $regime)
            <div class="p-4 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition-all duration-200 ease-in-out">
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-bold text-lg text-gray-900">{{ $regime->title }}</h4>
                        <div class="flex items-center mt-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>
                                {{ \Carbon\Carbon::parse($regime->start_date)->format('M d, Y') }}
                                @if($regime->end_date)
                                    - {{ \Carbon\Carbon::parse($regime->end_date)->format('M d, Y') }}
                                @endif
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-gray-700">
                            <p class="font-semibold">Description:</p>
                            <p>{{ $regime->description }}</p>
                        </div>
                        <div class="mt-2 text-sm text-gray-700">
                            <p class="font-semibold">Foods to Include:</p>
                            <p>{{ $regime->aliments_a_eviter }}</p>
                        </div>
                        <div class="mt-2 text-sm text-gray-700">
                            <p class="font-semibold">Foods to Avoid:</p>
                            <p>{{ $regime->aliments_a_privilegier }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-xs px-3 py-1 rounded-full font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            {{ $regime->type }}
                        </span>
                        <div class="mt-2 text-xs text-gray-500">
                            <p>Prescribed by:</p>
                            <p class="font-medium">Dr. {{ $regime->medecin->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex space-x-2">
                    <button class="text-xs bg-red-50 text-red-600 px-3 py-1 rounded-full hover:bg-red-100 flex items-center font-medium transition-colors">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Delete
                    </button>
                    <button class="text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full hover:bg-emerald-200 flex items-center font-medium transition-colors">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Details
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center py-8 bg-emerald-50 rounded-lg border border-emerald-200">
                <svg class="mx-auto h-14 w-14 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <h4 class="mt-3 text-lg font-semibold text-gray-900">No nutritional regimes found</h4>
                <p class="mt-1 text-sm text-gray-600">Add a new nutritional regime for this patient.</p>
                <div class="mt-6">
                    <a href="{{ route('regimes.create', ['patient_id' => $patient->id]) }}" 
                       class="inline-flex items-center px-5 py-2 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Regime
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    @if($patient->regimeAlimentaires->count() > 2)
        <div class="mt-6 pt-4 border-t border-emerald-100 text-center">
            <a href="{{ route('regimes.index', ['patient_id' => $patient->id]) }}" 
               class="text-md text-emerald-600 hover:text-emerald-800 font-semibold">View all regimes</a>
        </div>
    @endif
</div>
            </div>
        </div>
    </div>
</div>
@endsection