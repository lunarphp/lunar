<?php

return [
    'label_plural' => 'Shipping Methods',
    'label' => 'Shipping Method',
    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'schedule' => [
            'label' => 'Availability Schedule',
            'days' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
            'from' => [
                'label' => 'From',
            ],
            'to' => [
                'label' => 'Until',
                'validation' => [
                    'after' => 'The until time must be after the from time.',
                ],
            ],
        ],
        'charge_by' => [
            'label' => 'Charge By',
            'options' => [
                'cart_total' => 'Cart Total',
                'weight' => 'Weight',
            ],
        ],
        'driver' => [
            'label' => 'Type',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Collection',
                'flat-rate' => 'Flat Rate',
                'free-shipping' => 'Free Shipping',
            ],
        ],
        'stock_available' => [
            'label' => 'Stock of all basket items must be available',
        ],
        'weight_unit' => [
            'label' => 'Weight Unit',
            'placeholder' => 'No weight restriction',
        ],
        'min_weight' => [
            'label' => 'Minimum Weight',
        ],
        'max_weight' => [
            'label' => 'Maximum Weight',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'driver' => [
            'label' => 'Type',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Collection',
                'flat-rate' => 'Flat Rate',
                'free-shipping' => 'Free Shipping',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Availability',
            'customer_groups' => 'This shipping method is currently unavailable across all customer groups.',
        ],
    ],
];
