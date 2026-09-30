<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard.index')->name('pages.dashboard.index');
    Route::livewire('dashboard/counters', 'pages::dashboard.counters')->name('pages.dashboard.counters');
    // Route::livewire('dashboard/token-presets', 'pages::dashboard.token-presets')->name('pages.dashboard.token-presets');
    Route::livewire('dashboard/play-table', 'pages::dashboard.play-table')->name('pages.dashboard.play-table');
});

require __DIR__.'/settings.php';
