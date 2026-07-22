<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Checkout route
    |--------------------------------------------------------------------------
    |
    | The URI the self-contained checkout app is served from and the middleware
    | applied to its route group. A consuming storefront (any stack) links or
    | redirects a customer here.
    |
    */

    'path' => 'checkout',

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Merchant
    |--------------------------------------------------------------------------
    |
    | The store display name shown in the checkout header (and the accessible
    | name of the brand mark). A value, not a brand-asset substitution, so it
    | lives in config — the header logo itself is swapped via CheckoutTheme.
    | Falls back to the application name when null.
    |
    */

    'merchant' => env('CHECKOUT_MERCHANT'),

    /*
    |--------------------------------------------------------------------------
    | Package routes
    |--------------------------------------------------------------------------
    |
    | When true, the package registers its own checkout, element-store, app-
    | bundle and contributed-chunk routes. Set to false only for the
    | publish-and-own tier (spec 0008 §C): you publish the app source, register
    | your own route + controller, and serve the build yourself.
    |
    */

    'routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Checkout driver
    |--------------------------------------------------------------------------
    |
    | The active checkout driver (spec 0004): turns a cart into a checkout
    | session and a session into an order. The default `lunar` driver targets
    | Lunar's cart + order. A non-Lunar backend registers its own driver and
    | selects it here BY NAME (a value — not a class swap; bindings stay in the
    | container per the Lunar convention).
    |
    */

    'driver' => 'lunar',

    /*
    |--------------------------------------------------------------------------
    | Checkout session
    |--------------------------------------------------------------------------
    |
    | `expires_after` is the window (in hours) a newly created checkout session
    | stays Open before it is eligible for expiry (spec 0004, Stripe-aligned at
    | 24h). The `lunar:checkout:expire-sessions` command transitions stale Open
    | sessions to Expired.
    |
    */

    'session' => [
        'expires_after' => 24,

        // Grace window (minutes) re-armed onto expires_at when a session
        // returns PaymentProcessing → Open after a refund/void (spec 0010 §F),
        // so it never reopens pre-expired.
        'reopen_grace_minutes' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bounded reconciliation (spec 0010 §F)
    |--------------------------------------------------------------------------
    |
    | PaymentProcessing sessions older than `after_minutes` are swept against
    | the gateway's actual intent outcome. After `max_attempts` failed lookups
    | the session is marked stalled (event fires once) and waits for the
    | operator resolve command: lunar:checkout:reconcile {uuid} --resolve=…
    |
    */

    'reconciliation' => [
        'after_minutes' => 60,
        'max_attempts' => 5,
    ],

];
