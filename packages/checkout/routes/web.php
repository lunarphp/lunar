<?php

use Illuminate\Support\Facades\Route;
use Lunar\Checkout\Http\Controllers\CheckoutController;

$path = config('lunar.checkout.path', 'checkout');

Route::middleware(config('lunar.checkout.middleware', ['web']))
    ->group(function () use ($path) {
        // The self-contained Inertia checkout app (spec 0008).
        Route::get($path, [CheckoutController::class, 'show'])
            ->name('lunar.checkout.show');

        // Persist a single element's captured data into the checkout session.
        Route::post($path.'/elements/{handle}', [CheckoutController::class, 'storeElement'])
            ->name('lunar.checkout.elements.store');

        // The checkout app's OWN prebuilt bundle, streamed same-origin from the
        // package's dist/ — so install-and-go needs no `vendor:publish` and no
        // build (spec 0008 §B). Only files inside dist/ are servable.
        Route::get($path.'/build/{file}', [CheckoutController::class, 'build'])
            ->where('file', '.*')
            ->name('lunar.checkout.build');

        // Contributed element/gateway chunks, streamed same-origin from each
        // registered package's resources/dist (spec 0009 §C.1). Only registered
        // package + filename pairs are servable — no arbitrary path read.
        Route::get($path.'/assets/{package}/{file}', [CheckoutController::class, 'asset'])
            ->where('file', '[A-Za-z0-9._-]+')
            ->name('lunar.checkout.assets');
    });
