<?php

use App\Http\Controllers\Auth\EmailLoginController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SauceRequestController;
use App\Http\Controllers\SauceRequestTagController;
use App\Http\Controllers\SauceRequestTagHistoryController;
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
    Route::post('/sauce-requests/upload', [SauceRequestController::class, 'upload'])
        ->middleware('throttle:upload')
        ->name('sauce-requests.upload');
    Route::get('/sauce-requests/{sauceRequest}/details', [SauceRequestController::class, 'details'])->name('sauce-requests.details');
    Route::get('/sauce-requests/{sauceRequest}/duplicate/{duplicate}', [SauceRequestController::class, 'duplicate'])->name('sauce-requests.duplicate');
    Route::get('/sauce-requests/{sauceRequest}/sauce', [SauceRequestController::class, 'sauce'])->name('sauce-requests.sauce');
    Route::post('/sauce-requests/{sauceRequest}/publish', [SauceRequestController::class, 'publish'])->name('sauce-requests.publish');
    Route::post('/sauce-requests/{sauceRequest}/cancel', [SauceRequestController::class, 'cancel'])->name('sauce-requests.cancel');

    Route::get('/sauce-requests/{sauceRequest}/edit', [SauceRequestController::class, 'edit'])->name('sauce-requests.edit');
    Route::put('/sauce-requests/{sauceRequest}', [SauceRequestController::class, 'update'])->name('sauce-requests.update');

    // --- Community tagging ---
    Route::put('/sauce-requests/{sauceRequest}/tags', [SauceRequestTagController::class, 'update'])
        ->middleware('throttle:tagging')
        ->name('sauce-requests.tags.update');

    // --- Tagging history ---
    Route::get('/sauce-requests/{sauceRequest}/tags/history', [SauceRequestTagHistoryController::class, 'index'])->name('sauce-requests.tags.history');
    Route::post('/sauce-requests/{sauceRequest}/tags/history/{taggingHistory}/restore', [SauceRequestTagHistoryController::class, 'restore'])
        ->middleware('throttle:tagging')
        ->name('sauce-requests.tags.history.restore');
})->scopeBindings();

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
