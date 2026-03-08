@extends('layouts.app') {{-- Extend your main layout file --}}

@section('content')
<div class="ml-64 p-6 bg-gray-50 min-h-screen"> 
    <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-blue-800 dark:text-white">Consultation Details</h1>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 text-blue-500 dark:text-white">Consultation Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Patient:</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-200">{{ $consultation->patient->nom }} {{ $consultation->patient->prenom }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Date & Time:</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-200">{{ $consultation->date_heure->format('Y-m-d H:i') }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Motif:</p>
                <p class="mt-1 text-gray-900 dark:text-gray-200">{{ $consultation->motif }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Notes:</p>
                <p class="mt-1 text-gray-900 dark:text-gray-200">{{ $consultation->notes ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</p>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-200">{{ ucfirst($consultation->status) }}</p>
            </div>
        </div>
    </div>

    {{-- Associated Medical Records --}}
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 text-blue-500 dark:text-white">Associated Medical Records</h2>
        @if($consultation->medicalRecords->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Notes</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($consultation->medicalRecords as $record)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $record->record_start_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">{{ \Illuminate\Support\Str::limit($record->notes, 100, $end='...') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $record->medecin->name ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400">No medical records found for this consultation.</p>
        @endif
    </div>

    {{-- Associated Documents --}}
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 text-blue-500 dark:text-white">Associated Documents</h2>
        @if($consultation->documentMedicals->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Uploaded By</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">File</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($consultation->documentMedicals as $document)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $document->date_creation->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $document->nom_fichier ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $document->document_type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $document->medecin->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">
                                @if($document->file_path)
                                    <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        View File
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400">No medical documents found for this consultation.</p>
        @endif
    </div>

    <div class="mt-6">
        <a href="{{ route('doctor.medicalHistory') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-white bg-blue-500 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
            &larr; Back to History
        </a>
    </div>
</div>
@endsection
