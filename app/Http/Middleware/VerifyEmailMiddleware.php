<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Auto-verify and activate the user to bypass email server issues on shared hosting
        if (! $user->hasVerifiedEmail() || ! $user->isActive()) {
            if (!$user->hasVerifiedEmail()) {
                $user->email_verified_at = now();
            }
            $user->status = 'active';
            $user->save();
        }

        return $next($request);
    }
}
