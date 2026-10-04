

<?php

use Illuminate\Support\Facades\Route;

// 誰でも見られるページ
Route::redirect('/', '/events')->name('home');
Route::livewire('/events', 'pages::events.index')->name('events.index');

// ログインが必要なページ
Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/events')->name('dashboard');

    Route::livewire('/hello', 'pages::hello')->name('hello');
    Route::livewire('/events/create', 'pages::events.create')->name('events.create');
    Route::livewire('/events/{event}/edit', 'pages::events.edit')->name('events.edit');
});

require __DIR__.'/settings.php';
