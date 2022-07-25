<?php

use GetCandy\Hub\Http\Controllers\ScriptsController;
use GetCandy\Hub\Http\Controllers\StylesController;
use Illuminate\Support\Facades\Route;

/**
 * Assets routes.
 */
Route::group(['prefix' => 'scripts'], function () {
    Route::get('/{script}', ScriptsController::class)->name('hub.assets.scripts');
});

Route::group(['prefix' => 'styles'], function () {
    Route::get('/{style}', StylesController::class)->name('hub.assets.styles');
});
