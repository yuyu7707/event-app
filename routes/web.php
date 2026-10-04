<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/events', 'dashboard')->name('events.index');
Route::view('/events/create', 'dashboard')->name('events.create');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('/hello', 'pages::hello')->name('dashboaard');
    Route::livewire('/events/create', 'pages::events.create')->name('events.create');
});

require __DIR__ . '/settings.php';
