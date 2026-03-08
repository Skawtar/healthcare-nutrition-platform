@extends('layouts.app')

@section('content')
<div class="ml-64 p-6 bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4">

       <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-100">
    <div class="flex justify-between items-start flex-wrap gap-6">
        <div class="flex items-start space-x-6">
            <div class="flex-shrink-0">
                <img class="h-24 w-24 rounded-full object-cover border-3 border-blue-500 shadow-md"
                     src="{{ $consultation->patient->profile_pic ?? 'https://ui-avatars.com/api/?name='.urlencode($consultation->patient->prenom.'+'.$consultation->patient->nom).'&background=random' }}"
                     alt="Profile picture">
            </div>

            <div>
                <div class="flex items-center space-x-4 mb-2">
                    {{-- Changed from text-4xl font-extrabold to text-3xl font-bold for a slightly more formal visual --}}
                    <h1 class="text-3xl font-bold text-gray-900 leading-tight">{{ $consultation->patient->prenom }} {{ $consultation->patient->nom }}</h1>
                    <span class="text-sm font-semibold px-4 py-1.5 rounded-full bg-blue-100 text-blue-800 tracking-wide">Patient ID: {{ $consultation->patient->id }}</span>
                </div>
                <p class="text-gray-700 text-xl mb-1">{{ $consultation->patient->email }}</p>
                <p class="text-gray-600 text-base">{{ $consultation->patient->telephone }}</p>
            </div>
        </div>

        <div class="text-right text-gray-600 text-sm space-y-2">
            <p><span class="font-semibold text-gray-800">Date of Birth:</span>
                {{ $consultation->patient->date_naissance ? $consultation->patient->date_naissance->format('d M Y') : 'N/A' }}
            </p>
            <p><span class="font-semibold text-gray-800">Address:</span> {{ $consultation->patient->adresse ?? 'N/A' }}</p>
            <p><span class="font-semibold text-gray-800">Registration Date:</span>
                {{ $consultation->patient->created_at ? $consultation->patient->created_at->format('M j, Y') : 'N/A' }}
            </p>
        </div>
    </div>
</div>

        <div class="flex flex-col lg:flex-row gap-6">
            <div class="w-full lg:w-1/2 space-y-6">

               <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
    <div class="flex justify-between items-center mb-5 border-b pb-3 border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900">Medical Information</h2>
        {{-- Added "View Full Record" link --}}
        <a href="{{ route('patients.show', $consultation->patient->id) }}"
           class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors duration-200">
            View Full Record &rarr;
        </a>
    </div>

    <div class="mb-6">
        <p class="text-lg font-semibold text-gray-700 mb-3">Blood Group</p>
        <span class="inline-flex items-center px-5 py-2.5 text-md rounded-full bg-red-100 text-red-800 font-bold tracking-wide">
            {{ $consultation->patient->dossierMedical->groupe_sanguin ?? 'Unknown' }}
        </span>
    </div>

    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">Allergies</h3>
        @php
            $allergies = $consultation->patient->dossierMedical->allergies ?? [];
            if (is_string($allergies)) {
                $allergies = json_decode($allergies, true);
            }
        @endphp

        @if(!empty($allergies) && is_array($allergies))
            <div class="flex flex-wrap gap-2.5">
                @foreach($allergies as $allergy)
                <span class="px-3.5 py-1.5 bg-yellow-100 text-yellow-800 text-sm rounded-full font-medium">
                    {{ $allergy }}
                </span>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 italic text-sm">No known allergies recorded.</p>
        @endif
    </div>

    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">Medical History</h3>
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
            <ul class="list-disc pl-5 space-y-2 text-gray-700">
                @php
                    $antecedents = $consultation->patient->dossierMedical->antecedents ?? [];
                    if (is_string($antecedents)) {
                        $antecedents = json_decode($antecedents, true);
                    }
                @endphp

                @if(!empty($antecedents) && is_array($antecedents))
                    @foreach($antecedents as $history)
                    <li>{{ $history }}</li>
                    @endforeach
                @else
                    <li class="italic text-gray-600 text-sm">No medical history recorded.</li>
                @endif
            </ul>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold text-gray-700 mb-3">Current Treatments</h3>
        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
            <ul class="list-disc pl-5 space-y-2 text-gray-700">
                @php
                    $treatments = $consultation->patient->dossierMedical->traitements ?? [];
                    if (is_string($treatments)) {
                        $treatments = json_decode($treatments, true);
                    }
                @endphp

                @if(!empty($treatments) && is_array($treatments))
                    @foreach($treatments as $treatment)
                    <li>{{ $treatment }}</li>
                    @endforeach
                @else
                    <li class="italic text-gray-600 text-sm">No current treatments recorded.</li>
                @endif
            </ul>
        </div>
    </div>
</div>
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <div class="flex justify-between items-center mb-4 border-b pb-3 border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-900">Past Consultations</h2>
                        <span class="text-md font-semibold text-gray-600 bg-gray-100 px-3 py-1 rounded-full">Total: {{ $consultation->patient->consultations->where('date_heure', '<', now())->count() }}</span>
                    </div>

                    {{-- Scrollable section for past visits --}}
                    <div class="max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Time</th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Service</th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($consultation->patient->consultations->where('date_heure', '<', now())->sortByDesc('date_heure') as $pastConsult)
                                <tr>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                        {{ $pastConsult->date_heure ? $pastConsult->date_heure->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $pastConsult->date_heure ? $pastConsult->date_heure->format('H:i') : 'N/A' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm text-gray-800">{{ $pastConsult->motif ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($pastConsult->status == 'completed') bg-green-100 text-green-800
                                            @elseif($pastConsult->status == 'confirmed') bg-green-100 text-green-800 {{-- Added Confirmed --}}
                                            @elseif($pastConsult->status == 'cancelled') bg-red-100 text-red-800
                                            @elseif($pastConsult->status == 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-gray-500 text-white
                                            @endif">
                                            {{ ucfirst($pastConsult->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                @if($consultation->patient->consultations->where('date_heure', '<', now())->isEmpty())
                                    <tr>
                                        <td colspan="4" class="px-5 py-4 text-center text-gray-500 italic text-sm">No past consultations found.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div> {{-- End of scrollable div --}}
                </div>

            </div>

          <div class="w-full lg:w-1/2 space-y-6">
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <h2 class="text-2xl font-bold mb-4 text-gray-900 border-b pb-3 border-gray-200">Files</h2>
        <div class="space-y-3">
            @foreach($consultation->patient->documentMedicals as $document)
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg transition-colors duration-150">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-gray-800 font-medium">{{ $document->type ?? 'Document' }}.pdf</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">
                                     {{ $document->date_creation ? $document->date_creation->format('d/M/Y') : 'N/A' }}
                    </span>
                    <a href="{{ route('documents.download',['document'=> $document->id]) }}" class="text-blue-500 hover:text-blue-700" title="Download">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
            @if($consultation->patient->documentMedicals->isEmpty())
                <p class="text-gray-500 italic p-4 text-center text-sm">No medical files uploaded.</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <h2 class="text-2xl font-bold mb-4 text-gray-900 border-b pb-3 border-gray-200">Medical Records</h2>
        <div class="space-y-3">
            @foreach($consultation->patient->medicalRecords as $record)
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg transition-colors duration-150">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-gray-800 font-medium">Record {{ $record->record_start_date ? $record->record_start_date->format('d.m.y') : 'N/A' }}.pdf</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">
                        {{ $record->record_start_date ? $record->record_start_date->format('m/d') : 'N/A' }}
                    </span>
                    <a href="{{ route('medical-records.download',['record'=> $record->medical_record_id]) }}" class="text-green-500 hover:text-green-700" title="Download">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
            @if($consultation->patient->medicalRecords->isEmpty())
                <p class="text-gray-500 italic p-4 text-center text-sm">No medical notes recorded.</p>
            @endif
        </div>
    </div>


    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-4 border-b pb-3 border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Future Visits</h2>
            <span class="text-md font-semibold text-gray-600 bg-gray-100 px-3 py-1 rounded-full">{{ $consultation->patient->consultations->where('date_heure', '>', now())->count() }} Upcoming</span>
        </div>

        {{-- Scrollable section for future visits --}}
        <div class="max-h-80 overflow-y-auto pr-2 custom-scrollbar">
            <div class="space-y-4">
                @foreach($consultation->patient->consultations->where('date_heure', '>', now())->sortBy('date_heure') as $futureConsultation)
                <div class="border-l-4 border-purple-500 bg-purple-50 p-4 rounded-md shadow-sm">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-lg text-purple-800">
                            {{ $futureConsultation->date_heure ? $futureConsultation->date_heure->format('H:i') : 'N/A' }}
                        </span>
                        <span class="text-sm text-purple-600">
                            {{ $futureConsultation->date_heure ? $futureConsultation->date_heure->format('d M Y') : 'N/A' }}
                        </span>
                    </div>
                    <p class="text-gray-700 text-md">
                        Motif: {{ $futureConsultation->motif ?? 'Nothing' }}
                    </p>
                    <p class="text-gray-700 text-md">
                        Medecin: {{ $futureConsultation->medecin->name ?? 'N/A' }}
                    </p>
                    <p class="text-gray-700 text-md">
                        Specialty: {{ $futureConsultation->medecin->specialite_code ?? 'N/A' }}
                    </p>
                    {{-- Display status for Future Visits --}}
                    <p class="text-md mt-2">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($futureConsultation->status == 'confirmed') bg-green-100 text-green-800
                            @elseif($futureConsultation->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($futureConsultation->status == 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-500 text-white
                            @endif">
                            Status: {{ ucfirst($futureConsultation->status) }}
                        </span>
                    </p>
                </div>
                @endforeach
                @if($consultation->patient->consultations->where('date_heure', '>', now())->isEmpty())
                    <p class="text-gray-500 italic p-4 text-center text-sm">No future visits scheduled.</p>
                @endif
            </div>
        </div>
    </div>
</div>
        </div>

        <div class="mt-8 flex justify-end  space-x-4">
            <a href="{{ route('consultations.index') }}" class="bg-blue-400 px-6 py-3 border border-blue-300 rounded-lg text-white font-semibold transition-colors duration-200 shadow-sm">Back to List</a>
        </div>
    </div>
</div>

{{-- Add custom scrollbar styles (optional, but improves appearance) --}}
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endsection