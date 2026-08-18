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

        // Persist the contact email onto the checkout session model (guest) or
        // associate the authenticated customer. Inertia POST, returns back().
        Route::post($path.'/{session}/contact', [CheckoutController::class, 'storeContact'])
            ->name('lunar.checkout.contact.store');

        // Store the delivery address through the driver (spec 0010 §B coarse
        // cart write). Shipping options are address-dependent, so the page
        // re-projects them on the following render — no stale rates.
        Route::post($path.'/{session}/shipping-address', [CheckoutController::class, 'storeShippingAddress'])
            ->name('lunar.checkout.shipping-address.store');

        // Select a shipping option. The driver validates the identifier against
        // the live manifest before writing it to the cart, so anything a
        // modifier removed (exclusions, oversized blocklist) cannot be chosen.
        Route::post($path.'/{session}/shipping-option', [CheckoutController::class, 'storeShippingOption'])
            ->name('lunar.checkout.shipping-option.store');

        // Store the billing address (same payload shape as shipping). The
        // frontend defaults to copying the delivery address; a later billing
        // element can post its own capture here.
        Route::post($path.'/{session}/billing-address', [CheckoutController::class, 'storeBillingAddress'])
            ->name('lunar.checkout.billing-address.store');

        // Create (or resume) the active payment method's confirmable intent
        // and record its reference on the session (spec 0002 §A). Delegates to
        // the gateway driver via the CreatesPaymentIntents capability.
        Route::post($path.'/{session}/payment-intent', [CheckoutController::class, 'storePaymentIntent'])
            ->name('lunar.checkout.payment-intent.store');

        // The pay boundary (spec 0010 §E): pin the session against the
        // fingerprint of the state the customer confirmed. The gateway's
        // client-side confirmation happens after this returns.
        Route::post($path.'/{session}/pay', [CheckoutController::class, 'pay'])
            ->name('lunar.checkout.pay');

        // Render the self-contained Inertia checkout app for one session,
        // addressed by its UUID capability token (spec 0008). Safe/idempotent:
        // a refresh re-renders, it never mints or mutates a session. Ownership
        // is verified in the controller before any session data is projected.
        Route::get($path.'/{session}', [CheckoutController::class, 'show'])
            ->name('lunar.checkout.show');
    });
