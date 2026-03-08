@extends('layouts.app')

@section('title', 'Modifier Régime Alimentaire')

@section('content')
<div class="ml-64 p-6 min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto">
        <!-- Header with back button -->
        <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-200">
            <h1 class="text-3xl font-bold text-blue-800">Modifier le Régime Alimentaire</h1>
            <a href="{{ route('regimes.index') }}" class="flex items-center space-x-2 text-blue-600 hover:text-blue-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Retour à la liste</span>
            </a>
        </div>

        <!-- Form container -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden p-4">
            <form action="{{ route('regimes.update', $regime) }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Patient and Status -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Patient Selection -->
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Patient <span class="text-red-500">*</span>
                            </label>
                            <select name="patient_id" id="patient_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('patient_id') border-red-500 @enderror">
                                <option value="">Sélectionner un patient</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id', $regime->patient_id) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->nom }} {{ $patient->prenom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="statut_code" class="block text-sm font-medium text-gray-700 mb-2">
                                Statut du Régime <span class="text-red-500">*</span>
                            </label>
                            <select name="statut_code" id="statut_code" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('statut_code') border-red-500 @enderror">
                                <option value="">Sélectionner un statut</option>
                                @foreach ($regimeStatuses as $status)
                                    <option value="{{ $status->code }}" {{ old('statut_code', $regime->statut_code) == $status->code ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('statut_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Prescription Date -->
                        <div>
                            <label for="date_prescription" class="block text-sm font-medium text-gray-700 mb-2">
                                Date de Prescription <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_prescription" id="date_prescription"
                                value="{{ old('date_prescription', $regime->date_prescription->format('Y-m-d')) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('date_prescription') border-red-500 @enderror">
                            @error('date_prescription')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expiration Date -->
                        <div>
                            <label for="date_expiration" class="block text-sm font-medium text-gray-700 mb-2">
                                Date d'Expiration (Optionnel)
                            </label>
                            <input type="date" name="date_expiration" id="date_expiration"
                                value="{{ old('date_expiration', $regime->date_expiration ? $regime->date_expiration->format('Y-m-d') : '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('date_expiration') border-red-500 @enderror">
                            @error('date_expiration')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Calories -->
                    <div>
                        <label for="calories_journalieres" class="block text-sm font-medium text-gray-700 mb-2">
                            Calories Journalières (Optionnel)
                        </label>
                        <input type="number" name="calories_journalieres" id="calories_journalieres"
                            value="{{ old('calories_journalieres', $regime->calories_journalieres) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('calories_journalieres') border-red-500 @enderror"
                            placeholder="Ex: 2000">
                        @error('calories_journalieres')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Restrictions -->
                    <div>
                        <label for="restrictions" class="block text-sm font-medium text-gray-700 mb-2">
                            Restrictions Alimentaires (Séparées par des virgules)
                        </label>
                        @php
                            $restrictionsValue = '';
                            if (is_array(old('restrictions'))) {
                                $restrictionsValue = implode(', ', old('restrictions'));
                            } else {
                                $restrictionsValue = old('restrictions', $regime->restrictions ? implode(', ', $regime->restrictions) : '');
                            }
                        @endphp
                        <input type="text" name="restrictions" id="restrictions"
                            value="{{ $restrictionsValue }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('restrictions') border-red-500 @enderror"
                            placeholder="Ex: Gluten, Lactose, Noix">
                        @error('restrictions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Recommendations -->
                    <div>
                        <label for="recommandations" class="block text-sm font-medium text-gray-700 mb-2">
                            Recommandations
                        </label>
                        <textarea name="recommandations" id="recommandations" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('recommandations') border-red-500 @enderror"
                            placeholder="Ajouter des recommandations spécifiques pour le patient...">{{ old('recommandations', $regime->recommandations) }}</textarea>
                        @error('recommandations')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-10 flex justify-end space-x-4">
                    <a href="{{ route('regimes.index') }}" 
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        Annuler
                    </a>
                    <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        Mettre à jour le Régime
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection