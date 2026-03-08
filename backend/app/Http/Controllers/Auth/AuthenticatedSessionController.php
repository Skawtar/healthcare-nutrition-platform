<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\VerificationCodeController ;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Enums\UserRole; // Correct: This should be at the top level
// Assuming these are needed for other parts of the controller or just for reference
use App\Models\User; // Assuming you have a User model
use Illuminate\Support\Facades\Hash; // If you need to hash passwords or similar operations




class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Authenticate the user
        $request->authenticate();

        // 2. Regenerate the session
        $request->session()->regenerate();

        // 3. Check if the user's email is verified
        $user = auth()->user();

        if (!$user->hasVerifiedEmail()) {
            // If email is not verified, redirect to the custom verification notice route
            return redirect()->route('verification.notice.code')->with('status', 'Please verify your email address to continue.');
        }

        // If the email is verified, proceed with your existing role-based redirects
        if ($user->role === UserRole::Admin) {
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back, Admin!');
        }

        if ($user->role === UserRole::User) { // Assuming UserRole::User represents 'Medecin' based on your previous code
            return redirect()->route('dashboard')->with('success', 'Welcome back, Medecin!');
        }

        // Default fallback redirect if role is not recognized or any other issue
        return redirect('/login')->with('error', 'Your account role is not recognized.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }
}