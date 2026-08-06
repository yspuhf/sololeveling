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

        if (! $user || ! $user->hasVerifiedEmail() || ! $user->isActive()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your email address is not verified or your account is inactive.',
                ], 403);
            }

            return redirect()->route('verification.notice')
                ->with('error', 'Please verify your email address before accessing this feature.');
        }

        return $next($request);
    }
}
