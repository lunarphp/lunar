<?php

use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Auth\AuthenticatedSessionController;
use Lunar\Panel\Http\Controllers\DashboardController;

Route::get('/', DashboardController::class)->name('panel.dashboard');

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('panel.logout');
