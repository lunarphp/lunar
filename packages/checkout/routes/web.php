<?php

use Illuminate\Support\Facades\Route;
use Lunar\Checkout\Http\Controllers\CheckoutController;

$path = config('lunar.checkout.path', 'checkout');

Route::middleware(config('lunar.checkout.middleware', ['web']))
    ->group(function () use ($path) {
        // Start a checkout: resolve-or-create the current cart's session and
        // redirect to its UUID URL (spec 0004/0005). A POST, not a link — the
        // session is a mutation, never created on cart load or a bare GET.
        Route::post($path, [CheckoutController::class, 'start'])
            ->name('lunar.checkout.start');

        // Persist a single element's captured data into the checkout session.
        Route::post($path.'/elements/{handle}', [CheckoutController::class, 'storeElement'])
            ->name('lunar.checkout.elements.store');

        // Contact-step account lookup: owned + throttled, returns { exists }
        // only (never passkey info) so it is not a public enumeration oracle.
        Route::post($path.'/{session}/contact/lookup', [CheckoutController::class, 'contactLookup'])
            ->middleware('throttle:checkout-contact-lookup')
            ->name('lunar.checkout.contact.lookup');

        // Render the self-contained Inertia checkout app for one session,
        // addressed by its UUID capability token (spec 0008). Safe/idempotent:
        // a refresh re-renders, it never mints or mutates a session. Ownership
        // is verified in the controller before any session data is projected.
        Route::get($path.'/{session}', [CheckoutController::class, 'show'])
            ->name('lunar.checkout.show');
    });
