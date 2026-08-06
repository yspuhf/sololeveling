<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:1,1')
        ->name('verification.send');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Route::post('logout', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

Route::get('email/verified', function () {
    return view('auth.verification-success');
})->name('verification.success');
