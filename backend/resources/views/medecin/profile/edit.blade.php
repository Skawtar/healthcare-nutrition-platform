@extends('layouts.app') {{-- Assuming you have a layout file --}}

@section('content')
<div class="ml-64 p-6 bg-white min-h-screen"> {{-- Consistent background from show page --}}
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-center">
            <div class="w-full lg:w-3/4 xl:w-2/3"> {{-- Consistent width --}}
                <div class="bg-white shadow-lg rounded-lg overflow-hidden"> {{-- Main white card, subtle shadow, rounded corners --}}
                    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-200"> {{-- Header section --}}
                        <a href="{{ route('profile.show') }}" class="text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <span class="text-xl md:text-2xl font-semibold text-gray-800">Edit Profile</span>
                        <div></div> {{-- Empty div for spacing if needed on the right --}}
                    </div>

                    <div class="p-6">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- Profile Image and Basic Info Section --}}
                            <div class="flex flex-col md:flex-row items-center md:items-start mb-8 p-4 bg-blue-50 rounded-lg shadow-sm">
                                <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                                    @if($user->image_profil)
                                        <img src="{{ asset('storage/profiles/'.$user->image_profil) }}" alt="Profile Image" class="rounded-full object-cover w-28 h-28 md:w-32 md:h-32 border-2 border-blue-300 shadow-md">
                                    @else
                                        <div class="rounded-full bg-blue-200 text-blue-800 flex items-center justify-center w-28 h-28 md:w-32 md:h-32 text-5xl font-bold border-2 border-blue-300 shadow-md">
                                            <span>{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <label for="image_profil" class="block text-blue-600 text-sm mt-3 cursor-pointer hover:text-blue-800 transition">Change Photo</label>
                                    <input type="file" class="hidden" id="image_profil" name="image_profil">
                                    @error('image_profil')
                                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="w-full p-3 md:w-2/3">
                                    <div class="mb-4">
                                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                                        <input id="name" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('name') border-red-500 @enderror" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>
                                        @error('name')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                                        <input id="email" type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('email') border-red-500 @enderror" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" disabled>
                                        @error('email')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="cin" class="block text-gray-700 text-sm font-bold mb-2">CIN</label>
                                        <input id="cin" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('cin') border-red-500 @enderror" name="cin" value="{{ old('cin', $user->cin) }}" required autocomplete="cin" disabled>
                                        @error('cin')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Detailed Information Sections --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                                {{-- Personal Information Card --}}
                                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
                                    <h5 class="text-xl font-bold text-blue-600 mb-4">Personal Information</h5>

                                    <div class="mb-4">
                                        <label for="date_naissance" class="block text-gray-700 text-sm font-bold mb-2">Date of Birth</label>
                                        <input id="date_naissance" type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('date_naissance') border-red-500 @enderror" name="date_naissance" value="{{ old('date_naissance', $user->date_naissance ? \Carbon\Carbon::parse($user->date_naissance)->format('Y-m-d') : '') }}">
                                        @error('date_naissance')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="genre" class="block text-gray-700 text-sm font-bold mb-2">Gender</label>
                                        <select id="genre" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('genre') border-red-500 @enderror" name="genre">
                                            <option value="">Select Gender</option>
                                            <option value="Homme" {{ old('genre', $user->genre) == 'Homme' ? 'selected' : '' }}>Male</option>
                                            <option value="Femme" {{ old('genre', $user->genre) == 'Femme' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        @error('genre')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="telephone" class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('telephone') border-red-500 @enderror" name="telephone" value="{{ old('telephone', $user->telephone) }}">
                                        @error('telephone')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="adresse" class="block text-gray-700 text-sm font-bold mb-2">Address</label>
                                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('adresse') border-red-500 @enderror" name="adresse" value="{{ old('adresse', $user->adresse) }}">
                                        @error('adresse')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="ville" class="block text-gray-700 text-sm font-bold mb-2">City</label>
                                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('ville') border-red-500 @enderror" name="ville" value="{{ old('ville', $user->ville) }}">
                                        @error('ville')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Professional Information Card --}}
                                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
                                    <h5 class="text-xl font-bold text-blue-600 mb-4">Professional Information</h5>

                                    <div class="mb-4">
                                        <label for="num_licence" class="block text-gray-700 text-sm font-bold mb-2">License Number</label>
                                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('num_licence') border-red-500 @enderror" name="num_licence" value="{{ old('num_licence', $user->num_licence) }}">
                                        @error('num_licence')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="specialite_code" class="block text-gray-700 text-sm font-bold mb-2">Specialty Code</label>
                                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('specialite_code') border-red-500 @enderror" name="specialite_code" value="{{ old('specialite_code', $user->specialite_code) }}">
                                        @error('specialite_code')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="diplome" class="block text-gray-700 text-sm font-bold mb-2">Diploma</label>
                                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('diplome') border-red-500 @enderror" name="diplome" value="{{ old('diplome', $user->diplome) }}">
                                        @error('diplome')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="experience" class="block text-gray-700 text-sm font-bold mb-2">Experience</label>
                                        <textarea name="experience" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('experience') border-red-500 @enderror">{{ old('experience', $user->experience) }}</textarea>
                                        @error('experience')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="adresse_cabinet" class="block text-gray-700 text-sm font-bold mb-2">Clinic Address</label>
                                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('adresse_cabinet') border-red-500 @enderror" name="adresse_cabinet" value="{{ old('adresse_cabinet', $user->adresse_cabinet) }}">
                                        @error('adresse_cabinet')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="tarif_consultation" class="block text-gray-700 text-sm font-bold mb-2">Consultation Fee (MAD)</label>
                                        <input type="number" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('tarif_consultation') border-red-500 @enderror" name="tarif_consultation" value="{{ old('tarif_consultation', $user->tarif_consultation) }}">
                                        @error('tarif_consultation')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-300">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Working Hours Card --}}
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Working Hours</h2>
                                    <div class="mb-4">
                                        <label for="horaires_debut" class="block text-gray-700 text-sm font-bold mb-2">Start Time</label>
                                        <input type="time" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('horaires_debut') border-red-500 @enderror" name="horaires_debut" value="{{ old('horaires_debut', $user->horaires_debut) }}">
                                        @error('horaires_debut')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label for="horaires_fin" class="block text-gray-700 text-sm font-bold mb-2">End Time</label>
                                        <input type="time" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500 @error('horaires_fin') border-red-500 @enderror" name="horaires_fin" value="{{ old('horaires_fin', $user->horaires_fin) }}">
                                        @error('horaires_fin')
                                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Working Days Card --}}
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Working Days</h2>
                                    <div class="grid grid-cols-2 gap-4">
                                        @php
                                            // Define the mapping from French values (what's in DB) to English display (what user sees)
                                            $dayMapping = [
                                                'Lundi'    => 'Monday',
                                                'Mardi'    => 'Tuesday',
                                                'Mercredi' => 'Wednesday',
                                                'Jeudi'    => 'Thursday',
                                                'Vendredi' => 'Friday',
                                                'Samedi'   => 'Saturday',
                                                'Dimanche' => 'Sunday',
                                            ];

                                            // Define the order of days for consistent display
                                            $frenchDaysOrder = array_keys($dayMapping);

                                            // Get the user's currently selected working days from the database.
                                            // Due to the `array` cast in the User model, $user->jours_travail should already be a PHP array.
                                            $selectedDaysFromDB = $user->jours_travail ?? [];

                                            // 'old()' takes precedence. If there was a validation error,
                                            // 'old('jours_travail')' will contain the array of French values that were submitted.
                                            // If not, use the values from the database.
                                            $currentlySelectedValues = old('jours_travail', $selectedDaysFromDB);

                                            // Ensure $currentlySelectedValues is always an array
                                            if (!is_array($currentlySelectedValues)) {
                                                $currentlySelectedValues = [];
                                            }

                                        @endphp

                                        @foreach ($frenchDaysOrder as $frenchDayValue)
                                            @php
                                                // Get the English display name for the current French value
                                                $englishDisplay = $dayMapping[$frenchDayValue] ?? $frenchDayValue;

                                                // Check if this French value is in the array of currently selected values
                                                $isChecked = in_array($frenchDayValue, $currentlySelectedValues);
                                            @endphp
                                            <div class="flex items-center">
                                                <input type="checkbox"
                                                       name="jours_travail[]"
                                                       id="{{ strtolower($englishDisplay) }}"
                                                       value="{{ $frenchDayValue }}"
                                                       {{ $isChecked ? 'checked' : '' }}
                                                       class="form-checkbox h-4 w-4 text-blue-600">
                                                <label for="{{ strtolower($englishDisplay) }}" class="ml-2 text-gray-700">{{ $englishDisplay }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('jours_travail')
                                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end space-x-4">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                                    Update Profile
                                </button>
                                <a href="{{ route('profile.show') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection