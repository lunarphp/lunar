<?php

use Lunar\Base\OrderReferenceGenerator;

return [
    /*
    |--------------------------------------------------------------------------
    | Order Reference Generator
    |--------------------------------------------------------------------------
    |
    | Here you can specify how you want your order references to be generated
    | when you create an order from a cart.
    |
    */
    'reference_generator' => OrderReferenceGenerator::class,
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
    'statuses' => [
        'awaiting-payment' => [
            'label' => 'Awaiting Payment',
            'color' => '#848a8c',
            'mailers' => [],
            'notifications' => [],
        ],
        'payment-offline' => [
            'label' => 'Payment Offline',
            'color' => '#0A81D7',
            'mailers' => [],
            'notifications' => [],
        ],
        'payment-received' => [
            'label' => 'Payment Received',
            'color' => '#6a67ce',
            'mailers' => [],
            'notifications' => [],
        ],
        'dispatched' => [
            'label' => 'Dispatched',
            'mailers' => [],
            'notifications' => [],
        ],
    ],
];
