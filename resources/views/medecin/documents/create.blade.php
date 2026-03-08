@extends('layouts.app')

@section('title', 'Upload Document')

@section('content')
<div class="ml-64 p-6 bg-white min-h-screen"> 

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Upload Medical Document</h2>
    
    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Hidden doctor field with authenticated doctor's ID -->
        <input type="hidden" name="medecin_id" value="{{ auth()->user()->id }}">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Patient Selection -->
            <div>
                <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-1">Patient</label>
                <input type="text" name="patient_id" value="{{ $patient->id ?? '' }}" disabled 
                    class="w-full border border-gray-300 bg-gray-50 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Doctor Display (readonly) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Doctor</label>
                <div class="w-full border border-gray-300 bg-gray-50 rounded-md py-2 px-3">
                    Dr. {{ auth()->user()->name }}
                </div>
            </div>

            <!-- Document Type -->
            <div>
                <label for="document_type" class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                <select id="document_type" name="document_type" required
                    class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Document Type</option>
                        <option value="ORDONNANCE">Ordonnance</option>
                        <option value="BILAN_SANGUIN">Bilan Sanguin</option>
                        <option value="RADIOLOGIE">Radiologie</option>
                        <option value="COMPTE_RENDU">Compte Rendu</option>
                        <option value="AUTRE">Autre</option>
                    </select>
            </div>

            <!-- Medical Record Association -->
            <div>
                <label for="medical_record_id" class="block text-sm font-medium text-gray-700 mb-1">Associated Medical Record (Optional)</label>
                <select id="medical_record_id" name="medical_record_id"
                    class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">None</option>
                    @foreach($medicalRecords as $record)
                        <option value="{{ $record->medical_record_id }}">Record #{{$record->medical_record_id }} ({{ $record->created_at->format('Y-m-d') }})</option>
                    @endforeach
                </select>
            </div>

            <!-- File Name -->
            <div>
                <label for="nom_fichier" class="block text-sm font-medium text-gray-700 mb-1">Document Name</label>
                <input type="text" id="nom_fichier" name="nom_fichier" required
                    class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Signed Status -->
            <div class="flex items-center">
                <input type="checkbox" id="est_signe" name="est_signe" value="1"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="est_signe" class="ml-2 block text-sm text-gray-700">Document is signed</label>
            </div>

            <!-- File Upload -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Document File</label>
                <div class="mt-1 flex items-center">
                    <label for="fichier" class="cursor-pointer">
                        <div class="px-4 py-2 bg-blue-50 border border-blue-200 rounded-md shadow-sm text-sm font-medium text-blue-700 hover:bg-blue-100">
                            Choose file
                        </div>
                        <input id="fichier" name="fichier" type="file" class="sr-only" required>
                    </label>
                    <span id="file-name" class="ml-2 text-sm text-gray-500">No file chosen</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">PDF, DOC, JPG, PNG (Max: 5MB)</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('documents.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Upload Document
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('fichier').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'No file chosen';
    document.getElementById('file-name').textContent = fileName;
});
</script>
@endsection