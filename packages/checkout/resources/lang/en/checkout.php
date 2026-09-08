<?php

return [
    'payments' => [
        'offline' => [
            'label' => 'Pay later',
        ],
        'on_account' => [
            'label' => 'Pay on account',
        ],
    ],

    'pay' => [
        'place_order' => 'Place order',
    ],

    'states' => [
        'checkout-session' => [
            'open' => 'Open',
            'payment-processing' => 'Payment processing',
            'completed' => 'Completed',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
        ],
    ],
];
