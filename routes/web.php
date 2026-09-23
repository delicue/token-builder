<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard.index')->name('pages.dashboard.index');
    // Route::livewire('dashboard/life-counter', 'pages::dashboard.life-counter')->name('pages.dashboard.life-counter');
    Route::livewire('dashboard/token-presets', 'pages::dashboard.token-presets')->name('pages.dashboard.token-presets');
    Route::livewire('dashboard/play-table', 'pages::dashboard.play-table')->name('pages.dashboard.play-table');
});

require __DIR__.'/settings.php';
