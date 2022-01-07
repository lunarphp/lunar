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
        'awaiting-payment' => 'Awaiting Payment',
        'dispatched'       => 'Dispatched',
    ],
];
