<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminAuthController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified.custom'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Admin Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
});

// Admin Panel Protected Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/users/{id}', [AdminController::class, 'userDetail'])->name('admin.users.show');
    Route::get('/payments', [AdminController::class, 'payments'])->name('admin.payments');
    Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('admin.subscriptions');
    Route::get('/contracts', [AdminController::class, 'contracts'])->name('admin.contracts');
    Route::get('/features', [AdminController::class, 'features'])->name('admin.features');
    Route::get('/plans', [AdminController::class, 'plans'])->name('admin.plans');
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit-logs');
});

require __DIR__.'/auth.php';
