<?php

return [
    'payments' => [
        'offline' => [
            'label' => 'Pay later',
        ],
        'on_account' => [
            'label' => 'Pay on account',
            'guard' => 'Enter your purchase order reference to place this order on account.',
        ],
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
