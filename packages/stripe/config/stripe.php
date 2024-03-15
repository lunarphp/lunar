<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook path
    |--------------------------------------------------------------------------
    |
    | Set what the path should be for the webhook you set up in Stripe.
    |
    */
    'webhook_path' => 'stripe/webhook',

    /*
    |--------------------------------------------------------------------------
    | Capture policy
    |--------------------------------------------------------------------------
    |
    | Here is where you can set whether you want to capture and charge payments
    | straight away, or create the Payment Intent and release them at a later date.
    |
    | automatic - Capture the payment straight away.
    | manual - Don't take payment straight away and capture later.
    |
    */
    'policy' => 'automatic',

    /*
    |--------------------------------------------------------------------------
    | Status mapping
    |--------------------------------------------------------------------------
    |
    | When a payment intent is retrieved from Stripe it will have a status which is
    | unique to Stripe and potentially not what you have in Lunar. Here you can define
    | what each Stripe status should be in Lunar.
    |
    | Reference: https://stripe.com/docs/api/charges/object
    */
    'status_mapping' => [
        \Stripe\PaymentIntent::STATUS_REQUIRES_CAPTURE => 'requires-capture',
        \Stripe\PaymentIntent::STATUS_CANCELED => 'cancelled',
        \Stripe\PaymentIntent::STATUS_PROCESSING => 'processing',
        \Stripe\PaymentIntent::STATUS_REQUIRES_ACTION => 'awaiting-payment',
        \Stripe\PaymentIntent::STATUS_REQUIRES_CONFIRMATION => 'auth-pending',
        \Stripe\PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD => 'failed',
        \Stripe\PaymentIntent::STATUS_SUCCEEDED => 'payment-received',
    ],

    'actions' => [
        /*
        |--------------------------------------------------------------------------
        | Store charges
        |--------------------------------------------------------------------------
        |
        | A payment intent might have a number of charges associated to them, these
        | could be either pending, captured or refunds. This action takes the charges
        | which are associated to the payment intent and stores them against the order.
        |
        | Reference: https://stripe.com/docs/api/charges/object
        */
        'store_charges' => \Lunar\Stripe\Actions\StoreCharges::class,
    ],
];
