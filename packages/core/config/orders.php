<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Draft Status
    |--------------------------------------------------------------------------
    |
    | When a draft order is created from a cart, we need an initial status for
    | the order that's created. Define that here, it can be anything that would
    | make sense for the store you're building.
    |
    */
    'draft_status' => 'awaiting-payment',
    'statuses'     => [
        'awaiting-payment' => [
            'label' => 'Awaiting Payment',
            'color' => '#848a8c',
        ],
        'payment-received' => [
            'label' => 'Payment Received',
            'color' => '#6a67ce',
        ],
        'dispatched'  => [
            'label' => 'Dispatched',
        ],
    ],
];
