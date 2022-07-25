<?php

use GetCandy\Hub\Http\Controllers\ScriptsController;
use GetCandy\Hub\Http\Controllers\StylesController;
use Illuminate\Support\Facades\Route;

/**
 * Assets routes.
 */
Route::get('/{script}', ScriptsController::class)->name('hub.assets.scripts');
Route::get('/{style}', StylesController::class)->name('hub.assets.styles');
