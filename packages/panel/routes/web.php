<?php

use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\DashboardController;

Route::get('/', DashboardController::class)->name('panel.dashboard');
