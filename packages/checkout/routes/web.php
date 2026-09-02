<?php

use Illuminate\Support\Facades\Route;
use Lunar\Checkout\Http\Controllers\CheckoutController;

$path = config('lunar.checkout.path', 'checkout');

Route::middleware(config('lunar.checkout.middleware', ['web']))
    // {session} is always a UUID. The constraint matters beyond hygiene: it
    // lets the host register its own literal paths under the same prefix
    // (e.g. GET /checkout/confirmation as the success URL) without this
    // group's {session} routes swallowing them.
    ->whereUuid('session')
    ->group(function () use ($path) {
        // Start a checkout: resolve-or-create the current cart's session and
        // redirect to its UUID URL (spec 0004/0005). A POST, not a link — the
        // session is a mutation, never created on cart load or a bare GET.
        Route::post($path, [CheckoutController::class, 'start'])
            ->name('lunar.checkout.start');

        // Persist one element's captured data into the session's element bag.
        // Session-scoped and ownership-gated like every other write route: the
        // bag is now a row, so an ungated handle would let a stranger write to
        // someone else's checkout.
        Route::post($path.'/{session}/elements/{handle}', [CheckoutController::class, 'storeElement'])
            ->name('lunar.checkout.elements.store');

        // Contact-step account lookup: owned + throttled, returns { exists }
        // only (never passkey info) so it is not a public enumeration oracle.
        Route::post($path.'/{session}/contact/lookup', [CheckoutController::class, 'contactLookup'])
            ->middleware('throttle:checkout-contact-lookup')
            ->name('lunar.checkout.contact.lookup');

        // Postcode to address lookup (spec 0011 §C). Session-scoped and owned
        // like every other write route, throttled like the contact lookup, and
        // the postcode is format-checked before any vendor call is made.
        Route::post($path.'/{session}/address-lookup', [CheckoutController::class, 'addressLookup'])
            ->middleware('throttle:checkout-address-lookup')
            ->name('lunar.checkout.address-lookup');

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

        // Customer-initiated unpin after a failed/abandoned gateway
        // confirmation: reopens the pinned session for another attempt, but
        // only once the gateway confirms no money was captured.
        Route::post($path.'/{session}/payment-release', [CheckoutController::class, 'releasePayment'])
            ->name('lunar.checkout.payment-release');

        // Post-confirmation landing: settles the session against the
        // gateway's actual outcome and forwards to the store's success URL,
        // or renders a polling page while the outcome is still in flight.
        Route::get($path.'/{session}/processing', [CheckoutController::class, 'processing'])
            ->name('lunar.checkout.processing');

        // Render the self-contained Inertia checkout app for one session,
        // addressed by its UUID capability token (spec 0008). Safe/idempotent:
        // a refresh re-renders, it never mints or mutates a session. Ownership
        // is verified in the controller before any session data is projected.
        Route::get($path.'/{session}', [CheckoutController::class, 'show'])
            ->name('lunar.checkout.show');
    });
