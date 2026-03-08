@extends('layouts.app')

@section('content')
<div class="ml-64 p-4 bg-gray-100 min-h-screen">
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold text-gray-600 mb-6">Admin Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-blue-500 uppercase mb-1">Total Patients</div>
                        <div class="text-2xl font-bold text-gray-500">{{ $totalPatients }}</div>
                    </div>
                    {{-- SVG for Total Patients (User Group) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-blue-500">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-green-400">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-green-500 uppercase mb-1">Total Doctors</div>
                        <div class="text-2xl font-bold text-gray-500">{{ $totalDoctors }}</div>
                    </div>
                   
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-green-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.5h.008v.008H16.5A7.5 7.5 0 0 0 9 12.75V15h2.25L15 18.75m-3.75-10.5h.008v.008H12A7.5 7.5 0 0 0 4.5 12.75V15h2.25L10.5 18.75m-3.75-10.5Z" />
                    </svg>
                  
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-blue-400 uppercase mb-1">Subscribed Patients</div>
                        <div class="text-2xl font-bold text-gray-500">{{ $totalSubscribedPatients }}</div>
                    </div>
                    {{-- SVG for Subscribed Patients (Identification) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-blue-400">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-yellow-600 uppercase mb-1">Recent Subscriptions (30d)</div>
                        <div class="text-2xl font-bold text-gray-500">{{ $recentSubscriptions }}</div>
                    </div>
                    {{-- SVG for Recent Subscriptions (Calendar) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-yellow-600">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5m15 7.5v-7.5" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-purple-600 uppercase mb-1">Total Services</div>
                        <div class="text-2xl font-bold text-gray-500">{{ $totalServices }}</div>
                    </div>
                    {{-- SVG for Total Services (Squares 2x2) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-purple-600">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-red-600 uppercase mb-1">New Registrations (30d)</div>
                        <div class="flex items-center">
                            <div class="text-2xl font-bold text-gray-500">P: {{ $recentRegistrations['patients'] }} | D: {{ $recentRegistrations['doctors'] }}</div>
                        </div>
                    </div>
                    {{-- SVG for New Registrations (Clipboard Document List) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-red-600">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 21h4.5M12 3h.008v.008H12zm0 18h.008v.008H12zM3 16.5V12a3.75 3.75 0 0 1 3.75-3.75h1.5A1.5 1.5 0 0 1 9.75 8.25v1.5A3.75 3.75 0 0 0 13.5 13.5h1.5m-12 0h12" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-blue-200">
                <h6 class="text-lg font-semibold text-blue-800">Recent Subscriptions</h6>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-blue-400 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            <th class="px-6 py-3 border-b-2 border-gray-200">Patient</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200">Service</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200">Start Date</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200">End Date</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestSubscriptions as $subscription)
                        <tr class="hover:bg-blue-50">
                            <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-900">{{ $subscription->patient->nom }} {{ $subscription->patient->prenom }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-900">{{ $subscription->service->name }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-900">{{ $subscription->start_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 text-sm text-gray-900">{{ $subscription->end_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 border-b border-gray-200 text-sm">
                                <span class="px-3 py-1 font-semibold leading-tight rounded-full {{ $subscription->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


    <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">All Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $service) {{-- Assuming $services is passed from your controller --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($service->description, 100) }}</p> {{-- Using Str::limit for a short description --}}
                    @if(isset($service->price))
                        <p class="text-lg font-bold text-blue-600 mb-4">{{ number_format($service->price, 2) }} MAD</p> {{-- Example price display --}}
                    @endif

                    <div class="flex space-x-2 mt-4 border-t pt-4 border-gray-100">
                        <a href="" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            View
                        </a>
                        <a href="" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            Edit
                        </a>
                        {{-- Add more actions like Delete, etc. --}}
                    </div>
                </div>
                @empty
                <div class="col-span-full bg-white rounded-lg shadow-md p-6 text-gray-600">
                    No services found.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection