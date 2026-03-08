@extends('layouts.app')

@section('content')
<div class="ml-64 p-6  min-h-screen"> 
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-8">
        {{-- Upcoming Consultations Section --}}
        <div class="bg-white p-6 rounded-xl shadow-lg border "> 
            <h2 class="text-xl font-semibold mb-4 text-blue-500">Upcoming Consultations</h2> 

            {{-- Filter and Search for Upcoming Consultations --}}
            <form action="{{ route('consultations.index') }}" method="GET" class="flex flex-wrap items-center space-y-3 sm:space-y-0 sm:space-x-4 mb-4">
                <div class="flex-grow">
                    <label for="upcoming_cin_search" class="sr-only">Search by Patient CIN</label>
                    <input type="text" name="upcoming_cin_search" id="upcoming_cin_search"
                           class="block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50" {{-- Blue focus/border --}}
                           placeholder="Search Patient CIN..."
                           value="{{ request('upcoming_cin_search') }}">
                </div>
                <div>
                    <label for="upcoming_status_filter" class="sr-only">Filter by Status</label>
                    <select name="upcoming_status_filter" id="upcoming_status_filter"
                            class="block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50"> {{-- Blue focus/border --}}
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('upcoming_status_filter') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('upcoming_status_filter') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"> {{-- Main blue button --}}
                    Apply Filters
                </button>
                @if(request('upcoming_cin_search') || request('upcoming_status_filter'))
                    <a href="{{ route('consultations.index', array_merge(request()->except(['upcoming_cin_search', 'upcoming_status_filter', 'upcoming_page']), ['historical_page' => request('historical_page')])) }}" class="w-full sm:w-auto px-4 py-2 border border-blue-300 rounded-md text-blue-700 hover:bg-blue-100 text-center"> {{-- Blue border and text for clear filters --}}
                        Clear Filters
                    </a>
                @endif
            </form>

            <div class="relative overflow-x-auto shadow-md sm:rounded-lg bg-white max-h-[500px] overflow-y-auto">
                @if($upcomingConsultations->isEmpty())
                <div class="p-4 text-sm text-gray-700 bg-blue-50 dark:bg-gray-800 dark:text-gray-300"> {{-- Light blue background for empty state --}}
                    No upcoming consultations found.
                </div>
                @else
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-blue-700 uppercase bg-blue-100 dark:bg-gray-700 dark:text-gray-400"> {{-- Blue header --}}
                        <tr>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Reason</th>
                            <th scope="col" class="px-6 py-3">Patient</th>
                            <th scope="col" class="px-6 py-3">Date & Time</th>
                            <th scope="col" class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingConsultations as $consultation)
                        <tr class="bg-white border-b border-blue-50 dark:bg-gray-800 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-600"> {{-- Light blue border and hover --}}
                            <td class="px-6 py-4">
                                <span class="{{ getStatusBadgeClass($consultation->status) }}">
                                    {{ ucfirst($consultation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $consultation->motif ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                {{ $consultation->patient->nom ?? '' }} {{ $consultation->patient->prenom ?? '' }} (CIN: {{ $consultation->patient->cin ?? 'N/A' }})
                            </td>
                            <td class="px-6 py-4">
                                {{ $consultation->date_heure ? $consultation->date_heure->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 flex items-center space-x-2">
                                @if($consultation->id)
                                    <a href="{{ route('consultations.show', $consultation->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>

                                    @if($consultation->status == 'pending')
                                        <form action="{{ route('consultations.update_status', $consultation->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="font-medium text-green-600 dark:text-green-500 hover:underline">Accept</button>
                                        </form>
                                        <form action="{{ route('consultations.update_status', $consultation->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Reject</button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-gray-400">No ID</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            {{-- Upcoming Consultations Pagination --}}
            <div class="mt-4">
                {{ $upcomingConsultations->appends(request()->except('upcoming_page'))->links() }}
            </div>
        </div>

        {{-- Consultation History Section --}}
        <div class="bg-white p-6 rounded-xl shadow-lg"> {{-- Consistent blue border --}}
            <h2 class="text-xl font-semibold mb-4 text-blue-500">Consultation History</h2> {{-- Consistent blue heading --}}

            {{-- Filter and Search for Consultation History --}}
            <form action="{{ route('consultations.index') }}" method="GET" class="flex flex-wrap items-center space-y-3 sm:space-y-0 sm:space-x-4 mb-4">
                <div class="flex-grow">
                    <label for="historical_cin_search" class="sr-only">Search by Patient CIN</label>
                    <input type="text" name="historical_cin_search" id="historical_cin_search"
                           class="block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           placeholder="Search Patient CIN..."
                           value="{{ request('historical_cin_search') }}">
                </div>
                <div>
                    <label for="historical_status_filter" class="sr-only">Filter by Status</label>
                    <select name="historical_status_filter" id="historical_status_filter"
                            class="block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('historical_status_filter') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('historical_status_filter') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="rejected" {{ request('historical_status_filter') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
                    Apply Filters
                </button>
                @if(request('historical_cin_search') || request('historical_status_filter'))
                    <a href="{{ route('consultations.index', array_merge(request()->except(['historical_cin_search', 'historical_status_filter', 'historical_page']), ['upcoming_page' => request('upcoming_page')])) }}" class="w-full sm:w-auto px-4 py-2 border border-blue-300 rounded-md text-blue-700 hover:bg-blue-100 text-center">
                        Clear Filters
                    </a>
                @endif
            </form>

            <div class="relative overflow-x-auto shadow-md sm:rounded-lg bg-white max-h-[500px] overflow-y-auto">
                @if($historicalConsultations->isEmpty())
                <div class="p-4 text-sm text-gray-700 bg-blue-50 dark:bg-gray-800 dark:text-gray-300"> {{-- Light blue background for empty state --}}
                    No consultation history found.
                </div>
                @else
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-blue-700 uppercase bg-blue-100 dark:bg-gray-700 dark:text-gray-400"> {{-- Blue header --}}
                        <tr>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Reason</th>
                            <th scope="col" class="px-6 py-3">Patient</th>
                            <th scope="col" class="px-6 py-3">Date & Time</th>
                            <th scope="col" class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historicalConsultations as $consultation)
                        <tr class="bg-white border-b border-blue-50 dark:bg-gray-800 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-600"> {{-- Light blue border and hover --}}
                            <td class="px-6 py-4">
                                <span class="{{ getStatusBadgeClass($consultation->status) }}">
                                    {{ ucfirst($consultation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $consultation->motif ?? 'Nothing' }}</td>
                            <td class="px-6 py-4">
                                {{ $consultation->patient->nom ?? '' }} {{ $consultation->patient->prenom ?? '' }} (CIN: {{ $consultation->patient->cin ?? 'N/A' }})
                            </td>
                            <td class="px-6 py-4">
                                {{ $consultation->date_heure ? $consultation->date_heure->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 flex items-center space-x-2">
                                @if($consultation->id)
                                    <a href="{{ route('consultations.show', $consultation->id) }}"
                                       class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        View
                                    </a>
                                    @if($consultation->status == 'confirmed')
                                        <form action="{{ route('consultations.update_status', $consultation->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="font-medium text-yellow-600 dark:text-yellow-500 hover:underline">Cancel</button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-gray-400">No ID available</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            {{-- Consultation History Pagination --}}
            <div class="mt-4">
                {{ $historicalConsultations->appends(request()->except('historical_page'))->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@php
function getStatusBadgeClass($status) {
    switch(strtolower($status)) {
        case 'confirmed': return 'bg-green-200 text-green-800 text-xs font-medium px-2 py-0.5 rounded-full';
        case 'pending': return 'bg-yellow-200 text-yellow-800 text-xs font-medium px-2 py-0.5 rounded-full';
        case 'completed': return 'bg-blue-200 text-blue-800 text-xs font-medium px-2 py-0.5 rounded-full'; // Changed to a blue tone
        case 'cancelled': return 'bg-red-200 text-red-800 text-xs font-medium px-2 py-0.5 rounded-full';
        case 'rejected': return 'bg-purple-200 text-purple-800 text-xs font-medium px-2 py-0.5 rounded-full';
        default: return 'bg-gray-200 text-gray-800 text-xs font-medium px-2 py-0.5 rounded-full';
    }
}
@endphp