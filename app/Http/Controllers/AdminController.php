<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User; // Assuming User model represents doctors/users
use App\Models\Service;
use App\Models\Subscription;
use App\Enums\UserRole; // Make sure this enum is correctly imported
use Carbon\Carbon;

class AdminController extends Controller // Assuming this is your controller name
{
    public function dashboard()
    {
        // Total counts
        $totalPatients = Patient::count();
        // Assuming UserRole::User corresponds to doctors or a specific role for them
        $totalDoctors = User::where('role', UserRole::User)->count();
        $totalServices = Service::count();

        // Subscription statistics
        $totalSubscribedPatients = Patient::has('activeSubscription')->count();
        $recentSubscriptions = Subscription::with(['patient', 'service'])
            ->where('start_date', '>=', Carbon::now()->subDays(30))
            ->count();

        // Recent registrations (last 30 days)
        $recentRegistrations = [
            'patients' => Patient::where('created_at', '>=', Carbon::now()->subDays(30))->count(),
            'doctors' => User::where('role', UserRole::User)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->count(),
        ];

        // Recent subscriptions with details for the table
        $latestSubscriptions = Subscription::with(['patient', 'service'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Popular services (you might still use this for other purposes or ignore if not needed)
        $popularServices = Service::withCount(['subscriptions' => function($query) {
                $query->where('status', 'active')
                    ->where('end_date', '>', Carbon::now());
            }])
            ->orderBy('subscriptions_count', 'desc')
            ->take(5)
            ->get();

        // --- NEW ADDITION FOR SERVICE CARDS ---
        // Fetch all services to display in the cards section
        // You might want to order them alphabetically or by some other criteria
        $services = Service::orderBy('name')->get(); // Fetch all services, ordered by name

        return view('admin.dashboard', compact(
            'totalPatients',
            'totalDoctors',
            'totalServices',
            'totalSubscribedPatients',
            'recentSubscriptions',
            'recentRegistrations',
            'latestSubscriptions',
            'popularServices', // Keep if you still want to pass it, even if not explicitly used for cards
            'services' // <-- IMPORTANT: Pass the services collection for the cards
        ));
    }
}