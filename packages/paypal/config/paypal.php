<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | Your PayPal REST app credentials, and which API to talk to.
    |
    | env - sandbox or live.
    |
    | These fall back to the equivalent `services.paypal.*` keys when unset, so
    | existing installs keep working. That fallback is deprecated and will be
    | removed in a future release — move your credentials to this file.
    |
    */
    'env' => env('PAYPAL_ENV'),

    'client_id' => env('PAYPAL_CLIENT_ID'),

    'secret' => env('PAYPAL_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Webhook
    |--------------------------------------------------------------------------
    |
    | The ID of the webhook you created in the PayPal dashboard, used to verify
    | that inbound notifications genuinely came from PayPal, and the path the
    | driver listens on. Without a webhook ID, inbound notifications are
    | rejected.
    |
    */
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),

    'webhook_path' => 'paypal/webhook',

    /*
    |--------------------------------------------------------------------------
    | Capture policy
    |--------------------------------------------------------------------------
    |
    | Whether to take payment straight away, or authorize now and capture later.
    |
    | automatic - Capture the payment straight away.
    | manual - Authorize only, and capture later.
    |
    */
    'policy' => 'automatic',

    /*
    |--------------------------------------------------------------------------
    | Allow partial payments
    |--------------------------------------------------------------------------
    |
    | When enabled, the PayPal order does not need to cover the order total.
    | This is useful for stores that accept deposits or partial payments. When
    | disabled (default), an under-payment fails authorization before any money
    | is captured. An over-payment always places the order regardless of this
    | setting — the excess surfaces in the order's settlement state.
    |
    */
    'allow_partial_payment' => false,

    /*
    |--------------------------------------------------------------------------
    | Storefront routes
    |--------------------------------------------------------------------------
    |
    | Where PayPal returns the customer once they approve or cancel a payment.
    |
    */
    'success_route' => 'checkout.success',

    'cancel_route' => 'checkout.cancel',
];
