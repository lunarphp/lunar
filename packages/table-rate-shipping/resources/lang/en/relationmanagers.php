<?php

return [
    'shipping_rates' => [
        'title_plural' => 'Shipping Rates',
        'actions' => [
            'create' => [
                'label' => 'Create Shipping Rate',
            ],
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Shipping Method',
            ],
            'base_price' => [
                'label' => 'Base Price',
            ],
            'prices' => [
                'label' => 'Pricing Tiers',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Customer Group',
                        'placeholder' => 'Any',
                    ],
                    'currency_id' => [
                        'label' => 'Currency',
                    ],
                    'tier' => [
                        'label' => 'Tier',
                    ],
                    'price' => [
                        'label' => 'Price',
                    ],
                ],
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Shipping Exclusions',
        'actions' => [
            'create' => [
                'label' => 'Add shipping exclusion list',
            ],
        ],
    ],
];
