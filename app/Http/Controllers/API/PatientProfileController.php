<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class PatientProfileController extends Controller
{
    /**
     * Get authenticated patient's full profile with subscription status
     */
    public function show(Request $request)
    {
        $patient = $request->user()->load('subscription');
        
        return response()->json([
            'success' => true,
            'patient' => $this->formatPatientData($patient)
        ]);
    }

    /**
     * Update patient profile with optional image upload
     */
    public function update(Request $request)
    {
        $patient = $request->user();
        
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:patients,email,'.$patient->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'date_naissance' => 'nullable|date',
            'genre' => 'required|in:male,female,other',
            'image_profil' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'nom', 'prenom', 'email', 'telephone', 
            'adresse', 'date_naissance', 'genre'
        ]);

        // Handle image upload
        if ($request->hasFile('image_profil')) {
            // Delete old image if exists
            if ($patient->image_profil) {
                Storage::delete($patient->image_profil);
            }
            
            $path = $request->file('image_profil')->store('patient_images');
            $data['image_profil'] = $path;
        }

        $patient->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'patient' => $this->formatPatientData($patient->fresh())
        ]);
    }

    /**
     * Format patient data with subscription information
     */
    public function formatPatientData(Patient $patient)
    {
        return [
            'id' => $patient->id,
            'cin' => $patient->cin,
            'nom' => $patient->nom,
            'prenom' => $patient->prenom,
            'full_name' => $patient->prenom . ' ' . $patient->nom,
            'email' => $patient->email,
            'telephone' => $patient->telephone,
            'adresse' => $patient->adresse,
            'date_naissance' => $patient->date_naissance,
            'formatted_date_naissance' => $patient->date_naissance 
                ? $patient->date_naissance->format('d/m/Y')
                : null,
            'genre' => $patient->genre,
            'image_profil' => $patient->image_profil 
                ? Storage::url($patient->image_profil)
                : null,
            'is_subscribed' => $patient->is_subscribed,
            'is_active_subscriber' => $patient->subscription?->isActive() ?? false,
            'subscription_plan' => $patient->subscription?->plan_name,
            'subscription_start_date' => $patient->subscription?->start_date,
            'subscription_end_date' => $patient->subscription?->end_date,
            'subscription_period' => $patient->subscription 
                ? $patient->subscription->start_date->format('d/m/Y') . ' - ' . 
                  $patient->subscription->end_date->format('d/m/Y')
                : 'N/A',
            'payment_method' => $patient->subscription?->payment_method,
            'created_at' => $patient->created_at,
            'updated_at' => $patient->updated_at,
        ];
    }

    /**
     * Logout patient and revoke current token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
    public function change(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'], // 'confirmed' checks for new_password_confirmation
        ]);

        $user = $request->user(); // Gets the authenticated user

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided current password does not match our records.'],
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password changed successfully!'], 200);
    }

     public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $patient = Auth::user();

        // Delete old image if it exists
        if ($patient->image_profil && Storage::exists($patient->image_profil)) {
            Storage::delete($patient->image_profil);
        }

        // Store the new image
        $path = $request->file('profile_image')->store('profile_images', 'public');

        // Update patient record
        $patient->image_profil = $path;
        $patient->save();

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'profile_image_url' => $patient->profile_image_url,
        ]);
    }
}