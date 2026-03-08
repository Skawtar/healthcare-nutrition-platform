@extends('layouts.app') {{-- Assumes a main layout file --}}

@section('title', 'Admin - Add New Doctor')

@section('content')
<div class="ml-64 p-6">

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Add New Doctor</h1>
    <p class="text-gray-700 mb-6">
        Please fill in the details below to register a new medical professional in the system.
    </p>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('doctors.store') }}" method="POST" class="space-y-6">
            @csrf {{-- CSRF token for security --}}

            {{-- Hidden field to pre-set role to 'medecin' --}}
            <input type="hidden" name="role" value="medecin">
            
            {{-- Personal Information Section --}}
            <h2 class="text-xl font-semibold text-gray-700 pb-2 border-b border-gray-200">Personal Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                   <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1"> Name</label>
                    <input type="text" id="name" name="name" required
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Dr. Alice Smith">
                </div>
                <div>
                    <label for="cin" class="block text-sm font-medium text-gray-700 mb-1">CIN (National ID)</label>
                    <input type="text" id="cin" name="cin" required
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., AB123456">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" required
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., alice.smith@clinic.com">
                </div>
                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="telephone" name="telephone" required
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., +1234567890">
                </div>
                <div class="md:col-span-2">
                    <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" id="adresse" name="adresse"
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., 123 Main St, Anytown, Country">
                </div>
            </div>

            {{-- Professional Details Section --}}
            <h2 class="text-xl font-semibold text-gray-700 pt-4 pb-2 border-b border-gray-200">Professional Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="num_licence" class="block text-sm font-medium text-gray-700 mb-1">License Number</label>
                    <input type="text" id="num_licence" name="num_licence" required
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., MED12345">
                </div>
                <div>
                    <label for="specialite_code" class="block text-sm font-medium text-gray-700 mb-1">Specialty</label>
                    <select id="specialite_code" name="specialite_code" required
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select a specialty</option>
                        <option value="GEN">General Practitioner</option>
                        <option value="CAR">Cardiology</option>
                        <option value="PED">Pediatrics</option>
                        <option value="DER">Dermatology</option>
                        <option value="INT">Internal Medicine</option>
                        <option value="SUR">Surgery</option>
                    </select>
                </div>
            </div>

            {{-- Account Security Section --}}
            <h2 class="text-xl font-semibold text-gray-700 pt-4 pb-2 border-b border-gray-200">Account Security</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Enter a secure password">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Confirm the password">
                </div>
            </div>

            {{-- Profile Image Upload (Optional) --}}
            <div class="pt-4">
                <label for="image_profil" class="block text-sm font-medium text-gray-700 mb-1">Profile Image</label>
                <input type="file" id="image_profil" name="image_profil" accept="image/*"
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-4 pt-6">
                <a href="{{ route('admin.dashboard') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-100 transition duration-300">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-lg transition duration-300">
                    Add Doctor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
