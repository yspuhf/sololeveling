<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Auth\Events\Verified;

class EmailVerificationController extends Controller
{
    /**
     * Display the email verification notice.
     */
    public function notice(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user && $user->hasVerifiedEmail() && $user->isActive()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return view('auth.verify-email');
    }

    /**
     * Mark the user's email address as verified.
     */
    public function verify(Request $request, string $id, string $hash): RedirectResponse|View
    {
        $user = User::find($id);

        if (! $user) {
            abort(404, 'User not found.');
        }

        // Run cryptographic string equality validation matching the route hash against sha1($user->getEmailForVerification())
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return view('auth.verification-failed', [
                'error' => 'The verification hash is invalid or tampered with.'
            ]);
        }

        // Explicitly validate the signed URL via $request->hasValidSignature()
        if (! $request->hasValidSignature()) {
            return view('auth.verification-failed', [
                'error' => 'The verification link has either expired or been tampered with.'
            ]);
        }

        // If user is already verified and active, redirect to success page
        if ($user->hasVerifiedEmail() && $user->isActive()) {
            return redirect()->route('verification.success');
        }

        // Transition email_verified_at to the current timestamp
        if (! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
        }

        // Update status to 'active'
        $user->status = 'active';
        $user->save();

        event(new Verified($user));

        return redirect()->route('verification.success');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail() && $user->isActive()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
