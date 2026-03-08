@extends('layouts.app')

@section('title', 'Admin - All Patients')

@section('content')
<div class="ml-64 p-6">

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">All Patients (Admin View)</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <p class="text-gray-700 mb-4">
            This page displays a comprehensive list of all patients in the system, along with their key details and subscription status for administrative management.
        </p>

        {{-- Search and Filter Section --}}
        <div class="flex flex-col md:flex-row gap-4 items-center mb-6">
            <input type="text" placeholder="Search by CIN, Name, Email, or Phone..." class="flex-grow p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            <select class="p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Subscription Statuses</option>
                <option value="active">Active Subscribers</option>
                <option value="inactive">Inactive Subscribers</option>
                <option value="expired">Expired Subscriptions</option>
            </select>
            <button class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-md shadow-sm transition duration-300">Apply Filters</button>
        </div>
        
        <table class="min-w-full divide-y divide-gray-200 mt-4">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CIN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($patients as $patient)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $patient->profile_image_url }}" alt="{{ $patient->nom }} {{ $patient->prenom }}">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $patient->nom }} {{ $patient->prenom }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $patient->cin }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $patient->email }}</div>
                        <div class="text-sm text-gray-500">{{ $patient->telephone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($patient->is_active_subscriber)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active ({{ $patient->subscription_plan }})</span>
                            <div class="text-xs text-gray-500">Ends: {{ $patient->subscription_end_date->format('Y-m-d') }}</div>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="" class="text-blue-600 hover:text-blue-900 mr-2">View Profile</a>
                        @if ($patient->is_active_subscriber)
                            <a href="" class="text-red-600 hover:text-red-900 ml-2">Unsubscribe</a>
                        @else
                            <a href="" class="text-green-600 hover:text-green-900 ml-2">Subscribe</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No patients found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $patients->links() }} {{-- Pagination links --}}
        </div>
    </div>
</div>
@endsection
