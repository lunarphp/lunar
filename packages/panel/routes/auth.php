<?php

use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Auth\AuthenticatedSessionController;
use Lunar\Panel\Http\Middleware\RedirectIfAuthenticated;

Route::middleware(RedirectIfAuthenticated::class)->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('panel.login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('panel.login.store');
});
