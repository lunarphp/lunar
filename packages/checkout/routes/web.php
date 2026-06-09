<?php

use Illuminate\Support\Facades\Route;
use Lunar\Checkout\Http\Controllers\CheckoutController;

Route::middleware(config('lunar.checkout.middleware', ['web']))
    ->group(function () {
        Route::get(config('lunar.checkout.path', 'checkout'), [CheckoutController::class, 'show'])
            ->name('lunar.checkout.show');

        // Persist a single element's captured data into the checkout session.
        Route::post(config('lunar.checkout.path', 'checkout').'/elements/{handle}', [CheckoutController::class, 'storeElement'])
            ->name('lunar.checkout.elements.store');
    });
