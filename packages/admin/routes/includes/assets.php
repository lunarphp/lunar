<?php

use Illuminate\Support\Facades\Route;
use Lunar\Hub\Http\Controllers\ScriptsController;
use Lunar\Hub\Http\Controllers\StylesController;

/**
 * Assets routes.
 */
Route::group(['prefix' => 'scripts'], function () {
    Route::get('/{script}', ScriptsController::class)->name('hub.assets.scripts');
});

Route::group(['prefix' => 'styles'], function () {
    Route::get('/{style}', StylesController::class)->name('hub.assets.styles');
});
