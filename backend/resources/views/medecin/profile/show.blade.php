@extends('layouts.app')

@section('content')
<div class="ml-64 p-6 bg-white min-h-screen"> 
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-center">
            <div class="w-full lg:w-3/4 xl:w-2/3"> {{-- Responsive width --}}
                <div class="bg-white shadow-lg rounded-lg overflow-hidden"> {{-- Main white card --}}
                    {{-- Header Section --}}
                    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-200">
                        <a href="{{ url()->previous() }}" class="text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <span class="text-xl md:text-2xl font-semibold text-gray-800">Doctor Profile</span>
                        <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 transition ease-in-out duration-150">Edit Profile</a>
                    </div>

                    <div class="p-6">
                        {{-- Top Profile Card Section (Image, Name, Role) --}}
                        <div class="flex flex-col md:flex-row items-center md:items-start p-4 bg-blue-50 rounded-lg shadow-sm mb-8">
                            <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                                @if($user->image_profil)
                            <img src="{{ asset('storage/profiles/'.$user->image_profil) }}" alt="Profile Image" class="rounded-full object-cover w-28 h-28 md:w-32 md:h-32 border-2 border-blue-300 shadow-md">                                @else
                                    <div class="rounded-full bg-blue-200 text-blue-800 flex items-center justify-center w-28 h-28 md:w-32 md:h-32 text-5xl font-bold border-2 border-blue-300 shadow-md">
                                        <span>{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="text-center md:text-left">
                                <h4 class="text-3xl font-bold text-gray-800 mb-1">{{ $user->name }}</h4>
                                <p class="text-blue-600 text-lg md:text-xl"> {{ $user->specialite_code ? ' | ' . $user->specialite_code : '' }}</p>
                                @if($user->adresse_cabinet)
                                <p class="text-gray-600 text-sm md:text-base mt-1">Clinic: {{ $user->adresse_cabinet }}</p>
                                @endif
                                
                            </div>
                        </div>
                        {{-- End Top Profile Card Section --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"> {{-- Grid for the new sub-cards --}}

                            {{-- Personal Information Card --}}
                            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
                                <h5 class="text-xl font-bold text-blue-600 mb-4">Personal Information</h5>
                                <p class="mb-2"><strong class="text-gray-700">CIN:</strong> <span class="text-gray-600">{{ $user->cin }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Date of Birth:</strong> <span class="text-gray-600">{{ $user->date_naissance ? $user->date_naissance->format('d/m/Y') : 'N/A' }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Gender:</strong> <span class="text-gray-600">{{ $user->genre ?? 'N/A' }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Phone:</strong> <span class="text-gray-600">{{ $user->telephone ?? 'N/A' }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Email:</strong> <span class="text-gray-600">{{ $user->email }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Address:</strong> <span class="text-gray-600">{{ $user->adresse ?? 'N/A' }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">City:</strong> <span class="text-gray-600">{{ $user->ville ?? 'N/A' }}</span></p>
                            </div>

                            {{-- Professional Information Card --}}
                            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
                                <h5 class="text-xl font-bold text-blue-600 mb-4">Professional Information</h5>
                                <p class="mb-2"><strong class="text-gray-700">License Number:</strong> <span class="text-gray-600">{{ $user->num_licence ?? 'N/A' }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Specialty Code:</strong> <span class="text-gray-600">{{ $user->specialite_code ?? 'N/A' }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Diploma:</strong> <span class="text-gray-600">{{ $user->diplome ?? 'N/A' }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Experience:</strong> <span class="text-gray-600">{{ $user->experience ? $user->experience . ' years' : 'N/A' }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Clinic Address:</strong> <span class="text-gray-600">{{ $user->adresse_cabinet ?? 'N/A' }}</span></p>
                                <p class="mb-2"><strong class="text-gray-700">Registration Date:</strong> <span class="text-gray-600">{{ $user->date_inscription->format('d/m/Y') }}</span></p>
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> {{-- Grid for the new sub-cards (Working Hours & Consultation Fee) --}}

                            {{-- Working Hours Card --}}
                            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
                                <h5 class="text-xl font-bold text-blue-600 mb-4">Working Hours</h5>
                                @if($user->horaires_debut && $user->horaires_fin)
                                    <p class="mb-2">
                                        <strong class="text-gray-700">Hours:</strong>
                                        <span class="text-gray-600">{{ \Carbon\Carbon::parse($user->horaires_debut)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($user->horaires_fin)->format('H:i') }}</span>
                                    </p>
                                 <p class="mb-2">
    <strong class="text-gray-700">Working Days:</strong>
    <span class="text-gray-600">
        @if($user->jours_travail)
            @php
                // Attempt to decode JSON array or fall back to string replace
                $workingDaysArray = json_decode($user->jours_travail);
                if (is_array($workingDaysArray)) {
                    // Map French day names to English
                    $frenchToEnglishDays = [
                        'Lundi' => 'Monday',
                        'Mardi' => 'Tuesday',
                        'Mercredi' => 'Wednesday',
                        'Jeudi' => 'Thursday',
                        'Vendredi' => 'Friday',
                        'Samedi' => 'Saturday',
                        'Dimanche' => 'Sunday'
                    ];
                    
                    $englishDays = array_map(function($day) use ($frenchToEnglishDays) {
                        return $frenchToEnglishDays[$day] ?? $day;
                    }, $workingDaysArray);
                    
                    echo implode(', ', $englishDays);
                } else {
                    // Fallback if not a valid JSON array string
                    $days = str_replace(['[', ']', '"'], '', $user->jours_travail);
                    $days = preg_split('/,\s*/', $days);
                    
                    $frenchToEnglishDays = [
                        'Lundi' => 'Monday',
                        'Mardi' => 'Tuesday',
                        'Mercredi' => 'Wednesday',
                        'Jeudi' => 'Thursday',
                        'Vendredi' => 'Friday',
                        'Samedi' => 'Saturday',
                        'Dimanche' => 'Sunday'
                    ];
                    
                    $englishDays = array_map(function($day) use ($frenchToEnglishDays) {
                        return $frenchToEnglishDays[trim($day)] ?? trim($day);
                    }, $days);
                    
                    echo implode(', ', $englishDays);
                }
            @endphp
        @else
            N/A
        @endif
    </span>
</p>
                                @else
                                    <p class="text-gray-600 italic">No working hours specified</p>
                                @endif
                            </div>

                            {{-- Consultation Fee Card (Conditional) --}}
                            @if($user->tarif_consultation)
                            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100 flex flex-col justify-center items-center text-center">
                                <h5 class="text-xl font-bold text-blue-600 mb-4">Consultation Fee</h5>
                                <p class="text-5xl font-extrabold text-blue-600">{{ number_format($user->tarif_consultation, 2) }} MAD</p>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection