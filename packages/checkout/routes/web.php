<?php

use Illuminate\Support\Facades\Route;
use Lunar\Checkout\Http\Controllers\CheckoutController;

$path = config('lunar.checkout.path', 'checkout');

Route::middleware(config('lunar.checkout.middleware', ['web']))
    ->group(function () use ($path) {
        // The self-contained Inertia checkout app (spec 0008). The app bundle
        // and every contributed chunk (spec 0009) are served from the host's
        // public/ by Laravel's Vite class (published, no bespoke asset route) —
        // see the root view.
        Route::get($path, [CheckoutController::class, 'show'])
            ->name('lunar.checkout.show');

        // Persist a single element's captured data into the checkout session.
        Route::post($path.'/elements/{handle}', [CheckoutController::class, 'storeElement'])
            ->name('lunar.checkout.elements.store');
    });
