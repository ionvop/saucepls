<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/search', function () {
    return view('pages.search');
})->name('search');

Route::get('/notifications', function () {
    return view('pages.notifications');
})->name('notifications');

Route::get('/profile', function () {
    return view('pages.profile');
})->name('profile');

Route::get('/settings', function () {
    return view('pages.settings');
})->name('settings');

Route::get('/create', function () {
    return view('pages.create');
})->name('create');
