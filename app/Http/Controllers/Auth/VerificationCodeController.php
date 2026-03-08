<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Mail\VerifyEmailWithCode; // Correct: Use the Mailable class
use Illuminate\Support\Facades\Mail; // Add this line for Mail facade


class VerificationCodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the email verification code form and always send a new code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showVerificationForm(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $user = $request->user();

        $this->generateAndSendCode($user, $request->session());

        return view('auth.verify-email-code')->with('status', 'A new verification code has been sent to your email address.');
    }

    /**
     * Handle a verification code request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $user = $request->user();
        $sessionCode = $request->session()->get('verification_code_for_' . $user->id);
        $sessionExpiry = $request->session()->get('verification_code_expires_at_for_' . $user->id);

        if ($sessionCode === $request->code &&
            $sessionExpiry &&
            Carbon::parse($sessionExpiry)->isFuture()) {

            $user->markEmailAsVerified();

            $request->session()->forget('verification_code_for_' . $user->id);
            $request->session()->forget('verification_code_expires_at_for_' . $user->id);

            return redirect()->intended(route('dashboard'))->with('verified', 'Your email has been verified!');
        }

        $request->session()->forget('verification_code_for_' . $user->id);
        $request->session()->forget('verification_code_expires_at_for_' . $user->id);

        throw ValidationException::withMessages([
            'code' => __('The provided verification code is invalid or has expired. A new code has been sent.'),
        ]);
    }

    /**
     * Resend the verification code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendCode(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $user = $request->user();
        $sessionExpiry = $request->session()->get('verification_code_expires_at_for_' . $user->id);

        if ($sessionExpiry && Carbon::parse($sessionExpiry)->subMinutes(10)->diffInSeconds(Carbon::now()) < 60) {
            return back()->with('status', 'Please wait before resending the code. A code was sent recently.');
        }

        $this->generateAndSendCode($user, $request->session());

        return back()->with('status', 'A new verification code has been sent to your email address.');
    }

    /**
     * Helper method to generate and send the code, and store in session.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Session\Store  $session
     * @return void
     */
    protected function generateAndSendCode($user, $session)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(10);

        $session->put('verification_code_for_' . $user->id, $code);
        $session->put('verification_code_expires_at_for_' . $user->id, $expiresAt->toDateTimeString());

        // CORRECTED: Use Mail facade to send Mailable
        Mail::to($user->email)->send(new VerifyEmailWithCode($code));
    }
}