<?php

namespace App\Http\Controllers\Auth;

use App\Models\Patient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;


class AuthPatientController extends Controller
{
 public function register(Request $request)
    {
        // 1. Validation Block (Ensure this is uncommented)
        $request->validate([
            'cin' => 'required|unique:patients',
            'nom' => 'required',
            'prenom' => 'required',
            'date_naissance' => 'required|date',
            'genre' => 'required',
            'email' => 'required|email|unique:patients',
            'password' => ['required', 'confirmed', Password::defaults()],
            'telephone' => 'required', // Keep this without regex for now
            'adresse' => 'required'
        ]);

        // 2. Patient Creation Block (Ensure this is uncommented)
        $patient = Patient::create([
            'cin' => $request->cin,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'date_naissance' => $request->date_naissance,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash the password!
            'genre' => $request->genre,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            // Add any other fillable fields you require or have default values for
        ]);

    
        return response()->json([
            'patient' => $patient,
            'token' => $patient->createToken('patient_token')->plainTextToken, // Ensure HasApiTokens is on Patient model
            'message' => 'Registration successful!' // Custom success message
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth('patient')->attempt($credentials)) {
            $patient = Patient::where('email', $request->email)->first();
            return response()->json([
                'patient' => $patient,
                'token' => $patient->createToken('patient_token')->plainTextToken,
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }
}