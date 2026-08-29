<?php

use App\Http\Controllers\Auth\EmailLoginController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SauceRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

// --- Guest auth routes ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [EmailLoginController::class, 'show'])->name('login');

    Route::post('/login/email', [EmailLoginController::class, 'send'])
        ->middleware('throttle:5,1')
        ->name('login.email');

    Route::get('/login/email/verify', [EmailLoginController::class, 'verify'])->name('login.verify');

    Route::post('/login/email/verify', [EmailLoginController::class, 'verifyCode'])
        ->middleware('throttle:5,1')
        ->name('login.verify.submit');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

// --- Authenticated routes ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/create', [SauceRequestController::class, 'create'])->name('create');
    Route::post('/sauce-requests', [SauceRequestController::class, 'store'])->name('sauce-requests.store');

    Route::get('/sauce-requests/{sauceRequest}/edit', [SauceRequestController::class, 'edit'])->name('sauce-requests.edit');
    Route::put('/sauce-requests/{sauceRequest}', [SauceRequestController::class, 'update'])->name('sauce-requests.update');
});

// --- Public profile routes ---
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.show');

// --- Public sauce request routes ---
Route::get('/sauce-requests', [SauceRequestController::class, 'index'])->name('sauce-requests.index');
Route::get('/sauce-requests/{sauceRequest}', [SauceRequestController::class, 'show'])->name('sauce-requests.show');

Route::get('/search', function () {
    return view('pages.search');
})->name('search');

Route::get('/notifications', function () {
    return view('pages.notifications');
})->name('notifications');

Route::get('/settings', function () {
    return view('pages.settings');
})->name('settings');
