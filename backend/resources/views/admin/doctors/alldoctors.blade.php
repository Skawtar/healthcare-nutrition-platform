@extends('layouts.app') {{-- Assuming you have a main layout file --}}

@section('title', 'Admin Dashboard')

@section('content')
<div class="ml-64 p-6">
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">All Doctors (Admin View)</h1>

    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-700 mb-4">
            This page lists all registered doctors (medecins) in the system, allowing administrators to manage their accounts.
        </p>
        
        <table class="min-w-full divide-y divide-gray-200 mt-4">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($doctors as $doctor)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $doctor->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $doctor->specialite_code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $doctor->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($doctor->est_actif)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="#" class="text-blue-600 hover:text-blue-900 mr-2">View Profile</a>
                        <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No doctors found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $doctors->links() }}
        </div>
    </div>
</div>
@endsection
