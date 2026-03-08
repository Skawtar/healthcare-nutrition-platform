@extends('layouts.app') {{-- Assuming a main layout for consistent styling --}}

@section('title', 'Medecin Dashboard')

@section('content')
<div class="ml-64 p-6 bg-gray-50 min-h-screen"> {{-- Added a light gray background for the whole content area --}}
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Welcome Back, Dr. {{ $doctor->name ?? 'Doctor' }}!</h1> {{-- Dynamic Doctor's Name --}}

        {{-- Section 1: Today at a Glance & Key Metrics --}}
        <div class="bg-blue-600 text-white rounded-lg shadow-xl p-6 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center"> {{-- Changed to a deeper blue --}}
            <div>
                <h2 class="text-2xl font-semibold mb-2">Today at a Glance</h2>
                <p class="text-lg">You have <span class="font-bold">{{ $upcomingConsultationsCount }} upcoming consultations</span> and <span class="font-bold">{{ $newMessagesCount }} new message</span>.</p> {{-- Dynamic counts --}}
            </div>
            <div class="mt-4 md:mt-0 flex flex-wrap gap-4">
                <div class="text-center">
                    <p class="text-3xl font-bold">{{ $totalPatients }}</p> {{-- Dynamic Total Patients --}}
                    <p class="text-sm opacity-90">Total Patients</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold">{{ $followUpsDueCount }}</p> {{-- Dynamic Follow-ups Due (currently static) --}}
                    <p class="text-sm opacity-90">Follow-ups Due</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold">{{ $consultationsThisWeek }}</p> {{-- Dynamic Consultations This Week --}}
                    <p class="text-sm opacity-90">Consultations This Week</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Main Column 1: Detailed Upcoming Consultations --}}
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold text-gray-700">Your Consultations Today</h2>
                    <a href="{{ route('consultations.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Full Calendar &rarr;</a> {{-- Link to consultations index --}}
                </div>
                <ul class="divide-y divide-gray-200">
                    @forelse($upcomingConsultationsToday as $consultation)
                        <li class="py-4 flex items-start space-x-4">
                            <span class="block text-blue-700 font-bold text-lg w-16 flex-shrink-0 text-center">{{ \Carbon\Carbon::parse($consultation->date_heure)->format('H:i A') }}</span> {{-- Dynamic Time --}}
                            <div class="flex-grow">
                                <p class="text-lg font-semibold text-gray-800">{{ $consultation->patient->prenom ?? 'N/A' }} {{ $consultation->patient->nom ?? 'Patient' }}</p> {{-- Dynamic Patient Name --}}
                                <p class="text-sm text-gray-600 mb-2">Reason: {{ $consultation->motif ?? 'N/A' }}.</p> {{-- Dynamic Reason --}}
                                <div class="bg-blue-50 rounded-md p-3 text-gray-700 text-sm border border-blue-100"> {{-- Light blue background for notes --}}
                                    <strong class="text-gray-800">Notes:</strong> {{ $consultation->notes ?? 'No specific notes for this consultation.' }} {{-- Dynamic Notes --}}
                                </div>
                            </div>
                            <a href="{{ route('consultations.show', $consultation->id) }}" class="text-blue-500 hover:text-blue-700 self-center"> {{-- Link to specific consultation --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </li>
                    @empty
                        <li class="py-4 text-center text-gray-500 bg-blue-50 rounded-md p-4">No consultations scheduled for today.</li> {{-- Styled empty state --}}
                    @endforelse
                </ul>
            </div>

            {{-- Side Column 2: Quick Actions & Notifications --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Quick Actions Card --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 gap-3">
                        <a href="{{ route('patients.create') }}" class="flex items-center justify-center p-3 bg-green-500 text-white rounded-md hover:bg-green-600 transition duration-300 text-base font-medium"> {{-- Green button --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Add New Patient
                        </a>
                        <a href="{{ route('consultations.create') }}" class="flex items-center justify-center p-3 bg-purple-500 text-white rounded-md hover:bg-purple-600 transition duration-300 text-base font-medium"> {{-- Purple button --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Schedule Consultation
                        </a>
                        <a href="{{ route('patients.index') }}" class="flex items-center justify-center p-3 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300 text-base font-medium"> {{-- Blue button --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6m-12 0h-6a2 2 0 01-2-2V7a2 2 0 012-2h12a2 2 0 012 2v3m-7 0h7m-7 1.5H7.5" />
                            </svg>
                            View My Patients
                        </a>
                    </div>
                </div>

                {{-- Notifications Card --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Notifications & Alerts</h2>
                    <ul class="space-y-3">
                        <li class="p-3 bg-yellow-50 rounded-md border-l-4 border-yellow-500">
                            <p class="text-yellow-800 font-medium">New lab results available for David Lee.</p>
                            <span class="text-sm text-gray-500">2 hours ago</span>
                        </li>
                        <li class="p-3 bg-yellow-50 rounded-md border-l-4 border-yellow-500">
                            <p class="text-yellow-800 font-medium">Upcoming training session reminder.</p>
                            <span class="text-sm text-gray-500">Yesterday</span>
                        </li>
                    </ul>
                    <p class="text-gray-600 text-center py-4 text-sm">No critical alerts.</p>
                </div>
            </div>
        </div>

        {{-- Section 3: Recent Patient Activity (Full Width) --}}
        <div class="bg-white rounded-lg shadow-md p-6 mt-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Recent Patient Activity</h2>
            <ul class="divide-y divide-gray-200">
                @forelse($recentPatientActivity as $activity)
                    <li class="py-4 flex justify-between items-center">
                        <div>
                            <p class="text-gray-800">
                                <span class="font-medium">{{ $activity->patient->prenom ?? 'N/A' }} {{ $activity->patient->nom ?? 'Patient' }}</span> -
                                @if($activity->status == 'completed')
                                    Consultation Completed
                                @elseif($activity->status == 'cancelled')
                                    Consultation Cancelled
                                @elseif($activity->status == 'rejected')
                                    Consultation Rejected
                                @else
                                    Consultation Status: {{ ucfirst($activity->status) }}
                                @endif
                            </p>
                            <span class="text-sm text-gray-500">{{ $activity->date_heure->diffForHumans() }}</span> {{-- Shows "X hours ago" --}}
                        </div>
                        <a href="{{ route('consultations.show', $activity->id) }}" class="text-blue-500 hover:text-blue-700 text-sm font-medium">View Details &rarr;</a> {{-- Link to consultation details --}}
                    </li>
                @empty
                    <li class="py-4 text-center text-gray-500 bg-gray-50 rounded-md p-4">No recent patient activity.</li>
                @endforelse
            </ul>
        </div>

    </div>
</div>
@endsection