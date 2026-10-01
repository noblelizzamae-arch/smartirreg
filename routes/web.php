<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing page (root)
Route::get('/', [AuthController::class, 'landing'])->name('landing');

// Auth
Route::get('/login',          [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',         [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',        [AuthController::class, 'logout'])->name('logout');

// Forgot password (view only – full reset flow uses Laravel's built-in)
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');

// Password reset email – requires the notification trait on User; handled by Laravel
Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
    $request->validate(['email' => 'required|email']);
    $status = \Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));
    return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
        ? back()->with('status', __($status))
        : back()->withErrors(['email' => __($status)]);
})->name('password.email');

// Dashboard (protected) — auto-pulls live data from Blynk on every load
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// Live JSON endpoint for 2-second auto-update
Route::get('/dashboard/live', [DashboardController::class, 'live'])->name('dashboard.live')->middleware('auth');

// Live data endpoint for auto-refresh (no page reload)
Route::get('/dashboard/live', [DashboardController::class, 'live'])->name('dashboard.live')->middleware('auth');
