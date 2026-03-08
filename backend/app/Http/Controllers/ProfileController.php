<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;
use App\Enums\UserRole;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('medecin.profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
$daysOfWeek = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];   
     return view('medecin.profile.edit', compact('user', 'daysOfWeek'));
    }

  
public function update(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'name' => 'required|string|max:255',
        'date_naissance' => 'nullable|date',
        'genre' => 'nullable|string|in:Homme,Femme',
        'specialite_code' => 'nullable|string|max:255',
        'num_licence' => 'nullable|string|max:255',
        'telephone' => 'nullable|string|max:20',
        'adresse' => 'nullable|string|max:500',
        'diplome' => 'nullable|string|max:255',
        'adresse_cabinet' => 'nullable|string|max:500',
        'experience' => 'nullable|string|max:1000',
        'ville' => 'nullable|string|max:255',
        'horaires_debut' => 'nullable|date_format:H:i',
        'horaires_fin' =>  'nullable|date_format:H:i',
        'jours_travail' => 'nullable|array',
        'jours_travail.*' => 'string|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche',
        'tarif_consultation' => 'nullable|numeric|min:0',
        'image_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $data = $request->only([
        'name', 'date_naissance', 'genre', 'specialite_code', 'num_licence',
        'telephone', 'adresse', 'diplome', 'adresse_cabinet', 'experience',
        'ville', 'tarif_consultation'
    ]);

    // Handle horaires_debut
    if ($request->has('horaires_debut')) {
        $data['horaires_debut'] = $request->horaires_debut;
    } else {
        $data['horaires_debut'] = $user->horaires_debut;
    }

    // Handle horaires_fin
    if ($request->has('horaires_fin')) {
        $data['horaires_fin'] = $request->horaires_fin;
    } else {
        $data['horaires_fin'] = $user->horaires_fin;
    }

    // Handle jours_travail - only update if the field exists in the request
    if ($request->has('jours_travail')) {
        $data['jours_travail'] = json_encode($request->jours_travail);
    }
    // If not provided, don't include it in $data so the existing value remains

    // Handle image upload
    if ($request->hasFile('image_profil')) {
        if ($user->image_profil) {
            Storage::disk('public')->delete('profiles/' . $user->image_profil);
        }

        $imageName = time().'.'.$request->image_profil->extension();
        $request->image_profil->storeAs('profiles', $imageName, 'public');
        $data['image_profil'] = $imageName;
    }

    $user->update($data);

    return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
}
    public function changePassword()
    {
        return view('profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Password changed successfully.');
    }
}