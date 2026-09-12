<?php

use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('onboarding/create-group', [OnboardingController::class, 'createGroup'])->name('onboarding.create-group');
    Route::post('onboarding/join', [OnboardingController::class, 'join'])->name('onboarding.join');

    Route::post('groups/invite-code/refresh', [GroupController::class, 'refreshInviteCode'])
        ->middleware('role:admin,treasurer')
        ->name('groups.invite-code.refresh');

    Route::get('dashboard', function () {
        $user = auth()->user();

        if (! $user->group_id) {
            return redirect()->route('onboarding');
        }

        return "Dashboard untuk role: {$user->role} (belum diarahkan ke controller aslinya)";
    })->name('dashboard');
});