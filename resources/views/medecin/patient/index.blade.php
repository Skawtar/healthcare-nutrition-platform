@extends('layouts.app')
@section('content')
<div class="ml-64 p-6  min-h-screen"> 
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0">
        <h1 class="text-2xl font-bold text-blue-800">Patients</h1> {{-- Blue heading --}}
        <div class="w-full md:w-auto">
            <form action="{{ route('patients.index') }}" method="GET" class="flex flex-wrap items-center space-y-3 sm:space-y-0 sm:space-x-4">
                {{-- CIN Search --}}
                <div class="relative flex-grow">
                    <input type="text" name="cin_search" placeholder="Rechercher par CIN..."
                           class="pl-10 pr-4 py-2 border border-blue-300 rounded-lg focus:ring-blue-400 focus:border-blue-400 w-full" {{-- Blue borders and focus --}}
                           value="{{ request('cin_search') }}">
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Blue search icon --}}
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                {{-- Genre Filter --}}
                <div>
                    <select name="genre_filter"
                            class="block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50"> {{-- Blue borders and focus --}}
                        <option value="">Tous les genres</option>
                        <option value="H" {{ request('genre_filter') == 'H' ? 'selected' : '' }}>Homme</option>
                        <option value="F" {{ request('genre_filter') == 'F' ? 'selected' : '' }}>Femme</option>
                    </select>
                </div>

                {{-- Blood Group Filter --}}
                <div>
                    <select name="blood_group_filter"
                            class="block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50"> {{-- Blue borders and focus --}}
                        <option value="">Tous les groupes sanguins</option>
                        @foreach($availableBloodGroups as $group)
                            <option value="{{ $group }}" {{ request('blood_group_filter') == $group ? 'selected' : '' }}>{{ $group }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"> {{-- Main blue button --}}
                    Filtrer
                </button>
                @if(request('cin_search') || request('genre_filter') || request('blood_group_filter'))
                    <a href="{{ route('patients.index') }}" class="w-full sm:w-auto px-4 py-2 border border-blue-300 rounded-lg text-blue-700 hover:bg-blue-100 text-center"> {{-- Blue border and text for clear filters --}}
                        Réinitialiser
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-blue-200"> {{-- Blue divider --}}
                <thead class="bg-blue-100"> {{-- Light blue table header --}}
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">CIN</th> {{-- Blue header text --}}
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Nom Complet</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Téléphone</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Groupe Sanguin</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Genre</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-blue-700 uppercase tracking-wider">Détails</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-blue-100"> {{-- Light blue divider for table body --}}
                    @forelse ($patients as $patient)
                    <tr class="hover:bg-blue-50"> {{-- Light blue hover effect --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $patient->cin }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full"
                                         src="{{ $patient->photo ? asset('storage/'.$patient->photo) : 'https://ui-avatars.com/api/?name='.urlencode($patient->nom.' '.$patient->prenom).'&background=random' }}"
                                         alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $patient->prenom }} {{ $patient->nom }}</div>
                                    <div class="text-sm text-gray-500">{{ $patient->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patient->telephone }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $patient->dossierMedical && $patient->dossierMedical->groupe_sanguin ? 'bg-blue-200 text-blue-800' : 'bg-gray-100 text-gray-800' }}"> {{-- Adjusted to a blue badge --}}
                                {{ $patient->dossierMedical->groupe_sanguin ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($patient->genre === 'F')
                                <span class="px-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-pink-100 text-pink-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7.5 5a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zm2.45 2.5a3.5 3.5 0 10-1.9 6.5H10a.5.5 0 010 1H8.05a3.5 3.5 0 100-7H10a.5.5 0 011 0h1.45a3.5 3.5 0 00-1.9-6.5l-.05.001z" clip-rule="evenodd"/>
                                    </svg>
                                    Femme
                                </span>
                            @else
                                <span class="px-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-.464 5.535a1 1 0 10-1.415-1.414 3 3 0 01-4.242 0 1 1 0 00-1.415 1.414 5 5 0 007.072 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Homme
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('patients.show', $patient) }}" class="text-blue-600 hover:text-blue-800" title="Voir"> {{-- Darker blue on hover --}}
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 bg-blue-50"> {{-- Light blue background for empty state --}}
                            Aucun patient trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-blue-200 sm:px-6 rounded-b-lg"> {{-- Blue border --}}
            <div class="flex-1 flex justify-between sm:hidden">
                <a href="{{ $patients->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-white bg-blue-400 hover:bg-blue-500 {{ $patients->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}"> {{-- Blue pagination buttons --}}
                    Previous
                </a>
                <a href="{{ $patients->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-white bg-blue-400 hover:bg-blue-500 {{ !$patients->hasMorePages() ? 'opacity-50 cursor-not-allowed' : '' }}"> {{-- Blue pagination buttons --}}
                    Next
                </a>
            </div>

            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center">
                <nav class="relative z-0 inline-flex items-center space-x-1" aria-label="Pagination">
                    <a href="{{ $patients->previousPageUrl() }}" class="relative inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md {{ $patients->onFirstPage() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-blue-600 bg-white hover:bg-blue-50' }}"> {{-- Blue for active, gray for disabled --}}
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Previous
                    </a>

                    @foreach ($patients->getUrlRange(1, $patients->lastPage()) as $page => $url)
                        @if ($page == $patients->currentPage())
                            <span class="relative inline-flex items-center px-4 py-1.5 text-sm font-medium rounded-md text-white bg-blue-500"> {{-- Current page blue --}}
                                {{ $page }}
                            </span>
                        @elseif ($page === 1 || $page === $patients->lastPage() || ($page >= $patients->currentPage() - 2 && $page <= $patients->currentPage() + 2))
                            <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-1.5 text-sm font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50"> {{-- Page links blue on hover --}}
                                {{ $page }}
                            </a>
                        @elseif (($page === $patients->currentPage() - 3 || $page === $patients->currentPage() + 3))
                            <span class="relative inline-flex items-center px-4 py-1.5 text-sm font-medium text-blue-600">
                                ...
                            </span>
                        @endif
                    @endforeach

                    <a href="{{ $patients->nextPageUrl() }}" class="relative inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md {{ !$patients->hasMorePages() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-blue-600 bg-white hover:bg-blue-50' }}"> {{-- Blue for active, gray for disabled --}}
                        Next
                        <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </nav>
            </div>
        </div>
    </div>
</div>

@endsection