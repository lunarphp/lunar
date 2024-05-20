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
        'cutoff' => [
            'label' => 'Cutoff',
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
            ],
        ],
        'stock_available' => [
            'label' => 'Stock of all basket items must be available',
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
            ],
        ],
    ],
];
