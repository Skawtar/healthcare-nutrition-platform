<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient; // Assuming you have a Patient model
use App\Models\Consultation; // Assuming you have a Consultation model
use Carbon\Carbon; // For date calculations

class DashboardController extends Controller
{
    /**
     * Display the doctor's dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $doctor = Auth::user(); // Get the authenticated doctor (assuming your User model is for doctors)

        // 1. Today at a Glance
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $upcomingConsultationsToday = Consultation::where('medecin_id', $doctor->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('date_heure', [$today->startOfDay(), $today->endOfDay()])
            ->with('patient') // Eager load patient for display
            ->orderBy('date_heure', 'asc')
            ->get();

        $upcomingConsultationsCount = $upcomingConsultationsToday->count();

        // 2. Key Metrics
        $totalPatients = Patient::count(); // Assuming doctors can see all patients. Adjust if patients are assigned.
        // If patients are assigned to doctors: $totalPatients = $doctor->patients()->count();

        $consultationsThisWeek = Consultation::where('medecin_id', $doctor->id)
            ->whereBetween('date_heure', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        // Placeholder for "new messages" and "follow-ups due" - you'd need to implement these features
        $newMessagesCount = 1; // Static for now
        $followUpsDueCount = 5; // Static for now

        // 3. Recent Patient Activity (e.g., last 5 completed/updated consultations)
        $recentPatientActivity = Consultation::where('medecin_id', $doctor->id)
            ->whereIn('status', ['completed', 'cancelled', 'rejected']) // Or include 'confirmed' if you want to show recent confirmed ones too
            ->with('patient')
            ->orderBy('date_heure', 'desc') // Order by consultation date, most recent first
            ->limit(5) // Get latest 5 activities
            ->get();

        return view('medecin.dashboard', compact(
            'doctor',
            'upcomingConsultationsToday',
            'upcomingConsultationsCount',
            'totalPatients',
            'consultationsThisWeek',
            'newMessagesCount',
            'followUpsDueCount',
            'recentPatientActivity'
        ));
    }
}