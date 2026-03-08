@extends('layouts.app')

@section('content')
<div class="ml-64 p-6 bg-gray-50 min-h-screen"> 
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-white">Create New Medical Record</h1>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <form action="{{ route('medical-records.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="patient_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patient:</label>
                <select name="patient_id" id="patient_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Select a Patient</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (old('patient_id', $patient->id ?? '') == $p->id) ? 'selected' : '' }}>
                            {{ $p->nom }} {{ $p->prenom }} (CIN: {{ $p->cin }})
                        </option>
                    @endforeach
                </select>
                @error('patient_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="consultation_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Associated Consultation (Optional):</label>
                <select name="consultation_id" id="consultation_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">No specific consultation</option>
                    {{-- Consultations will be dynamically loaded via JS if patient_id changes, or pre-filled if patient is passed --}}
                    @foreach($consultations as $c)
                        <option value="{{ $c->id }}" {{ (old('consultation_id') == $c->id) ? 'selected' : '' }}>
                            {{ $c->date_heure->format('Y-m-d H:i') }} - {{ Str::limit($c->motif, 50) }}
                        </option>
                    @endforeach
                </select>
                @error('consultation_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="record_start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Record Date:</label>
                <input type="date" name="record_start_date" id="record_start_date" value="{{ old('record_start_date', now()->format('Y-m-d')) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @error('record_start_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes:</label>
                <textarea name="notes" id="notes" rows="5" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Medical Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const patientSelect = document.getElementById('patient_id');
        const consultationSelect = document.getElementById('consultation_id');

        patientSelect.addEventListener('change', function() {
            const patientId = this.value;
            consultationSelect.innerHTML = '<option value="">Loading consultations...</option>'; // Clear and show loading

            if (patientId) {
                // Make an AJAX request to fetch consultations for the selected patient
                fetch(`/api/patients/${patientId}/consultations-by-doctor`) // You'll need to define this API route
                    .then(response => response.json())
                    .then(data => {
                        consultationSelect.innerHTML = '<option value="">No specific consultation</option>'; // Default option
                        data.forEach(consultation => {
                            const option = document.createElement('option');
                            option.value = consultation.id;
                            option.textContent = `${new Date(consultation.date_heure).toLocaleString()} - ${consultation.motif.substring(0, 50)}${consultation.motif.length > 50 ? '...' : ''}`;
                            consultationSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching consultations:', error);
                        consultationSelect.innerHTML = '<option value="">Error loading consultations</option>';
                    });
            } else {
                consultationSelect.innerHTML = '<option value="">No specific consultation</option>'; // Reset if no patient selected
            }
        });
    });
</script>
@endsection
