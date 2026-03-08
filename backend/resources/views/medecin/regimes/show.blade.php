@extends('layouts.app') {{-- Assuming a main layout for consistent styling --}}

@section('title', 'Détails du Régime Alimentaire')

@section('content')
<div class="ml-64 p-6 min-h-screen bg-gray-50"> {{-- Adjust ml-64 if your sidebar width is different --}}
    <div class="max-w-4xl mx-auto ">
        <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-200">
            <h1 class="text-3xl font-bold text-blue-800">Détails du Régime Alimentaire #{{ $regime->id }}</h1>
            <a href="{{ route('regimes.index') }}" class="flex items-center space-x-2 text-blue-600 hover:text-blue-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Retour à la liste des régimes</span>
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Succès!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Erreur!</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-md overflow-hidden p-4 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                <div>
                    <p class="text-sm font-medium text-gray-500">Patient</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ $regime->patient->nom ?? 'N/A' }} {{ $regime->patient->prenom ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">Médecin Prescripteur</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ $regime->medecin->name ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">Date de Prescription</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ $regime->date_prescription ? $regime->date_prescription->format('d/m/Y') : 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">Date d'Expiration</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ $regime->date_expiration ? $regime->date_expiration->format('d/m/Y') : 'Non spécifiée' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">Statut du Régime</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        <span class="status-badge status-{{ $regime->regimeStatut->code ?? 'UNKNOWN' }}">
                            {{ $regime->regimeStatut->name ?? 'Inconnu' }}
                        </span>
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">Calories Journalières</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ $regime->calories_journalieres ?? 'Non spécifié' }} {{ $regime->calories_journalieres ? 'kcal' : '' }}
                    </p>
                </div>

                <div class="md:col-span-2"> {{-- Span across two columns --}}
                    <p class="text-sm font-medium text-gray-500">Restrictions Alimentaires</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        @if ($regime->restrictions && count($regime->restrictions) > 0)
                            {{ implode(', ', $regime->restrictions) }}
                        @else
                            Aucune restriction spécifiée.
                        @endif
                    </p>
                </div>

                <div class="md:col-span-2"> {{-- Span across two columns --}}
                    <p class="text-sm font-medium text-gray-500">Recommandations</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 whitespace-pre-wrap">
                        {{ $regime->recommandations ?? 'Aucune recommandation.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4 mt-6">
            <a href="{{ route('regimes.edit', $regime) }}"
                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                Modifier le Régime
            </a>

            <form action="{{ route('regimes.destroy', $regime) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce régime alimentaire ? Cette action est irréversible.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                    Supprimer le Régime
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- Add some basic CSS for status badges if not already in your app.css --}}
@push('styles')
<style>
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px; /* Pill shape */
        font-size: 0.875rem; /* text-sm */
        font-weight: 500; /* font-medium */
        line-height: 1;
        text-transform: uppercase;
    }
    .status-ACT { /* Active */
        background-color: #d1fae5; /* green-100 */
        color: #065f46; /* green-700 */
    }
    .status-INA { /* Inactive */
        background-color: #fee2e2; /* red-100 */
        color: #991b1b; /* red-700 */
    }
    .status-COM { /* Completed */
        background-color: #dbeafe; /* blue-100 */
        color: #1e40af; /* blue-700 */
    }
    .status-EXP { /* Expired */
        background-color: #fffbeb; /* yellow-100 */
        color: #92400e; /* yellow-700 */
    }
    .status-UNKNOWN { /* Fallback */
        background-color: #e5e7eb; /* gray-200 */
        color: #4b5563; /* gray-700 */
    }
</style>
@endpush