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
        'table' => [
            'shipping_method' => [
                'label' => 'Shipping Method',
            ],
            'base_price' => [
                'label' => 'Base Price',
            ],
            'tiered_prices_count' => [
                'label' => 'No. Tiers',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Shipping Exclusions',
        'form' => [
            'purchasable' => [
                'label' => 'Product',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Add shipping exclusion list',
            ],
            'attach' => [
                'label' => 'Add exclusion list',
            ],
            'detach' => [
                'label' => 'Remove',
            ],
        ],
    ],
];
