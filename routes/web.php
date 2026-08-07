<?php

use App\Http\Controllers\CashflowController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('cashflows', CashflowController::class)->except(['create', 'edit', 'show']);
});

require __DIR__.'/settings.php';
