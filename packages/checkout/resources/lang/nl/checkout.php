<?php

return [
    'payments' => [
        'offline' => [
            'label' => 'Pay later',
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
