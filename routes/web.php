<?php

use App\Http\Controllers\CashflowController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('cashflows', CashflowController::class)->except(['create', 'edit', 'show']);
    Route::get('users/{user}/permissions', [UserController::class, 'permissions'])->name('users.permissions.edit');
    Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');
    Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
});

require __DIR__.'/settings.php';
